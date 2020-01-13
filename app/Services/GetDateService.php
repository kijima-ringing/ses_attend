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

    public function getDaysOfMonth($date) {
        $endDate = $date->copy()->endOfMonth()->day;
        $dateOfMonth = [];

        for ($i = 0; $i < $endDate; $i++, $date->addDay()) {
            $dateOfMonth[] =
                [
                    'day' => $date->day,
                    'dayOfWeek' => $this->getDayOfWeek($date->dayOfWeek),
                    'work_date' => $date->toDateString()
                ];
        }

        return $dateOfMonth;
    }

    public function getDayOfWeek($dayOfWeekNumber) {
        $dayOfWeek = [
            '日', '月', '火', '水', '木','金', '土'
        ];

        return $dayOfWeek[$dayOfWeekNumber];
    }

    public static function diffInHours($time_to, $time_from) {
        $from = new carbon($time_from);
        $to = Carbon::parse($time_to);

        return $from->diffInHours($to);
    }

    public static function getHourInt($time) {
        $carbon = Carbon::create($time);
        return $carbon->hour + ($carbon->minute / 60);
    }
}
