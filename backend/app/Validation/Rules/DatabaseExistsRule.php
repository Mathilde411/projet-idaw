<?php

namespace App\Validation\Rules;

use App\Facade\DB;
use App\Validation\Rules\ValidationRule;
use App\Validation\ValidationError;

class DatabaseExistsRule extends ValidationRule
{


    protected string $col = "id";

    public function __construct(protected string $table)
    {
    }

    public function col(string $col): static
    {
        $this->col = $col;
        return $this;
    }

    /**
     * @throws ValidationError
     */
    public function validate(string $var, mixed $value): bool {

        $count = DB::table($this->table)
            ->where($this->col, $value)
            ->select(DB::count('*', 'count'))
            ->first()['count'];

        if($count == 0)
            throw new ValidationError($var . " n'existe pas dans la base de données.");

        return true;
    }
}