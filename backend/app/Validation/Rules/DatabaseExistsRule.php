<?php

namespace App\Validation\Rules;

use App\Facade\Database;
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
    public function validate(string $var, mixed $value): bool
    {
        $db = Database::connection();
        $sql = static::getSQL($this->table, $this->col);
        $stmt = $db->connection->prepare($sql);
        $stmt->execute(['field' => $value]);

        if($stmt->fetchColumn() == 0)
            throw new ValidationError($var . " n'existe pas dans la base de données.");

        return true;
    }

    public static function getSQL(string $table, string $field) : string {
        return "SELECT COUNT(*) FROM " . $table . " WHERE " . $field . " = :field";
    }
}