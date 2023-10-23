<?php

namespace App\Validation\Rules;

use App\Validation\Rules\ValidationRule;
use App\Validation\ValidationError;

class NullableRule extends ValidationRule
{

    public function validate(string $var, mixed $value): bool
    {
        if(!isset($value))
            return false;
        return true;
    }
}