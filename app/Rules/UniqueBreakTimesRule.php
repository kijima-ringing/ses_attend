<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueBreakTimesRule implements Rule
{
    protected $workingTime;
    protected $leaveTime;

    public function __construct($workingTime, $leaveTime)
    {
        $this->workingTime = strtotime($workingTime);
        $this->leaveTime = strtotime($leaveTime);
    }

    public function passes($attribute, $value)
    {
        if (!$this->workingTime || !$this->leaveTime) {
            return true; // 出勤・退勤時間が設定されていない場合は検証しない
        }

        $timeRanges = [];
        foreach ($value as $breakTime) {
            $from = strtotime($breakTime['break_time_from']);
            $to = strtotime($breakTime['break_time_to']);

            // バリデーション条件
            if ($from >= $to) {
                return false;
            }
            if ($from < $this->workingTime || $to > $this->leaveTime) {
                return false;
            }

            // 重複チェック
            foreach ($timeRanges as $range) {
                if ($from < $range['to'] && $to > $range['from']) {
                    return false;
                }
            }

            $timeRanges[] = ['from' => $from, 'to' => $to];
        }

        return true;
    }

    public function message()
    {
        return '休憩時間が勤務時間の範囲内で、かつ重複していないことを確認してください。';
    }
}
