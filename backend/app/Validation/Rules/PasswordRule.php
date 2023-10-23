<?php

namespace App\Validation\Rules;

use App\Validation\Rules\ValidationRule;
use App\Validation\ValidationError;

class PasswordRule extends LengthRule
{

    protected bool $special = false; #"!#$%&()*+,-./:;<=>?@[\]^_`{|}~";
    protected bool $numbers = false;
    protected bool $mixedCase = false;

    public function special() : static {
        $this->special = true;
        return $this;
    }

    public function numbers() : static {
        $this->numbers = true;
        return $this;
    }

    public function mixedCase() : static {
        $this->mixedCase = true;
        return $this;
    }

    /**
     * @throws ValidationError
     */
    public function validate(string $var, mixed $value): bool
    {
        parent::validate($var, $value);

        if($this->numbers and !preg_match('@[0-9]@', $value))
            throw new ValidationError($var . " doit contenir au moins un chiffre.");

        if($this->mixedCase and !preg_match('@[A-Z]@', $value))
            throw new ValidationError($var . " doit contenir au moins une majuscule.");

        if($this->mixedCase and !preg_match('@[a-z]@', $value))
            throw new ValidationError($var . " doit contenir au moins une minuscule.");

        if($this->special and !preg_match('@[\@$!%*?&#]@', $value))
            throw new ValidationError($var . " doit contenir au moins un caractère spécial ( @ $ ! % * ? & # ).");

        return true;
    }
}