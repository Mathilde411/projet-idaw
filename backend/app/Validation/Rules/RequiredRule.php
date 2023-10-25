<?php

namespace App\Validation\Rules;

use App\Validation\Rules\ValidationRule;
use App\Validation\ValidationError;

class RequiredRule extends ValidationRule
{

    /**
     * @throws ValidationError
     */
    public function validate(string $var, mixed $value): bool
    {
        if(!isset($value))
            throw new ValidationError("La valeur " . $var . " est requise.");
        return true;
    }
}