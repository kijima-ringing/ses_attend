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
                'nullable',
                'date_format:H:i:s',
                new ComparisonTimeRule($this->input('working_time'), $this->input('leave_time'))
            ],
            'leave_time' => 'nullable|date_format:H:i:s',
            'break_time_from' => [
                'nullable',
                'date_format:H:i:s',
                new ComparisonTimeRule($this->input('break_time_from'), $this->input('break_time_to')),
            ],
            'break_time_to' => 'nullable|date_format:H:i:s',
            'memo' => 'nullable'
        ];
    }

    public function all($keys = null)
    {
        $results = parent::all($keys);


        $working_time = new Carbon($this->input('working_time'));
        $leave_time = new Carbon($this->input('leave_time'));
        $break_time_from = new Carbon($this->input('break_time_from'));
        $break_time_to = new Carbon($this->input('break_time_to'));

        $results['working_time'] = $working_time->format('H:i:s');
        $results['leave_time'] = $leave_time->format('H:i:s');
        $results['break_time_from'] = $break_time_from->format('H:i:s');
        $results['break_time_to'] = $break_time_to->format('H:i:s');

        return $results;
    }

    public function attributes()
    {
        return [
            'work_date' => '日付',
            'attendance_class' => '区分',
            'working_time' => '出勤時間',
            'leave_time' => '退勤時間',
            'break_time_from' => '休憩開始時間',
            'break_time_to' => '休憩終了時間',
            'memo' => 'メモ',
        ];
    }



}
