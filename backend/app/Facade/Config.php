<?php

namespace App\Facade;

use App\Facade\Facade;

class Config extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return 'config';
    }
}