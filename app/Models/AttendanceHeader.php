<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuthorObservable;

class AttendanceHeader extends Model
{
    use AuthorObservable;

    CONST FRACTION_1 = 1;
    CONST FRACTION_15 = 15;
    CONST FRACTION_30 = 30;

    protected $table = 'attendance_header';

    protected $fillable = [
        'user_id',
        'year_month',
        'working_days',
        'scheduled_working_hours',
        'overtime_hours',
        'working_hours',
    ];

    public function attendancDailies() {
        return $this->hasMany('App\Models\AttendanceDaily', 'attendance_header_id');
    }

    public function user() {
        return $this->belongsTo('App\Models\User');
    }
}
