<?php

namespace App\Services;

use App\Database\DatabaseManager;
use App\Database\QueryBuilder;
use App\Services\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DatabaseManager::class);
        $this->app->bind(QueryBuilder::class);
    }
}