<?php

namespace App\Http\Requests;

use App\Rules\ComparisonTimeRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'work_date' => 'required|date_format:Y-m-d',
            'attendance_class' => 'required|in:0,1,2',
            'working_time' => [
                'required',
                'date_format:H:i',
            ],
            'leave_time' => [
                'required',
                'date_format:H:i',
                new ComparisonTimeRule($this->input('working_time'), $this->input('leave_time'))
            ],
            'break_times' => 'array|min:1',
            'break_times.*.break_time_from' => [
                'required',
                'date_format:H:i',
            ],
            'break_times.*.break_time_to' => [
                'required',
                'date_format:H:i',
                new ComparisonTimeRule(
                    $this->input('break_times.*.break_time_from'),
                    $this->input('break_times.*.break_time_to')
                )
            ],
        ];
    }

    /**
     * Additional validation logic for custom rules.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateBreakTimes($validator);
        });
    }

    private function validateBreakTimes($validator)
    {
        $workingTime = Carbon::parse($this->input('working_time'));
        $leaveTime = Carbon::parse($this->input('leave_time'));
        $breakTimes = $this->input('break_times', []);

        foreach ($breakTimes as $index => $break) {
            $breakFrom = Carbon::parse($break['break_time_from']);
            $breakTo = Carbon::parse($break['break_time_to']);

            // 勤務時間外チェック
            if ($breakFrom->lt($workingTime) || $breakTo->gt($leaveTime)) {
                $validator->errors()->add(
                    "break_times.$index.break_time_from",
                    '休憩時間は勤務時間内である必要があります。'
                );
            }

            // 他の休憩時間と重複チェック
            foreach ($breakTimes as $subIndex => $otherBreak) {
                if ($index === $subIndex) {
                    continue;
                }

                $otherFrom = Carbon::parse($otherBreak['break_time_from']);
                $otherTo = Carbon::parse($otherBreak['break_time_to']);

                if ($breakFrom->lt($otherTo) && $breakTo->gt($otherFrom)) {
                    $validator->errors()->add(
                        "break_times.$index.break_time_from",
                        '休憩時間が重複しています。'
                    );
                }
            }
        }
    }

    public function attributes()
    {
        return [
            'work_date' => '日付',
            'attendance_class' => '区分',
            'working_time' => '出勤時間',
            'leave_time' => '退勤時間',
            'break_times.*.break_time_from' => '休憩開始時間',
            'break_times.*.break_time_to' => '休憩終了時間',
        ];
    }
}
