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
        if (strlen($time) == 8) {
            return substr($time, -8, 5);
        } else if (strlen($time) == 9) {
            return substr($time, -9, 6);
        } else if (strlen($time) == 0) {
            return '00:00';
        }
    }

    public static function daysFormat($days) {
        if (isset($days)) {
            return $days;
        } else {
            return 0;
        }
    }
}
