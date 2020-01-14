<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'company';

    protected $fillable = [
        'base_time_from',
        'base_time_to',
        'time_fraction',
    ];

    public static function company() {
        return self::findOrFail(1);
    }

}
