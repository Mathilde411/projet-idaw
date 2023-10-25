<?php

namespace App\Facade;

use App\Database\DatabaseManager;
use App\Database\DbConnection;

/**
 * @method static ?DbConnection connection()
 */
class DBManager extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return DatabaseManager::class;
    }
}