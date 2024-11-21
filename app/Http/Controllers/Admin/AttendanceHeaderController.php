<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Http\Resources\AttendanceDailyResource;
use App\Models\AttendanceDaily;
use App\Models\AttendanceHeader;
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
        // 日付操作サービスを利用してフォーマットされた日付を取得
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($yearMonth);

        // ユーザーと年月に基づいて勤怠ヘッダーを取得または新規作成
        $attendance = AttendanceHeader::firstOrNew(['user_id' => $user_id, 'year_month' => $date]);

        // 勤怠ヘッダー ID に基づき日次勤怠を取得
        $attendanceDaily = AttendanceDaily::monthOfDailies($attendance->id);

        // 月の日数リストを取得
        $daysOfMonth = $getDateService->getDaysOfMonth($date->copy());

        // 会社情報を取得
        $company = Company::company();

        // ビューに必要なデータを渡して表示
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

        // フォーマットされた年月データを取得
        $date = $getDateService->createYearMonthFormat($request->year_month);

        try {
            // トランザクション処理を開始
            DB::transaction(function () use ($request, $attendanceService, $date) {
                // 勤怠ヘッダーを取得または作成
                $attendanceHeader = AttendanceHeader::firstOrCreate(['user_id' => $request->user_id, 'year_month' => $date]);

                // 労働時間計算処理（日次）のパラメータを取得
                $requestParams = $request->validated();
                $updateDailyParams = $attendanceService->getUpdateDailyParams($requestParams);

                // 日次勤怠情報を更新または新規作成
                $attendanceDaily = AttendanceDaily::firstOrNew(['attendance_header_id' => $attendanceHeader->id, 'work_date' => $request->work_date]);
                $attendanceDaily->fill($updateDailyParams)->saveOrfail();

                // 端数処理設定を取得
                $company = Company::find(1);

                // 労働時間計算処理（月次）のパラメータを取得（端数処理を考慮）
                if ($company->rounding_scope == 0) { // 全体適用
                    $updateMonthParams = $attendanceService->getUpdateMonthParamsWithGlobalRounding($attendanceHeader->id);
                } else { // 日別適用
                    $updateMonthParams = $attendanceService->getUpdateMonthParams($attendanceHeader->id);
                }

                // 勤怠ヘッダー情報を更新
                $attendanceHeader->fill($updateMonthParams)->saveOrFail();
            });
        } catch (\Exception $e) {
            // エラーメッセージをセッションに保存
            session()->flash('flash_message', '更新が失敗しました');
        }

        // 勤怠詳細画面にリダイレクト
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
