<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDaily;
use App\Models\AttendanceHeader;
use App\Models\BreakTime;
use App\Services\GetDateService;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceStampController extends Controller
{
    protected $getDateService;
    protected $attendanceService;

    public function __construct(GetDateService $getDateService, AttendanceService $attendanceService)
    {
        $this->getDateService = $getDateService;
        $this->attendanceService = $attendanceService;
    }

    /**
     * 打刻画面を表示
     */
    public function index($user_id, $year_month)
    {
        // ログインユーザーと異なるユーザーIDの場合はリダイレクト
        if (Auth::id() != $user_id) {
            return redirect()->route('stamp.index', [
                'user_id' => Auth::id(),
                'year_month' => $year_month
            ]);
        }

        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($year_month);

        return view('user.attendance_header.stamp', [
            'user_id' => $user_id,
            'date' => $date->format('Y-m')
        ]);
    }

    /**
     * 出勤打刻を処理
     */
    public function startWork(Request $request)
    {
        try {
            $now = Carbon::now('Asia/Tokyo');
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => '認証エラーが発生しました。'], 401);
            }

            DB::transaction(function () use ($now, $user) {
                try {
                    // 当月の勤怠ヘッダーを取得または作成
                    $header = AttendanceHeader::firstOrCreate([
                        'user_id' => $user->id,
                        'year_month' => $now->format('Y-m-01')
                    ]);

                    // 既存の日次データをチェック
                    $existingDaily = AttendanceDaily::where('attendance_header_id', $header->id)
                        ->where('work_date', $now->format('Y-m-d'))
                        ->first();

                    if ($existingDaily) {
                        // 関連する休憩時間を削除
                        DB::table('break_times')
                            ->where('attendance_daily_id', $existingDaily->id)
                            ->delete();

                        // 日次データを削除
                        DB::table('attendance_daily')
                            ->where('id', $existingDaily->id)
                            ->delete();
                    }

                    // 新規の日次データを作成
                    $daily = AttendanceDaily::create([
                        'attendance_header_id' => $header->id,
                        'work_date' => $now->format('Y-m-d'),
                        'working_time' => $now->format('H:i:s'),
                        'attendance_class' => 0
                    ]);

                    // 勤怠集計を更新
                    $updateMonthParams = $this->attendanceService->getUpdateMonthParams($header->id);
                    $header->fill($updateMonthParams)->save();

                } catch (\Exception $e) {
                    throw $e;
                }
            });

            return response()->json(['success' => true, 'message' => '出勤を記録しました。']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 退勤打刻を処理
     */
    public function endWork(Request $request)
    {
        try {
            $now = Carbon::now('Asia/Tokyo');
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => '認証エラーが発生しました。'], 401);
            }

            DB::transaction(function () use ($now, $user) {
                // 当月の勤怠ヘッダーを検索
                $header = AttendanceHeader::where('user_id', $user->id)
                    ->where('year_month', $now->format('Y-m-01'))
                    ->firstOrFail();

                // 本日の日次データを検索
                $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                    ->where('work_date', $now->format('Y-m-d'))
                    ->firstOrFail();

                // 退勤時刻を更新
                $daily->update([
                    'leave_time' => $now->format('H:i:s')
                ]);

                // 日次データの計算パラメータを取得
                $updateDailyParams = $this->attendanceService->getUpdateDailyParams([
                    'working_time' => $daily->working_time,
                    'leave_time' => $daily->leave_time,
                    'break_times' => $daily->breakTimes->toArray(),
                    'attendance_class' => $daily->attendance_class
                ]);

                // 日次データを更新
                $daily->fill($updateDailyParams)->save();

                // 勤怠集計を更新
                $updateMonthParams = $this->attendanceService->getUpdateMonthParams($header->id);
                $header->fill($updateMonthParams)->save();
            });

            return response()->json(['success' => true, 'message' => '退勤を記録しました。']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 休憩開始を処理
     */
    public function startBreak(Request $request)
    {
        try {
            $now = Carbon::now('Asia/Tokyo');
            $user = Auth::user();

            if (!$user) {
                \Log::error('User not authenticated');
                return response()->json(['success' => false, 'message' => '認証エラーが発生しました。'], 401);
            }

            DB::transaction(function () use ($now, $user) {
                \Log::info('Transaction started for user: ' . $user->id);

                $header = AttendanceHeader::where('user_id', $user->id)
                    ->where('year_month', $now->format('Y-m-01'))
                    ->firstOrFail();
                \Log::info('AttendanceHeader found: ' . $header->id);

                $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                    ->where('work_date', $now->format('Y-m-d'))
                    ->firstOrFail();
                \Log::info('AttendanceDaily found: ' . $daily->id);

                DB::table('break_times')
                    ->where('attendance_daily_id', $daily->id)
                    ->delete();
                \Log::info('Existing break times deleted for daily ID: ' . $daily->id);

                BreakTime::create([
                    'attendance_daily_id' => $daily->id,
                    'break_time_from' => $now->format('H:i:s'),
                    'created_by' => $user->id,
                    'updated_by' => $user->id
                ]);
                \Log::info('New break time created for daily ID: ' . $daily->id);
            });

            return response()->json(['success' => true, 'message' => '休憩開始を記録しました。']);
        } catch (\Exception $e) {
            \Log::error('Error in startBreak: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 休憩終了を処理
     */
    public function endBreak(Request $request)
    {
    }
}