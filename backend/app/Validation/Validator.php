<?php

namespace App\Validation;

class Validator
{
    protected array $rules = [];
    protected array $values = [];
    protected bool $ran = false;
    protected bool $valid = true;
    protected array $errors = [];

    public function __construct()
    {
    }

    public function build(array $rules, array $values): static
    {
        $this->rules = $rules;
        $this->values = $values;

        foreach ($this->rules as $var => $ruleSet) {
            foreach ($ruleSet as $rule) {
                $rule->setData($this->values);
            }
        }

        return $this;
    }

    public function validate(): void
    {
        $this->ran = true;
        foreach ($this->rules as $var => $ruleSet) {
            $val = $this->values[$var] ?? null;
            try {
                foreach ($ruleSet as $rule) {
                    if(!$rule->validate($var, $val))
                        break;
                }
            } catch (ValidationError $e) {
                $this->valid = false;
                $this->errors[$var] = $e->getMessage();
            }
        }
    }

    public function valid(): bool
    {
        if (!$this->ran)
            $this->validate();

        return $this->valid;
    }

    public function errors(): array
    {
        if (!$this->ran)
            $this->validate();

        return $this->errors;
    }

}