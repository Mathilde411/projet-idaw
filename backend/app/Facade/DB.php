<?php

namespace App\Facade;

use App\Database\DbQueryBuilder;
use App\Database\QueryProvider;
use App\Database\RawSQL;
use Closure;

/**
 * @method static DbQueryBuilder table(string|RawSQL|QueryProvider $table)
 * @method static bool transaction(Closure $closure)
 * @method static RawSQL raw(string $sql)
 * @method static RawSQL min(string $identifier, ?string $newName = null)
 * @method static RawSQL max(string $identifier, ?string $newName = null)
 * @method static RawSQL sum(string $identifier, ?string $newName = null)
 * @method static RawSQL avg(string $identifier, ?string $newName = null)
 * @method static RawSQL count(string $identifier, ?string $newName = null)
 */
class DB extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return DbQueryBuilder::class;
    }
}