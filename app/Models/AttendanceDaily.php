<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuthorObservable;

class AttendanceDaily extends Model
{
    use AuthorObservable;

    protected $table = 'attendance_daily';

    const NORMAL_WORKING = 0;
    const PAID_HOLIDAYS = 1;
    const ABSENT_WORKING = 2;

    protected $fillable = [
        'attendance_header_id',
        'work_date',
        'attendance_class',
        'working_time',
        'leave_time',
        'break_time_from',
        'break_time_to',
        'memo',
        'scheduled_working_hours',
        'overtime_hours',
        'working_hours',
    ];

    public static function monthOfDailys($attendance_header_id) {
        return array_column(self::where('attendance_header_id', '=', $attendance_header_id)->get()->toArray(), null, 'work_date');
    }
}
