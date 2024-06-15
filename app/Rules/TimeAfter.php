<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TimeAfter implements ValidationRule
{
    protected $otherField;
    protected $otherFieldName;

    public function __construct($otherField, $otherFieldName)
    {
        $this->otherField = $otherField;
        $this->otherFieldName = $otherFieldName;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $otherValue = request($this->otherField);

        if (strtotime($value) <= strtotime($otherValue)) {
            $fail('The :attribute must be a time after ' . $this->otherFieldName . '.');
        }
    }
}
