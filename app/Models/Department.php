<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuthorObservable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use AuthorObservable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public function getDepartmentSelectList(){
        return $this->select([
            'departments.id',
            'departments.name',
        ])
        ->get();
    }
}
