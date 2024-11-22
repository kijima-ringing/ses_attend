<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuthorObservable;

class BreakTime extends Model
{
    use AuthorObservable;

    protected $table = 'break_times';

    protected $fillable = [
        'attendance_daily_id',
        'break_time_from',
        'break_time_to',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attendance record that owns the break time.
     */
    public function attendanceDaily()
    {
        return $this->belongsTo(AttendanceDaily::class, 'attendance_daily_id');
    }
}
