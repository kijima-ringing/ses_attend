<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\YearMonthRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceHeaderController extends Controller
{
    public function index(YearMonthRequest $request) {

        if(!$request->validated() || !isset($request)) {
            $date = Carbon::now()->startOfMonth();
        } else {
            $date = Carbon::create($request->year_month)->startOfMonth();
        }

        $dateForSearch = $date->format('Y-m-d');

        $query = User::orderBy('users.id');

        $users = $query->ledftJoinAttendanceHeader($dateForSearch);

        return view('admin.attendance_header.index')->with([
            'users' => $users,
            'date' => $date->format('Y-m'),
        ]);
    }
}
