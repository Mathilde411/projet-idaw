<?php

namespace App\Facade;


class Validator extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return \App\Validation\Validator::class;
    }
}