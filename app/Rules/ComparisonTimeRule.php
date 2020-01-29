<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ComparisonTimeRule implements Rule
{

    private $fromTime;
    private $toTime;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $fromTime, string $toTime)
    {
        $this->fromTime = $fromTime;
        $this->toTime = $toTime;
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
        $from = strtotime($this->fromTime);
        $to = strtotime($this->toTime);

        if ($from < $to) {
            return true;
        }

        if ($to - $from > 0)  {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
