<?php

namespace App\Facade;

use App\Database\DatabaseManager;

class Database extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return DatabaseManager::class;
    }
}