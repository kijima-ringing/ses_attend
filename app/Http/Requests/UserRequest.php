<?php

namespace App\Http\Requests;

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
            'last_name_kana' => 'required|string|max:40',
            'first_name_kana' => 'required|string|max:40',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')
            ],
            'password' => 'required|string|max:255',
            'department_ids' => 'nullable|exists:departments,id'
        ];

        if (!empty($this->id)) {
            $rules['id'] = 'required|alpha_num';
            $rules['email'] = [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')
            ];
            $rules['password'] = 'nullable|string|max:255';

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
