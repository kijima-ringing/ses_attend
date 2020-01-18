<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use function foo\func;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'last_name', 'first_name', 'last_name_kana', 'first_name_kana', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function scopeLedftJoinAttendanceHeader($query, $date) {

        return $query->select(
            'users.id AS user_id',
            'users.last_name AS last_name',
            'users.first_name AS first_name',
            'attendance_header.working_days AS working_days',
            'attendance_header.scheduled_working_hours AS scheduled_working_hours',
            'attendance_header.overtime_hours AS overtime_hours',
            'attendance_header.working_hours AS working_hours'
        )
            ->leftjoin('attendance_header', function($join) use ($date) {
            $join->on('users.id', 'attendance_header.user_id')
                ->where('attendance_header.year_month', '=', $date);
        })->get();
    }

    public function getViewListForIndex(){
        return $this->select([
            'users.id',
            'users.last_name',
            'users.first_name',
            'users.last_name_kana',
            'users.first_name_kana',
            'users.email'
        ])
        ->whereNull('users.deleted_by')
        ->orderBy('users.id')
        ->get();
    }

    public function getUserInfo($user_id){
        return $this->select([
            'users.id',
            'users.last_name',
            'users.first_name',
            'users.last_name_kana',
            'users.first_name_kana',
            'users.email',
            DB::raw('GROUP_CONCAT(departments.id) AS department_ids'),
        ])
        ->leftJoin('department_members', 'users.id', '=', 'department_members.user_id')
        ->leftJoin('departments', 'department_members.department_id', '=', 'departments.id')
        ->where('users.id', $user_id)
        ->whereNull('users.deleted_by')
        ->groupBy('users.id')
        ->get()
        ->toArray();
    }

    // ユーザの追加・更新
    public static function createOrUpdate($request) {
        $data = [
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'last_name_kana' => $request->last_name_kana,
            'first_name_kana' => $request->first_name_kana,
        ];

        if (isset($request->email)) {
            $data['email'] = $request->email;
        }

        if (isset($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        return self::updateOrCreate(['id' => $request->id], $data);
    }
}
