<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Http\Resources\AttendanceDailyResource;
use App\Models\AttendanceDaily;
use App\Models\AttendanceHeader;
use App\Models\BreakTime;
use App\Models\Company;
use App\Services\AttendanceService;
use App\Services\GetDateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceHeaderController extends Controller
{
    public function show($user_id, $yearMonth)
    {

        $getDateService = new GetDateService();

        $date = $getDateService->createYearMonthFormat($yearMonth);

        $attendance = AttendanceHeader::firstOrNew(['user_id' => $user_id, 'year_month' => $date]);

        // 日次勤怠データを取得し、休憩時間をリレーションで取得
        $attendanceDaily = AttendanceDaily::where('attendance_header_id', $attendance->id)
             ->with('breakTimes') // リレーションにより休憩時間を含む
            ->get()
            ->keyBy('work_date')
            ->toArray();

        $daysOfMonth = $getDateService->getDaysOfMonth($date->copy());

        $company = Company::company();

        return view('user.attendance_header.show')->with([
            'attendance' => $attendance,
            'attendanceDaily' => $attendanceDaily,
            'daysOfMonth' => $daysOfMonth,
            'date' => $date->format('Y-m'),
            'company' => $company,
        ]);
    }

    public function update(AttendanceRequest $request)
    {
        $attendanceService = new AttendanceService();
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($request->year_month);

        try {
            DB::transaction(function () use ($request, $attendanceService, $date) {
                // 勤怠ヘッダーを作成または取得
                $attendanceHeader = AttendanceHeader::firstOrCreate([
                    'user_id' => $request->user_id,
                    'year_month' => $date
                ]);

                // 日次勤怠を作成または更新
                $attendanceDaily = AttendanceDaily::updateOrCreate(
                    [
                        'attendance_header_id' => $attendanceHeader->id,
                        'work_date' => $request->work_date,
                    ],
                    $attendanceService->getUpdateDailyParams(array_merge(
                        $request->validated(),
                        ['break_times' => $request->input('break_times', [])]
                    ))
                );

                // 休憩時間を更新
                BreakTime::where('attendance_daily_id', $attendanceDaily->id)->delete();
                foreach ($request->input('break_times', []) as $breakTime) {
                    BreakTime::create([
                        'attendance_daily_id' => $attendanceDaily->id,
                        'break_time_from' => $breakTime['break_time_from'],
                        'break_time_to' => $breakTime['break_time_to'],
                    ]);
                }

                // 月次勤怠計算を更新
                $company = Company::find(1);
                $updateMonthParams = $company->rounding_scope == 0
                    ? $attendanceService->getUpdateMonthParamsWithGlobalRounding($attendanceHeader->id)
                    : $attendanceService->getUpdateMonthParams($attendanceHeader->id);

                $attendanceHeader->fill($updateMonthParams)->save();
            });

            session()->flash('flash_message', '勤怠情報を更新しました');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // バリデーションエラー時の処理
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            session()->flash('flash_message', '更新が失敗しました');
        }
        return redirect(route('user.attendance_header.show', ['user_id' => $request->user_id, 'year_month' => $date]));
    }

    public function destroy($user_id, $year_month, $work_date)
    {
        $attendanceService = new AttendanceService();

        // 勤怠ヘッダーを取得または作成
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($year_month);
        $attendanceHeader = AttendanceHeader::firstOrCreate(['user_id' => $user_id, 'year_month' => $date]);

        // 指定された日次勤怠データを削除
        AttendanceDaily::where(['attendance_header_id' => $attendanceHeader->id, 'work_date' => $work_date])->delete();

        // 労働時間計算処理（月次）のパラメータを更新
        $updateMonthParams = $attendanceService->getUpdateMonthParams($attendanceHeader->id);

        // 勤怠ヘッダー情報を更新
        $attendanceHeader->fill($updateMonthParams)->saveOrFail();

        // 勤怠詳細画面にリダイレクト
        return redirect(route('user.attendance_header.show', ['user_id' => $user_id, 'year_month' => $date]));
    }

    public function ajaxGetAttendanceInfo(Request $request)
    {
        // 指定された ID の日次勤怠データを取得または新規作成
        $attendanceDaily = AttendanceDaily::with('breakTimes')->findOrNew($request->id);

        // 勤怠データと休憩時間を JSON で返却
        return response()->json([
            'data' => [
                'attendance_class' => $attendanceDaily->attendance_class,
                'working_time' => $attendanceDaily->working_time,
                'leave_time' => $attendanceDaily->leave_time,
                'memo' => $attendanceDaily->memo,
                'break_times' => $attendanceDaily->breakTimes->map(function ($breakTime) {
                    return [
                        'break_time_from' => $breakTime->break_time_from,
                        'break_time_to' => $breakTime->break_time_to,
                    ];
                }),
            ]
        ]);
    }
}
