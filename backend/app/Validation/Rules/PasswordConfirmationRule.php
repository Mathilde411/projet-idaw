<?php

namespace App\Validation\Rules;

use App\Validation\Rules\ValidationRule;
use App\Validation\ValidationError;

class PasswordConfirmationRule extends RequiredRule
{

    protected array $data;

    public function __construct(protected string $passwordField = "password")
    {
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }


    public function validate(string $var, mixed $value): bool
    {
        if(!isset($this->data[$this->passwordField]))
            return false;

        parent::validate($var, $value);

        if($this->data[$this->passwordField] != $value)
            throw new ValidationError($this->passwordField . " ne correspond pas à " . $var . ".");

        return true;
    }
}