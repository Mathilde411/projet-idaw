<?php

namespace App\Facade;

class Router extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return \App\Http\Routing\Router::class;
    }
}