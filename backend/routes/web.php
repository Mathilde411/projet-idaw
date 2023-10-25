<?php

use App\Facade\Router;

Router::get('/test', [\App\Http\Controllers\TestController::class, 'test']);