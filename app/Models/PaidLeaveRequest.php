<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuthorObservable;

class PaidLeaveRequest extends Model
{
    use AuthorObservable;

    const STATUS_PENDING = 0;    // 申請中
    const STATUS_APPROVED = 1;   // 承認済
    const STATUS_RETURNED = 2;   // 差し戻し

    protected $fillable = [
        'paid_leave_defaults_id',
        'attendance_daily_id',
        'break_time_id',
        'status',
        'request_reason',
        'return_reason',
    ];

    public function paidLeaveDefault()
    {
        return $this->belongsTo(PaidLeaveDefault::class, 'paid_leave_defaults_id');
    }

    public function attendanceDaily()
    {
        return $this->belongsTo(AttendanceDaily::class);
    }

    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }
}