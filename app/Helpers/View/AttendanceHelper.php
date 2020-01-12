<?php

namespace App\Helpers\View;

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
}
