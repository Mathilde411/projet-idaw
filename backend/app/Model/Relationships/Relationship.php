<?php

namespace App\Model\Relationships;

use App\Database\DbQueryBuilder;
use App\Database\QueryProvider;
use App\Facade\DB;

abstract class Relationship implements QueryProvider
{
    public abstract function execute() : mixed;

    public function __call(string $name, array $arguments)
    {
        return DB::table($this->getQuery())->$name(...$arguments);
    }
}