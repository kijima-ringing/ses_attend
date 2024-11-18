<?php

namespace App\Http\Requests;

use App\Rules\KanaRule;
use App\Rules\PasswordRegexRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            'last_name' => 'required|string|max:20',
            'first_name' => 'required|string|max:20',
            'last_name_kana' => [
                'required',
                'string',
                'max:40',
                new KanaRule()
            ],
            'first_name_kana' => [
                'required',
                'string',
                'max:40',
                new KanaRule()
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'password' => 'required|string|max:255',
            'department_ids' => 'nullable|exists:departments,id',
            'admin_flag' => 'boolean'
        ];

        if (!empty($this->id)) {
            $rules['id'] = 'required|exists:users,id';
            $rules['email'] = [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')
            ];
            $rules['password'] = [
                'nullable',
                'max:255',
                new PasswordRegexRule()
            ];

            if (empty($this->email)) {
                $this->request->remove('email');
                unset($rules['email']);
            }

            if (empty($this->password)) {
                $this->request->remove('password');
                unset($rules['password']);
            }
        }

        return $rules;
    }

    public function all($keys = null)
    {
        $results = parent::all($keys);

        return $results;
    }
}
