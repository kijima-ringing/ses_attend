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

        $today = Carbon::now('Asia/Tokyo')->format('Y-m-d');
        $daily = $header ? AttendanceDaily::where('attendance_header_id', $header->id)
            ->where('work_date', $today)
            ->first() : null;

        $breakTime = $daily ? BreakTime::where('attendance_daily_id', $daily->id)
            ->orderBy('break_time_from', 'desc')
            ->first() : null;

        // 出勤ボタンの非活性化条件
        $workStartDisabled = $daily && $daily->working_time ? true : false;

        // 退勤ボタンの非活性化条件
        $workEndDisabled = $daily && $daily->leave_time ? true : false;

        // 休憩開始ボタンの非活性化条件
        $breakStartDisabled = ($breakTime && $breakTime->break_time_from) || ($daily && $daily->working_time && $daily->leave_time) ? true : false;

        // 休憩終了ボタンの非活性化条件
        $breakEndDisabled = ($breakTime && $breakTime->break_time_to) || ($daily && $daily->working_time && $daily->leave_time) ? true : false;

        return view('user.attendance_header.stamp', [
            'user_id' => $user_id,
            'date' => $date->format('Y-m'),
            'confirm_flag' => $confirm_flag,
            'workStartDisabled' => $workStartDisabled,
            'workEndDisabled' => $workEndDisabled,
            'breakStartDisabled' => $breakStartDisabled,
            'breakEndDisabled' => $breakEndDisabled
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

            // 勤怠ヘッダーを取得または作成
            $header = AttendanceHeader::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'year_month' => $now->format('Y-m-01')
                ],
                [
                    'confirm_flag' => 0 // 初期値を設定
                ]
            );

            if ($header->confirm_flag == 1) {
                session()->flash('error_message', '勤怠が確定されています。');
                return response()->json(['success' => false, 'message' => '勤怠が確定されています。'], 400);
            }

            // 既存の日次データをチェック
            $existingDaily = AttendanceDaily::where('attendance_header_id', $header->id)
                ->where('work_date', $now->format('Y-m-d'))
                ->first();

            if ($existingDaily) {
                session()->flash('error_message', '既に出勤記録があります。');
                return response()->json(['success' => false, 'message' => '既に出勤記録があります。'], 400);
            }

            DB::transaction(function () use ($now, $user, $header) {
                // 新規の日次データを作成
                $daily = AttendanceDaily::create([
                    'attendance_header_id' => $header->id,
                    'work_date' => $now->format('Y-m-d'),
                    'working_time' => $now->format('H:i:s'),
                    'attendance_class' => 0,
                    'locked_by' => null, // ロック解除
                    'locked_at' => null  // ロック解除
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

            if (!$header) {
                session()->flash('error_message', '出勤時間が記録されていないため、退勤できません。');
                return response()->json(['success' => false, 'message' => '出勤時間が記録されていないため、退勤できません。'], 400);
            }

            $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                ->where('work_date', $now->format('Y-m-d'))
                ->first();

            if ($daily && $daily->locked_at && $daily->locked_by !== $user->id) {
                $lockedAt = Carbon::parse($daily->locked_at);
                if ($lockedAt->diffInMinutes($now) < 5) {
                    session()->flash('error_message', '勤怠データは現在ロックされています。しばらくしてから再試行してください。');
                    return response()->json(['locked' => true, 'message' => '勤怠データは現在ロックされています。しばらくしてから再試行してください。'], 423);
                }
            }

            if ($header->confirm_flag == 1) {
                session()->flash('error_message', '勤怠が確定されています。');
                return response()->json(['success' => false, 'message' => '勤怠が確定されています。'], 400);
            }

            if (!$daily || !$daily->working_time) {
                session()->flash('error_message', '出勤時間が記録されていないため、退勤できません。');
                return response()->json(['success' => false, 'message' => '出勤時間が記録されていないため、退勤できません。'], 400);
            }

            $breakTimes = BreakTime::where('attendance_daily_id', $daily->id)->get();

            foreach ($breakTimes as $breakTime) {
                if (!$breakTime->break_time_from || !$breakTime->break_time_to) {
                    session()->flash('error_message', '休憩時間が正しく記録されていないため、退勤できません。');
                    return response()->json(['success' => false, 'message' => '休憩時間が正しく記録されていないため、退勤できません。'], 400);
                }
            }

            DB::transaction(function () use ($now, $daily, $header) {
                $daily->update([
                    'leave_time' => $now->format('H:i:s'),
                    'locked_by' => null, // ロック解除
                    'locked_at' => null  // ロック解除
                ]);

                $updateDailyParams = $this->attendanceService->getUpdateDailyParams([
                    'working_time' => $daily->working_time,
                    'leave_time' => $daily->leave_time,
                    'break_times' => $daily->breakTimes->toArray(),
                    'attendance_class' => $daily->attendance_class
                ]);

                $daily->fill($updateDailyParams)->save();

                $updateMonthParams = $this->attendanceService->getUpdateMonthParams($daily->attendance_header_id);
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

            if (!$header) {
                session()->flash('error_message', '出勤時間が記録されていないため、休憩を開始できません。');
                return response()->json(['success' => false, 'message' => '出勤時間が記録されていないため、休憩を開始できません。'], 400);
            }

            $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                ->where('work_date', $now->format('Y-m-d'))
                ->first();

            if ($daily && $daily->locked_at && $daily->locked_by !== $user->id) {
                $lockedAt = Carbon::parse($daily->locked_at);
                if ($lockedAt->diffInMinutes($now) < 5) {
                    session()->flash('error_message', '勤怠データは現在ロックされています。しばらくしてから再試行してください。');
                    return response()->json(['locked' => true, 'message' => '勤怠データは現在ロックされています。しばらくしてから再試行してください。'], 423);
                }
            }

            if ($header->confirm_flag == 1) {
                session()->flash('error_message', '勤怠が確定されています。');
                return response()->json(['success' => false, 'message' => '勤怠が確定されています。'], 400);
            }

            if (!$daily || !$daily->working_time) {
                session()->flash('error_message', '出勤時間が記録されていないため、休憩を開始できません。');
                return response()->json(['success' => false, 'message' => '出勤時間が記録されていないため、休憩を開始できません。'], 400);
            }

            DB::transaction(function () use ($now, $user, $daily) {
                DB::table('break_times')
                    ->where('attendance_daily_id', $daily->id)
                    ->delete();

                BreakTime::create([
                    'attendance_daily_id' => $daily->id,
                    'break_time_from' => $now->format('H:i:s'),
                    'created_by' => $user->id,
                    'updated_by' => $user->id
                ]);

                // ロック解除
                $daily->update([
                    'locked_by' => null,
                    'locked_at' => null
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

            if (!$header) {
                session()->flash('error_message', '休憩開始時間が記録されていないため、休憩を終了できません。');
                return response()->json(['success' => false, 'message' => '休憩開始時間が記録されていないため、休憩を終了できません。'], 400);
            }

            $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                ->where('work_date', $now->format('Y-m-d'))
                ->first();

            if (!$daily) {
                session()->flash('error_message', '出勤時間が記録されていないため、休憩を終了できません。');
                return response()->json(['success' => false, 'message' => '出勤時間が記録されていないため、休憩を終了できません。'], 400);
            }

            if ($daily && $daily->locked_at && $daily->locked_by !== $user->id) {
                $lockedAt = Carbon::parse($daily->locked_at);
                if ($lockedAt->diffInMinutes($now) < 5) {
                    session()->flash('error_message', '勤怠データは現在ロックされています。しばらくしてから再試行してください。');
                    return response()->json(['locked' => true, 'message' => '勤怠データは現在ロックされています。しばらくしてから再試行してください。'], 423);
                }
            }

            if ($header->confirm_flag == 1) {
                session()->flash('error_message', '勤怠が確定されています。');
                return response()->json(['success' => false, 'message' => '勤怠が確定されています。'], 400);
            }

            $breakTime = BreakTime::where('attendance_daily_id', $daily->id)
                ->orderBy('break_time_from', 'desc')
                ->first();

            if (!$breakTime || !$breakTime->break_time_from) {
                session()->flash('error_message', '休憩開始時間が記録されていないため、休憩を終了できません。');
                return response()->json(['success' => false, 'message' => '休憩開始時間が記録されていないため、休憩を終了できません。'], 400);
            }

            DB::transaction(function () use ($now, $breakTime, $user, $daily) {
                $breakTime->update([
                    'break_time_to' => $now->format('H:i:s'),
                    'updated_by' => $user->id
                ]);

                // ロック解除
                $daily->update([
                    'locked_by' => null,
                    'locked_at' => null
                ]);
            });

            session()->flash('flash_message', '休憩終了を記録しました。');

            return response()->json(['success' => true, 'message' => '休憩終了を記録しました。']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}