<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
}
