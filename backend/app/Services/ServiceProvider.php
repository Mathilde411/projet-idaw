<?php

namespace App\Services;

use App\Application;

class ServiceProvider
{

    public function __construct(protected Application $app)
    {}

    public function register(){}
    public function boot(){}
    public function shutdown(){}
}