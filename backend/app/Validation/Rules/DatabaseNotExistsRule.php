<?php

namespace App\Validation\Rules;

use App\Facade\Database;
use App\Validation\Rules\ValidationRule;
use App\Validation\ValidationError;

class DatabaseNotExistsRule extends DatabaseExistsRule
{

    /**
     * @throws ValidationError
     */
    public function validate(string $var, mixed $value): bool
    {
        try {
            parent::validate($var, $value);
        } catch (ValidationError $e) {
            return true;
        }

        throw new ValidationError($var . " existe déjà dans la base de données.");
    }
}