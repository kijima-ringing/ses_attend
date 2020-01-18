<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        '*'
    ];

    public function getDepartmentSelectList(){
        return $this->select([
            'departments.id',
            'departments.name',
        ])
        ->get();
    }
}
