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
use Illuminate\Support\Facades\Auth;

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
            'confirmFlag' => $attendance->confirm_flag,
        ]);
    }

    public function update(AttendanceRequest $request)
    {
        $attendanceService = new AttendanceService();
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($request->year_month);

        try {
            // 勤怠ヘッダーを取得
            $attendanceHeader = AttendanceHeader::where([
                'user_id' => $request->user_id,
                'year_month' => $date
            ])->first();

            // 勤怠が確定済みかを確認
            if ($attendanceHeader && $attendanceHeader->confirm_flag === 1) {
                return response()->json(['success' => false, 'message' => 'すでに勤怠は確定されています。'],403);
            }

            DB::transaction(function () use ($request, $attendanceService, $date, $attendanceHeader) {
                if (!$attendanceHeader) {
                    $attendanceHeader = AttendanceHeader::firstOrCreate([
                        'user_id' => $request->user_id,
                        'year_month' => $date
                    ]);
                }

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

                BreakTime::where('attendance_daily_id', $attendanceDaily->id)->delete();
                foreach ($request->input('break_times', []) as $breakTime) {
                    BreakTime::create([
                        'attendance_daily_id' => $attendanceDaily->id,
                        'break_time_from' => $breakTime['break_time_from'],
                        'break_time_to' => $breakTime['break_time_to'],
                    ]);
                }

                $company = Company::find(1);
                $updateMonthParams = $company->rounding_scope == 0
                    ? $attendanceService->getUpdateMonthParamsWithGlobalRounding($attendanceHeader->id)
                    : $attendanceService->getUpdateMonthParams($attendanceHeader->id);

                $attendanceHeader->fill($updateMonthParams)->save();

                // ロック解除処理を追加
                $attendanceDaily->locked_by = null;
                $attendanceDaily->locked_at = null;
                $attendanceDaily->save();
            });

            session()->flash('flash_message', '勤怠情報を更新しました');

            return response()->json(['success' => true, 'message' => '勤怠情報を更新しました']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '更新が失敗しました。'], 500);
        }
    }

    /**
     * 勤怠情報の日次データを削除するメソッド。
     *
     * @param int $user_id
     * @param string $year_month
     * @param string $work_date
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($user_id, $year_month, $work_date)
    {
        $attendanceService = new AttendanceService();

        // 勤怠ヘッダーを取得
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($year_month);
        $attendanceHeader = AttendanceHeader::where([
            'user_id' => $user_id,
            'year_month' => $date,
        ])->first();

        // 勤怠が確定済みかを確認
        if ($attendanceHeader && $attendanceHeader->confirm_flag === 1) {
            return response()->json([
                'success' => false,
                'message' => 'この勤怠情報は確定済みのため削除できません。',
            ], 403); // 403 Forbidden を返す
        }

        try {
            DB::transaction(function () use ($attendanceHeader, $work_date, $attendanceService) {
                // 日次勤怠データを削除
                AttendanceDaily::where([
                    'attendance_header_id' => $attendanceHeader->id,
                    'work_date' => $work_date,
                ])->delete();

                // 労働時間計算処理（月次）のパラメータを更新
                $updateMonthParams = $attendanceService->getUpdateMonthParams($attendanceHeader->id);

                // 勤怠ヘッダー情報を更新
                $attendanceHeader->fill($updateMonthParams)->save();
            });

            // フラッシュメッセージを設定
            session()->flash('flash_message', '勤怠情報を削除しました。');

            return response()->json(['success' => true, 'message' => '勤怠情報を削除しました。']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '削除に失敗しました。'], 500);
        }
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

    /**
     * 勤怠データのロック状態を確認するメソッド。
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkLock(Request $request)
    {
        $attendanceDaily = AttendanceDaily::find($request->id);

        if (!$attendanceDaily) {
            return response()->json(['error' => 'データが見つかりません'], 404);
        }

        // ロック有効期限のチェック（例：5分）
        $lockTimeout = now()->subMinutes(5);
        if ($attendanceDaily->locked_at && $attendanceDaily->locked_at < $lockTimeout) {
            // ロック解除
            $attendanceDaily->locked_by = null;
            $attendanceDaily->locked_at = null;
            $attendanceDaily->save();

            return response()->json(['locked_by' => null]); // ロックが解除されたことを通知
        }

        return response()->json(['locked_by' => $attendanceDaily->locked_by]);
    }

    /**
     * 勤怠データをロックするメソッド。
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function lock(Request $request)
    {
        $attendanceDaily = AttendanceDaily::find($request->id);

        if (!$attendanceDaily) {
            return response()->json(['error' => 'データが見つかりません'], 404);
        }

        $userId = (int) $request->user_id; // 明示的に整数型に変換

        // 他のユーザーによるロックチェック
        if ($attendanceDaily->locked_by && (int) $attendanceDaily->locked_by !== $userId) {
            return response()->json(['error' => 'このデータは他のユーザーがロック中です'], 403);
        }

        // ロックを設定または更新
        $attendanceDaily->locked_by = $userId;
        $attendanceDaily->locked_at = now(); // ロック日時を更新
        $attendanceDaily->save();

        return response()->json(['success' => true]);
    }

    /**
     * 勤怠データのロックを解除するメソッド。
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unlock(Request $request)
    {
        $attendanceDaily = AttendanceDaily::find($request->id);

        if (!$attendanceDaily) {
            return response()->json(['error' => 'データが見つかりません'], 404);
        }

        $currentUserId = auth()->id();

        if ($attendanceDaily->locked_by !== $currentUserId) {
            return response()->json(['error' => 'ロックを解除する権限がありません'], 403);
        }

        $attendanceDaily->locked_by = null;
        $attendanceDaily->locked_at = null;
        $attendanceDaily->save();

        return response()->json(['success' => true]);
    }
}
