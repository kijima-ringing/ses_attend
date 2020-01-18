<?php

namespace App\Http\Requests;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class SettingRequest extends FormRequest
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
        $rules = [
            'base_time_from' => 'required|date_format:"H:i"',
            'base_time_to' => 'required|date_format:"H:i"',
        ];

        return $rules;
    }

    public function withValidator(Validator $validator) {
        $time_fraction_values = Company::TIME_FRACTION_VALUES;
        $validator->after(function ($validator) use ($time_fraction_values) {
            if (!in_array((int)$this->input('time_fraction'), $time_fraction_values, true)) {
                $validator->errors()->add('time_fraction', '端数処理が対応しているのは、なし・15分・30分のいずれかです。');
            }

            if ($this->base_time_from >= $this->base_time_to) {
                $validator->errors()->add('base_time', '出勤時間 < 退勤時間で設定してください。');
            }
        });
    }

    public function all($keys = null)
    {
        $results = parent::all($keys);

        return $results;
    }
}
