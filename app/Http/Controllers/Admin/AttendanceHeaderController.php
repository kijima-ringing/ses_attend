<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\BreakTimeRequest;
use App\Http\Resources\AttendanceDailyResource;
use App\Models\AttendanceDaily;
use App\Models\AttendanceHeader;
use App\Models\BreakTime;
use App\Models\Company;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\GetDateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceHeaderController extends Controller
{
    /**
     * 勤怠ヘッダー一覧を表示するメソッド。
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // 日付操作を提供するサービスクラスのインスタンスを作成
        $getDateService = new GetDateService();

        // フォーマットされた年-月データを取得
        $date = $getDateService->createYearMonthFormat($request->year_month);

        // 検索用に年-月の最初の日付を取得
        $dateForSearch = $date->format('Y-m-d');

        // ユーザーを ID 順に取得するクエリを構築
        $query = User::orderBy('users.id');

        // 勤怠ヘッダー情報を結合してユーザー情報を取得
        $users = $query->leftJoinAttendanceHeader($dateForSearch);

        // ビューにユーザーと選択された日付情報を渡す
        return view('admin.attendance_header.index')->with([
            'users' => $users,
            'date' => $date->format('Y-m'),
        ]);
    }

    /**
     * 指定されたユーザーと年月の勤怠詳細を表示するメソッド。
     *
     * @param int $user_id
     * @param string $yearMonth
     * @return \Illuminate\View\View
     */
    public function show($user_id, $yearMonth)
    {
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($yearMonth);

        $attendance = AttendanceHeader::firstOrNew(['user_id' => $user_id, 'year_month' => $date]);
        $attendanceDaily = AttendanceDaily::where('attendance_header_id', $attendance->id)
            ->with('breakTimes') // 休憩時間を含めて取得
            ->get()
            ->keyBy('work_date')
            ->toArray();

        foreach ($attendanceDaily as &$daily) {
            $daily['break_times'] = BreakTime::where('attendance_daily_id', $daily['id'])->get()->toArray();
        }

        $daysOfMonth = $getDateService->getDaysOfMonth($date->copy());
        $company = Company::company();

        return view('admin.attendance_header.show')->with([
            'attendance' => $attendance,
            'attendanceDaily' => $attendanceDaily,
            'daysOfMonth' => $daysOfMonth,
            'date' => $date->format('Y-m'),
            'company' => $company,
        ]);
    }

    /**
     * 勤怠情報を更新するメソッド。
     *
     * @param AttendanceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(AttendanceRequest $request)
    {
        $attendanceService = new AttendanceService();
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($request->year_month);

        try {
            DB::transaction(function () use ($request, $attendanceService, $date) {
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

                // 休憩時間を保存
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
        } catch (\Exception $e) {
            session()->flash('flash_message', '更新が失敗しました');
        }

        return redirect(route('admin.attendance_header.show', ['user_id' => $request->user_id, 'year_month' => $date]));
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
        return redirect(route('admin.attendance_header.show', ['user_id' => $user_id, 'year_month' => $date]));
    }

    /**
     * 勤怠情報を非同期で取得するメソッド。
     *
     * @param Request $request
     * @return AttendanceDailyResource
     */
    public function ajaxGetAttendanceInfo(Request $request)
    {
        // 指定された ID の日次勤怠データを取得または新規作成
        $attendanceDaily = AttendanceDaily::findOrNew($request->id);
        return AttendanceDailyResource::make($attendanceDaily);
    }

    /**
     * 勤怠情報の確定フラグを切り替えるメソッド。
     *
     * @param Request $request
     * @param int $user_id
     * @param string $year_month
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirm(Request $request, $user_id, $year_month)
    {
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($year_month);

        // 勤怠ヘッダーを取得
        $attendanceHeader = AttendanceHeader::where([
            'user_id' => $user_id,
            'year_month' => $date
        ])->firstOrFail();

        // 勤怠の確定状態をトグル（切り替え）
        $attendanceHeader->confirm_flag = !$attendanceHeader->confirm_flag;
        $attendanceHeader->save();

        // 状態に応じてメッセージを設定
        $message = $attendanceHeader->confirm_flag ? '勤怠を確定しました。' : '勤怠の確定を取り消しました。';
        session()->flash('flash_message', $message);

        // 勤怠詳細画面にリダイレクト
        return redirect()->route('admin.attendance_header.show', ['user_id' => $user_id, 'year_month' => $year_month]);
    }
}
