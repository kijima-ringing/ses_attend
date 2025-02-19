<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PasswordRegexRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return (bool) preg_match('/^[a-zA-Z0-9-\^\!\#\<\>\:\;\&\~\@\%\+\$\"\'\*\^\(\)\[\]\|\/\.\,\_\-]+$/', $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attributeは英大文字、英小文字、数、記号を使用してください。';
    }
}
