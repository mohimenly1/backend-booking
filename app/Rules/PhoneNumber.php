<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Example regex for a simple 10-digit phone number validation
        if (!preg_match('/^[0-9]{10}$/', $value)) {
            $fail('The :attribute must be a valid phone number.');
        }
    }
}