<?php

namespace App\Http\Requests;

use App\Rules\ComparisonTimeRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\UniqueBreakTimesRule;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'work_date' => 'required|date_format:Y-m-d',
            'attendance_class' => 'required|in:0,1,2',
            'working_time' => 'nullable|date_format:H:i',
            'leave_time' => 'nullable|date_format:H:i|after:working_time',
            'break_times' => [
                'nullable',
                'array',
                new UniqueBreakTimesRule($this->input('working_time'), $this->input('leave_time')),
            ],
            'break_times.*.break_time_from' => 'required_with:break_times.*.break_time_to|date_format:H:i',
            'break_times.*.break_time_to' => 'required_with:break_times.*.break_time_from|date_format:H:i|',
            'memo' => 'nullable|string',
        ];
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
            'memo' => 'メモ',
        ];
    }
}
