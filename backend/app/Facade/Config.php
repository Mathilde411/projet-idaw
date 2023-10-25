<?php

namespace App\Facade;

use App\Facade\Facade;

/**
 * @method static mixed get(string $key, mixed $default = null)
 */
class Config extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return 'config';
    }
}