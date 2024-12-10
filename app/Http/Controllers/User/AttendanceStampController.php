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
            $now = Carbon::now();
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
        $now = Carbon::now()->timezone('Asia/Tokyo');
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

            // 勤怠集計を更新
            $updateMonthParams = $this->attendanceService->getUpdateMonthParams($header->id);
            $header->fill($updateMonthParams)->save();
        });

        return response()->json(['success' => true, 'message' => '退勤を記録しました。']);
    } catch (\Exception $e) {
        \Log::error('Error in endWork: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

    /**
     * 休憩開始を処理
     */
    public function startBreak(Request $request)
    {
        try {
            $now = Carbon::now();
            $user = Auth::user();

            DB::transaction(function () use ($now, $user) {
                $header = AttendanceHeader::where('user_id', $user->id)
                    ->where('year_month', $now->format('Y-m-01'))
                    ->firstOrFail();

                $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                    ->where('work_date', $now->format('Y-m-d'))
                    ->firstOrFail();

                if (empty($daily->working_time)) {
                    throw new \Exception('出勤��録がありません。');
                }

                if (!empty($daily->leave_time)) {
                    throw new \Exception('既に退勤済みです。');
                }

                // 既に休憩中かチェック
                $ongoingBreak = $daily->breakTimes()
                    ->whereNull('break_time_to')
                    ->first();
                if ($ongoingBreak) {
                    throw new \Exception('既に休憩中です。');
                }

                $daily->breakTimes()->create([
                    'break_time_from' => $now->format('H:i:s')
                ]);

                // 勤怠集計を更新
                $updateMonthParams = $this->attendanceService->getUpdateMonthParams($header->id);
                $header->fill($updateMonthParams)->save();
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 休憩終了を処理
     */
    public function endBreak(Request $request)
    {
        try {
            $now = Carbon::now();
            $user = Auth::user();

            DB::transaction(function () use ($now, $user) {
                $header = AttendanceHeader::where('user_id', $user->id)
                    ->where('year_month', $now->format('Y-m-01'))
                    ->firstOrFail();

                $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                    ->where('work_date', $now->format('Y-m-d'))
                    ->firstOrFail();

                if (empty($daily->working_time)) {
                    throw new \Exception('出勤記録がありません。');
                }

                if (!empty($daily->leave_time)) {
                    throw new \Exception('既に退勤済みです。');
                }

                $lastBreak = $daily->breakTimes()
                    ->whereNull('break_time_to')
                    ->latest()
                    ->first();

                if (!$lastBreak) {
                    throw new \Exception('休憩開始記録がありません。');
                }

                $lastBreak->update([
                    'break_time_to' => $now->format('H:i:s')
                ]);

                // 勤怠集計を更新
                $updateMonthParams = $this->attendanceService->getUpdateMonthParams($header->id);
                $header->fill($updateMonthParams)->save();
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}