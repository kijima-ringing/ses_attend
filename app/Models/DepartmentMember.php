<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuthorObservable;

class DepartmentMember extends Model
{
    use AuthorObservable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '*'
    ];

    // 社員IDから部署を取得
    public static function getDepartments($user_id) {

        return self::select([
            'departments.id',
            'departments.name',
        ])
        ->join('departments', 'department_members.department_id', '=', 'departments.id')
        ->where('department_members.user_id', $user_id)
        ->get()
        ->toArray();
    }
}
