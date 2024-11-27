<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BreakTimeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'break_times' => 'nullable|array',
            'break_times.*.break_time_from' => 'required_with:break_times.*.break_time_to|date_format:H:i',
            'break_times.*.break_time_to' => 'required_with:break_times.*.break_time_from|date_format:H:i|after:break_times.*.break_time_from',
        ];
    }

    public function attributes()
    {
        return [
            'break_times.*.break_time_from' => '休憩開始時間',
            'break_times.*.break_time_to' => '休憩終了時間',
        ];
    }
}
