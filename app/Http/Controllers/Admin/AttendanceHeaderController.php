<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceHeader;
use App\Models\User;
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

        $atendanceDaily = $attendance->attendancDailies;

        $daysOfMonth = $getDateService->getDaysOfMonth($date);

        return view('admin.attendance_header.show')->with([
            'attendance' => $attendance,
            'atendanceDaily' => $atendanceDaily,
            'daysOfMonth' => $daysOfMonth,
            'date' => $date->format('Y-m')
        ]);
    }
}
