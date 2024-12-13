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
        if (Auth::id() != $user_id) {
            return redirect()->route('stamp.index', [
                'user_id' => Auth::id(),
                'year_month' => $year_month
            ]);
        }

        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($year_month);

        // 勤怠ヘッダーを取得して確定状態を確認
        $header = AttendanceHeader::where('user_id', $user_id)
            ->where('year_month', $date->format('Y-m-01'))
            ->first();

        $confirm_flag = $header ? $header->confirm_flag : 0;

        return view('user.attendance_header.stamp', [
            'user_id' => $user_id,
            'date' => $date->format('Y-m'),
            'confirm_flag' => $confirm_flag
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

            $header = AttendanceHeader::where('user_id', $user->id)
                ->where('year_month', $now->format('Y-m-01'))
                ->first();

            if ($header && $header->confirm_flag == 1) {
                session()->flash('error_message', '勤怠が確定されています。');
                return response()->json(['success' => false, 'message' => '勤怠が確定されています。'], 400);
            }

            DB::transaction(function () use ($now, $user, $header) {
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
            });

            session()->flash('flash_message', '出勤時間を記録しました。');

            return response()->json(['success' => true, 'message' => '出勤時間を記録しました。']);
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

            $header = AttendanceHeader::where('user_id', $user->id)
                ->where('year_month', $now->format('Y-m-01'))
                ->first();

            if ($header && $header->confirm_flag == 1) {
                session()->flash('error_message', '勤怠が確定されています。');
                return response()->json(['success' => false, 'message' => '勤怠が確定されています。'], 400);
            }

            DB::transaction(function () use ($now, $user, $header) {
                $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                    ->where('work_date', $now->format('Y-m-d'))
                    ->firstOrFail();

                $daily->update([
                    'leave_time' => $now->format('H:i:s')
                ]);

                $updateDailyParams = $this->attendanceService->getUpdateDailyParams([
                    'working_time' => $daily->working_time,
                    'leave_time' => $daily->leave_time,
                    'break_times' => $daily->breakTimes->toArray(),
                    'attendance_class' => $daily->attendance_class
                ]);

                $daily->fill($updateDailyParams)->save();

                $updateMonthParams = $this->attendanceService->getUpdateMonthParams($header->id);
                $header->fill($updateMonthParams)->save();
            });

            session()->flash('flash_message', '退勤時間を記録しました。');

            return response()->json(['success' => true, 'message' => '退勤時間を記録しました。']);
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
                return response()->json(['success' => false, 'message' => '認証エラーが発生しました。'], 401);
            }

            $header = AttendanceHeader::where('user_id', $user->id)
                ->where('year_month', $now->format('Y-m-01'))
                ->first();

            if ($header && $header->confirm_flag == 1) {
                session()->flash('error_message', '勤怠が確定されています。');
                return response()->json(['success' => false, 'message' => '勤怠が確定されています。'], 400);
            }

            DB::transaction(function () use ($now, $user, $header) {
                $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                    ->where('work_date', $now->format('Y-m-d'))
                    ->firstOrFail();

                DB::table('break_times')
                    ->where('attendance_daily_id', $daily->id)
                    ->delete();

                BreakTime::create([
                    'attendance_daily_id' => $daily->id,
                    'break_time_from' => $now->format('H:i:s'),
                    'created_by' => $user->id,
                    'updated_by' => $user->id
                ]);
            });

            session()->flash('flash_message', '休憩開始を記録しました。');

            return response()->json(['success' => true, 'message' => '休憩開始を記録しました。']);
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
            $now = Carbon::now('Asia/Tokyo');
            $user = Auth::user();

            if (!$user) {
                return response()->json(['success' => false, 'message' => '認証エラーが発生しました。'], 401);
            }

            $header = AttendanceHeader::where('user_id', $user->id)
                ->where('year_month', $now->format('Y-m-01'))
                ->first();

            if ($header && $header->confirm_flag == 1) {
                session()->flash('error_message', '勤怠が確定されています。');
                return response()->json(['success' => false, 'message' => '勤怠が確定されています。'], 400);
            }

            DB::transaction(function () use ($now, $user, $header) {
                $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                    ->where('work_date', $now->format('Y-m-d'))
                    ->firstOrFail();

                $breakTime = BreakTime::where('attendance_daily_id', $daily->id)
                    ->orderBy('break_time_from', 'desc')
                    ->firstOrFail();

                $breakTime->update([
                    'break_time_to' => $now->format('H:i:s'),
                    'updated_by' => $user->id
                ]);
            });

            session()->flash('flash_message', '休憩終了を記録しました。');

            return response()->json(['success' => true, 'message' => '休憩終了を記録しました。']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}