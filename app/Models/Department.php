<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuthorObservable;

class Department extends Model
{
    use AuthorObservable;

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
