<?php

namespace App\Facade;

use App\Database\DbQueryBuilder;
use App\Database\RawSQL;
use Closure;

/**
 * @method static DbQueryBuilder table(string $table)
 * @method static bool transaction(Closure $closure)
 * @method static RawSQL raw(string $sql)
 * @method static RawSQL min(string $identifier)
 * @method static RawSQL max(string $identifier)
 * @method static RawSQL sum(string $identifier)
 * @method static RawSQL avg(string $identifier)
 * @method static RawSQL count(string $identifier)
 */
class DB extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return DbQueryBuilder::class;
    }
}