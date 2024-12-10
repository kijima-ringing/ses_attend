<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDaily;
use App\Models\AttendanceHeader;
use App\Services\GetDateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceStampController extends Controller
{
    protected $getDateService;

    public function __construct(GetDateService $getDateService)
    {
        $this->getDateService = $getDateService;
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
            DB::transaction(function () {
                $now = Carbon::now();
                $user = Auth::user();

                // 当月の勤怠ヘッダーを取得または作成
                $header = AttendanceHeader::firstOrCreate([
                    'user_id' => $user->id,
                    'year_month' => $now->format('Y-m-01')
                ]);

                // 本日の日次データを作成
                AttendanceDaily::create([
                    'attendance_header_id' => $header->id,
                    'work_date' => $now->format('Y-m-d'),
                    'working_time' => $now->format('H:i:s'),
                    'attendance_class' => 0 // 通常勤務
                ]);
            });

            return response()->json(['success' => true]);
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
            DB::transaction(function () {
                $now = Carbon::now();
                $user = Auth::user();

                $header = AttendanceHeader::where('user_id', $user->id)
                    ->where('year_month', $now->format('Y-m-01'))
                    ->firstOrFail();

                $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                    ->where('work_date', $now->format('Y-m-d'))
                    ->firstOrFail();

                $daily->update([
                    'leave_time' => $now->format('H:i:s')
                ]);
            });

            return response()->json(['success' => true]);
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
            DB::transaction(function () {
                $now = Carbon::now();
                $user = Auth::user();

                $header = AttendanceHeader::where('user_id', $user->id)
                    ->where('year_month', $now->format('Y-m-01'))
                    ->firstOrFail();

                $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                    ->where('work_date', $now->format('Y-m-d'))
                    ->firstOrFail();

                $daily->breakTimes()->create([
                    'break_time_from' => $now->format('H:i:s')
                ]);
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
            DB::transaction(function () {
                $now = Carbon::now();
                $user = Auth::user();

                $header = AttendanceHeader::where('user_id', $user->id)
                    ->where('year_month', $now->format('Y-m-01'))
                    ->firstOrFail();

                $daily = AttendanceDaily::where('attendance_header_id', $header->id)
                    ->where('work_date', $now->format('Y-m-d'))
                    ->firstOrFail();

                $lastBreak = $daily->breakTimes()
                    ->whereNull('break_time_to')
                    ->latest()
                    ->firstOrFail();

                $lastBreak->update([
                    'break_time_to' => $now->format('H:i:s')
                ]);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}