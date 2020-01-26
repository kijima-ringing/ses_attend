<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\AuthorObservable;

class Company extends Model
{
    use AuthorObservable;

    const TIME_FRACTION_LIST = [
        '1' => 'なし',
        '15' => '15分',
        '30' => '30分'
    ];

    const TIME_FRACTION_VALUES = [1, 15, 30];

    protected $table = 'company';

    protected $fillable = [
        'base_time_from',
        'base_time_to',
        'time_fraction',
    ];

    public static function company() {
        return self::findOrFail(1);
    }

    public function getBaseTimeFromAttribute($value)
    {
        return substr($value, 0, -3);
    }

    public function getBaseTimeToAttribute($value)
    {
        return substr($value, 0, -3);
    }

}
