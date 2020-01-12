<?php

namespace App\Services;

use Illuminate\Support\Carbon;

class GetDateService
{
    public function checkYearMonthFormat($yearMonth) {
        if (Carbon::hasFormat($yearMonth, 'Y-m')) {
            return true;
        } else {
            return false;
        }
    }

    public function createYearMonthFormat($yearMonth) {
        if ($this->checkYearMonthFormat($yearMonth)) {
            $date = Carbon::createFromFormat('Y-m', $yearMonth)->startOfMonth();
        } else {
            $date = Carbon::now()->startOfMonth();
        }

        return $date;
    }
}
