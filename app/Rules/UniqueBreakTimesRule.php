<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueBreakTimesRule implements Rule
{
    protected $workingTime;
    protected $leaveTime;

    // 出勤時間と退勤時間をコンストラクタで受け取る
    public function __construct($workingTime, $leaveTime)
    {
        $this->workingTime = strtotime($workingTime);
        $this->leaveTime = strtotime($leaveTime);
    }

    public function passes($attribute, $value)
    {
        // 出勤時間または退勤時間が空の場合はスキップ
        if (!$this->workingTime || !$this->leaveTime) {
            return false; // 出勤・退勤時間がない場合は無効
        }

        $timeRanges = [];
        foreach ($value as $breakTime) {
            $from = strtotime($breakTime['break_time_from']);
            $to = strtotime($breakTime['break_time_to']);

            // 開始時刻が終了時刻以降の場合
            if ($from >= $to) {
                return false;
            }

            // 出勤・退勤時間の範囲外の場合
            if ($from < $this->workingTime || $to > $this->leaveTime) {
                return false;
            }

            // 既存の時間範囲と重複がある場合
            foreach ($timeRanges as $range) {
                if ($from < $range['to'] && $to > $range['from']) {
                    return false;
                }
            }

            $timeRanges[] = ['from' => $from, 'to' => $to];
        }

        return true; // 条件を全て満たす場合
    }

    public function message()
    {
        return '休憩時間は出勤時間と退勤時間の範囲内に設定してください。また、休憩時間が重複していないことを確認してください。';
    }
}

