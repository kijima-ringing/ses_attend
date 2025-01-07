<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
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
use App\Models\PaidLeaveDefault;
use App\Models\PaidLeaveRequest;

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

        // 検索用に年-月の最初の日付を取
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

        // 勤怠ヘッダーを取得または新規作成
        $attendance = AttendanceHeader::firstOrNew(['user_id' => $user_id, 'year_month' => $date]);

        // 有給休暇情報を取得
        $paidLeaveDefault = PaidLeaveDefault::where('user_id', $user_id)->first();

        // 勤怠ヘッダーが存在するかどうかをチェック
        $headerExists = AttendanceHeader::where('user_id', $user_id)
            ->whereDate('year_month', '=', $date->startOfMonth())
            ->exists();

        // 日次勤怠データを取得
        $attendanceDaily = AttendanceDaily::where('attendance_header_id', $attendance->id)
             ->with('breakTimes') // リレーションにより休憩時間を含む
            ->get()
            ->keyBy('work_date')
            ->toArray();

        // 月の日数を取得
        $daysOfMonth = $getDateService->getDaysOfMonth($date->copy());

        // 会社情報を取得
        $company = Company::company();

        // ビューにデータを渡す
        return view('admin.attendance_header.show')->with([
            'attendance' => $attendance,
            'attendanceDaily' => $attendanceDaily,
            'daysOfMonth' => $daysOfMonth,
            'date' => $date->format('Y-m'),
            'company' => $company,
            'confirmFlag' => $attendance->confirm_flag,
            'headerExists' => $headerExists, // レコードの存在状態をビューに渡す
            'paidLeaveDefault' => $paidLeaveDefault,
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
            // 勤怠ヘッダーを取得
            $attendanceHeader = AttendanceHeader::where([
                'user_id' => $request->user_id,
                'year_month' => $date
            ])->first();

            // 勤怠が確定済みかを確認
            if ($attendanceHeader && $attendanceHeader->confirm_flag === 1) {
                session()->flash('error_message', 'すでに勤怠は確定されています。');
                return response()->json(['success' => false, 'message' => 'すでに勤怠は確定されています。'],403);
            }

            // 有給休暇申請の場合、残日数のバリデーションを行う
            if ($request->attendance_class == '1') {
                // ユーザーの有給休暇デフォルト情報を取得
                $paidLeaveDefault = PaidLeaveDefault::where('user_id', $request->user_id)->first();

                if ($paidLeaveDefault) {
                    // 申請中の有給休暇数を取得
                    $pendingRequests = PaidLeaveRequest::whereHas('paidLeaveDefault', function($query) use ($request) {
                        $query->where('user_id', $request->user_id);
                    })
                    ->where('status', PaidLeaveRequest::STATUS_PENDING)
                    ->count();

                    // 残日数が申請中の数を超えていないかチェック
                    if ($paidLeaveDefault->remaining_days <= $pendingRequests) {
                        return response()->json([
                            'success' => false,
                            'message' => '有給休暇の残日数が不足しています。',
                            'errors' => ['paid_leave' => ['有給休暇の残日数が不足しています。申請中の有給休暇申請をご確認ください。']]
                        ], 422);
                    }
                }
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

                // 有給休暇申請の場合
                if ($request->attendance_class == '1' && $request->has('paid_leave_reason')) {
                    // ユーザーの有給休暇デフォルト情報を取得
                    $paidLeaveDefault = PaidLeaveDefault::where('user_id', $request->user_id)->first();

                    if ($paidLeaveDefault) {
                        // 休憩時間の取得（最初の休憩時間を使用）
                        $breakTimeId = null;
                        if (!empty($request->input('break_times'))) {
                            $breakTime = BreakTime::where('attendance_daily_id', $attendanceDaily->id)
                                ->first();
                            if ($breakTime) {
                                $breakTimeId = $breakTime->id;
                            }
                        }

                        // 有給休暇申請を作成
                        PaidLeaveRequest::create([
                            'paid_leave_default_id' => $paidLeaveDefault->id,
                            'attendance_daily_id' => $attendanceDaily->id,
                            'status' => PaidLeaveRequest::STATUS_PENDING,
                            'request_reason' => $request->paid_leave_reason,
                            'break_time_id' => $breakTimeId  // null を許容するように設定が必要
                        ]);
                    }
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

    /**
     * 勤怠情報を非同期で取得するメソッド。
     *
     * @param Request $request
     * @return AttendanceDailyResource
     */
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

    public function getRequest(Request $request)
    {
        try {
            // AttendanceDailyを取得
            $attendanceDaily = AttendanceDaily::whereHas('attendanceHeader', function($query) use ($request) {
                $query->where('user_id', $request->user_id);
            })
            ->where('work_date', $request->work_date)
            ->first();

            if (!$attendanceDaily) {
                return response()->json([
                    'status' => null,
                    'reason' => null,
                    'return_reason' => null
                ]);
            }

            // PaidLeaveRequestを取得
            $paidLeave = PaidLeaveRequest::where('attendance_daily_id', $attendanceDaily->id)
                ->first();

            if (!$paidLeave) {
                return response()->json([
                    'status' => null,
                    'reason' => null,
                    'return_reason' => null
                ]);
            }

            return response()->json([
                'status' => $paidLeave->status,
                'reason' => $paidLeave->request_reason,
                'return_reason' => $paidLeave->return_reason
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => '有給休暇申請情報の取得に失敗しました'
            ], 500);
        }
    }

    public function reapply(Request $request)
    {
        try {
            $validated = $request->validate([
                'work_date' => 'required|date',
                'user_id' => 'required|exists:users,id',
                'paid_leave_reason' => 'required|string|max:1000',
            ]);

            // 勤怠ヘッダーを取得
            $getDateService = new GetDateService();
            $date = $getDateService->createYearMonthFormat(date('Y-m', strtotime($validated['work_date'])));
            
            $attendanceHeader = AttendanceHeader::where([
                'user_id' => $validated['user_id'],
                'year_month' => $date
            ])->first();

            // 勤怠が確定済みかを確認
            if ($attendanceHeader && $attendanceHeader->confirm_flag === 1) {
                session()->flash('error_message', 'すでに勤怠は確定されています。');
                return response()->json(['success' => false, 'message' => 'すでに勤怠は確定されています。'], 403);
            }

            DB::transaction(function () use ($validated) {
                // AttendanceDailyを取得
                $attendanceDaily = AttendanceDaily::whereHas('attendanceHeader', function($query) use ($validated) {
                    $query->where('user_id', $validated['user_id']);
                })
                ->where('work_date', $validated['work_date'])
                ->firstOrFail();

                // PaidLeaveRequestを取得して更新
                $paidLeaveRequest = PaidLeaveRequest::where('attendance_daily_id', $attendanceDaily->id)
                    ->firstOrFail();

                $paidLeaveRequest->update([
                    'status' => PaidLeaveRequest::STATUS_PENDING,
                    'request_reason' => $validated['paid_leave_reason']
                ]);
            });

            session()->flash('flash_message', '有給休暇を再申請しました。');

            return response()->json([
                'success' => true,
                'message' => '有給休暇を再申請しました。'
            ]);

        } catch (\Exception $e) {
            session()->flash('error_message', '再申請処理に失敗しました。');
            return response()->json([
                'success' => false,
                'message' => '再申請処理に失敗しました。'
            ], 500);
        }
    }
}