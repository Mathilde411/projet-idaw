<?php

namespace App\Services;

use App\Http\Kernel;
use App\Http\Request;
use App\Http\Response;

class HttpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Kernel::class);
        $this->app->singleton(Request::class);
        $this->app->singleton(Response::class);
    }

    public function up()
    {

    }
}