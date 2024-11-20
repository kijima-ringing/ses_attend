<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'base_time_from' => 'required|date_format:H:i',
            'base_time_to' => 'required|date_format:H:i',
            'time_fraction' => 'required|integer|in:1,15,30',
            'rounding_scope' => 'required|integer|in:0,1', // 新規追加
        ];
    }

    /**
     * エラーメッセージのカスタマイズ
     *
     * @return array
     */
    public function messages()
    {
        return [
            'base_time_from.required' => '基準時間（From）は必須です。',
            'base_time_from.date_format' => '基準時間（From）は正しい形式で入力してください。',
            'base_time_to.required' => '基準時間（To）は必須です。',
            'base_time_to.date_format' => '基準時間（To）は正しい形式で入力してください。',
            'time_fraction.required' => '端数処理単位は必須です。',
            'time_fraction.in' => '端数処理単位は1、15、30のいずれかを選択してください。',
            'rounding_scope.required' => '端数処理の適用範囲は必須です。',
            'rounding_scope.in' => '端数処理の適用範囲は全体適用または日別適用を選択してください。',
        ];
    }
}
