<?php

namespace App\Validation\Rules;

use App\Validation\ValidationError;

abstract class ValidationRule
{
    public function setData(array $data) {}

    /**
     * @throws ValidationError
     */
    public abstract function validate(string $var, mixed $value) : bool;
}