<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuthorObservable;

class PaidLeaveDefault extends Model
{
    use AuthorObservable;

    protected $fillable = [
        'user_id',
        'default_days',
        'remaining_days',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paidLeaveRequests()
    {
        return $this->hasMany(PaidLeaveRequest::class, 'paid_leave_defaults_id');
    }
} 