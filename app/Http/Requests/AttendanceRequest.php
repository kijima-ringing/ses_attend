<?php

namespace App\Http\Requests;

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
            'working_time' => 'nullable|date_format:H:i:s',
            'leave_time' => 'nullable|date_format:H:i:s',
            'break_time_from' => 'nullable|date_format:H:i:s',
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



}
