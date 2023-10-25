<?php

namespace App\Facade;

use App\Database\QueryBuilder;

/**
 * @method static QueryBuilder table(string $table)
 */
class DB extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return QueryBuilder::class;
    }
}