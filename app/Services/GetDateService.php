<?php

namespace App\Services;

use App\Models\AttendanceHeader;
use App\Models\Company;
use Illuminate\Support\Carbon;

class GetDateService
{
    public static function getNowYearMonth() {
        return Carbon::now()->format('Y-m');
    }

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

    public function getHourInt($time) {
        $company = Company::company();
        $carbon = Carbon::create($time);

        $min = $this->ceilTime($carbon->minute, $company);

        return $carbon->hour + ($min / 60);
    }

    public function ceilTime($time, $company) {
        $ceil = $company->time_fraction;
        if ($ceil == AttendanceHeader::FRACTION_1) {
            $return = $time;
        } else if($ceil == AttendanceHeader::FRACTION_15) {
            $return = $this->settingFraction15($time);
        } else if($ceil == AttendanceHeader::FRACTION_30) {
            $return = $this->settingFraction30($time);
        }

        return $return;
    }

    public function settingFraction15($time) {

        if ($time >= 0 && $time < 15) {
            $res = 0;
        } else if ($time >= 15 && $time < 30) {
            $res = 15;
        } else if ($time >= 30 && $time < 45) {
            $res = 30;
        } else if ($time >= 45) {
            $res = 45;
        }

        return $res;
    }

    public function settingFraction30($time) {
        if ($time >= 0 && $time < 30) {
            $res = 0;
        } else if ($time >= 30) {
            $res = 30;
        }

        return $res;
    }

    public function getRawHourInt($time)
    {
        // 時刻をそのまま計算に使用する（丸め処理なし）
        $carbon = Carbon::create($time);

        return $carbon->hour + ($carbon->minute / 60);
    }

    public function applyRounding($hours, $company)
    {
        $timeInMinutes = $hours * 60;

        if ($company->time_fraction == AttendanceHeader::FRACTION_15) {
            $timeInMinutes = floor($timeInMinutes / 15) * 15; // 15分単位で切り捨て
        } elseif ($company->time_fraction == AttendanceHeader::FRACTION_30) {
            $timeInMinutes = floor($timeInMinutes / 30) * 30; // 30分単位で切り捨て
        }

        return $timeInMinutes / 60; // 分を時間に戻す
    }
}