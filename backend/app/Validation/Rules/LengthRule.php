<?php

namespace App\Validation\Rules;

use App\Validation\Rules\ValidationRule;
use App\Validation\ValidationError;

class LengthRule extends ValidationRule
{
    protected int $minLength = 0;
    protected int $maxLength = PHP_INT_MAX;

    public function min(int $minLength): static
    {
        $this->minLength = $minLength;
        return $this;
    }

    public function max(int $maxLength): static
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * @throws ValidationError
     */
    public function validate(string $var, mixed $value): bool
    {
        $len = strlen($value);
        if($len < $this->minLength)
            throw new ValidationError($var . " doit être plus grand que " . $this->minLength . " caractères.");
        if($len > $this->maxLength)
            throw new ValidationError($var . " doit être plus petit que " . $this->maxLength . " caractères.");
        return true;
    }
}