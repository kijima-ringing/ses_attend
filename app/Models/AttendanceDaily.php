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
        'scheduled_working_hours',
        'overtime_hours',
        'working_hours',
        'memo',
        'locked_by',
        'locked_at'
    ];

    public static function monthOfDailies($attendance_header_id)
    {
        return self::where('attendance_header_id', $attendance_header_id)
            ->with('breakTimes') // リレーションで休憩時間も取得
            ->get()
            ->keyBy('work_date')
            ->toArray();
    }

    public function attendanceHeader()
    {
        return $this->belongsTo(AttendanceHeader::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function paidLeaveRequest()
    {
        return $this->hasOne(PaidLeaveRequest::class);
    }
}
