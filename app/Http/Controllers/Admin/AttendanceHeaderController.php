<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Models\AttendanceDaily;
use App\Models\AttendanceHeader;
use App\Models\Company;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\GetDateService;
use Illuminate\Http\Request;

class AttendanceHeaderController extends Controller
{
    public function index(Request $request) {

        $getDateService = new GetDateService();

        $date = $getDateService->createYearMonthFormat($request->year_month);

        $dateForSearch = $date->format('Y-m-d');

        $query = User::orderBy('users.id');

        $users = $query->ledftJoinAttendanceHeader($dateForSearch);

        return view('admin.attendance_header.index')->with([
            'users' => $users,
            'date' => $date->format('Y-m'),
        ]);
    }

    public function show($user_id, $yearMonth) {

        $getDateService = new GetDateService();

        $date = $getDateService->createYearMonthFormat($yearMonth);

        $attendance = AttendanceHeader::firstOrNew(['user_id' => $user_id, 'year_month' => $date]);

        $atendanceDaily = AttendanceDaily::monthOffailys($attendance->id);

        $daysOfMonth = $getDateService->getDaysOfMonth($date->copy());

        $company = Company::company();

        return view('admin.attendance_header.show')->with([
            'attendance' => $attendance,
            'atendanceDaily' => $atendanceDaily,
            'daysOfMonth' => $daysOfMonth,
            'date' => $date->format('Y-m'),
            'company' => $company,
        ]);
    }

    public function update(AttendanceRequest $request) {

        $attendanceService = new AttendanceService();

        // 抽出①を実行
        $getDateService = new GetDateService();
        $date = $getDateService->createYearMonthFormat($request->year_month);
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

        return redirect(route('admin.attendance_header.show', ['user_id' => $request->user_id, 'year_month' => $date]));
    }
}
