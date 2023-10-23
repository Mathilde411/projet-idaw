<?php

namespace App\Facade;

class App extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return 'app';
    }
}