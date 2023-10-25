<?php

namespace App\Facade;

/**
 * @method static \App\Validation\Validator build(array $rules, array $values)
 */
class Validator extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return \App\Validation\Validator::class;
    }
}