<?php

namespace App\Services;

use App\Database\DatabaseManager;
use App\Services\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(DatabaseManager::class);
    }
}