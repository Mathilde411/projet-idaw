<?php

namespace App\Validation\Rules;

use App\Validation\Rules\ValidationRule;
use App\Validation\ValidationError;

class EmailRule extends ValidationRule
{

    /**
     * @throws ValidationError
     */
    public function validate(string $var, mixed $value): bool
    {
        if(!filter_var($value, FILTER_VALIDATE_EMAIL))
            throw new ValidationError($var . " doit être une adresse email valide.");
        return true;
    }
}