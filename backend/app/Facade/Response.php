<?php

namespace App\Facade;


class Response extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return \App\Http\Response::class;
    }
}