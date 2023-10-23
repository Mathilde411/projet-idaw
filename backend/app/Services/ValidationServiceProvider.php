<?php

namespace App\Services;

use App\Database\DatabaseManager;
use App\Services\ServiceProvider;
use App\Validation\Validator;

class ValidationServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Validator::class);
    }
}