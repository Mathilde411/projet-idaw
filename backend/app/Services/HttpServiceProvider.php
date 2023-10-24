<?php

namespace App\Services;

use App\Http\Kernel;
use App\Http\Request;
use App\Http\Response;
use App\Http\Routing\Router;

class HttpServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Kernel::class);
        $this->app->singleton(Request::class);
        $this->app->singleton(Response::class);
        $this->app->singleton(Router::class);
    }

    public function boot()
    {
        $router = $this->app->make(Router::class);

        $router
            ->prefix('backend/api')
            ->group($this->app->getBasePath('routes/api.php'));
        $router
            ->prefix('backend')
            ->group($this->app->getBasePath('routes/web.php'));
    }
}