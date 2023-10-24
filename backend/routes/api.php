<?php

use App\Facade\Router;
use App\Http\Controllers\Test1Controller;
use App\Http\Controllers\Test2Controller;

Router::prefix('users')->group(function () {
    Router::get('/', [Test1Controller::class, 'test1']);
    Router::get('{id}', [Test1Controller::class, 'test2']);
});

Router::put('/test', [Test2Controller::class, 'test1']);
Router::any('/test/{id}', [Test2Controller::class, 'test2']);