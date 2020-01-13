<?php

namespace App\Helpers\View;

use Illuminate\Support\Carbon;

class AttendanceHelper
{
    public static function attendanceClass($attendance_class) {
        switch ($attendance_class) {
            case 0:
                return '通常勤務';
                break;
            case 1:
                return '有給休暇';
                break;
            case 2:
                return '欠勤';
                break;
        }
    }

    public static function timeFormat($time) {
        return substr($time, -8, 5);
    }
}
