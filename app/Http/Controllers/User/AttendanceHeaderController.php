<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Http\Resources\AttendanceDailyResource;
use App\Models\AttendanceDaily;
use App\Models\AttendanceHeader;
use App\Models\Company;
use App\Services\AttendanceService;
use App\Services\GetDateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceHeaderController extends Controller
{
    public function show($user_id, $yearMonth) {

        $getDateService = new GetDateService();

        $date = $getDateService->createYearMonthFormat($yearMonth);

        $attendance = AttendanceHeader::firstOrNew(['user_id' => $user_id, 'year_month' => $date]);

        $atendanceDaily = AttendanceDaily::monthOfDailys($attendance->id);

        $daysOfMonth = $getDateService->getDaysOfMonth($date->copy());

        $company = Company::company();

        return view('user.attendance_header.show')->with([
            'attendance' => $attendance,
            'atendanceDaily' => $atendanceDaily,
            'daysOfMonth' => $daysOfMonth,
            'date' => $date->format('Y-m'),
            'company' => $company,
        ]);
    }

    public function update(AttendanceRequest $request) {

        $attendanceService = new AttendanceService();
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($request->year_month);

        try {
            DB::transaction(function () use ($request, $attendanceService, $date) {
                // 抽出①を実行
                $attendanceHeader = AttendanceHeader::firstOrCreate(['user_id' => $request->user_id, 'year_month' => $date]);

                // 労働時間計算処理(日)
                $requestParams = $request->validated();
                $updateDailyParams = $attendanceService->getUpdateDailyParams($requestParams);

                // 更新処理①を実行
                $attendanceDaily = Attendancedaily::firstOrNew(['attendance_header_id' => $attendanceHeader->id, 'work_date' => $request->work_date]);
                $attendanceDaily->fill($updateDailyParams)->saveOrfail();

                // 労働時間計算処理(月)
                $updateMonthParams = $attendanceService->getUpdateMonthParams($attendanceHeader->id);

                // 更新処理②を実行
                $attendanceHeader->fill($updateMonthParams)->saveOrFail();
            });
        } catch (\Exception $e) {
            session()->flash('flash_message', '更新が失敗しました');
        }

        return redirect(route('user.attendance_header.show', ['user_id' => $request->user_id, 'year_month' => $date]));
    }

    public function destroy($user_id, $year_month, $work_date) {
        $attendanceService = new AttendanceService();

        // 抽出①を実行
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($year_month);
        $attendanceHeader = AttendanceHeader::firstOrCreate(['user_id' => $user_id, 'year_month' => $date]);

        // 更新処理④
        Attendancedaily::where(['attendance_header_id' => $attendanceHeader->id, 'work_date' => $work_date])->delete();

        // 労働時間計算処理(月)
        $updateMonthParams = $attendanceService->getUpdateMonthParams($attendanceHeader->id);

        // 更新処理②を実行
        $attendanceHeader->fill($updateMonthParams)->saveOrFail();

        return redirect(route('user.attendance_header.show', ['user_id' => $user_id, 'year_month' => $date]));
    }

    public function ajaxGetAttendanceInfo(Request $request) {
        $attendanceDaily = AttendanceDaily::findOrNew($request->id);
        return AttendanceDailyResource::make($attendanceDaily);
    }

    public function AttendanceValidate(AttendanceRequest $request) {
        return response()->json($request->validated());
    }
}
