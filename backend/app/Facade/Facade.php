<?php

namespace App\Facade;

use App\Application;
use RuntimeException;

class Facade
{
    protected static Application $app;

    protected static array $resolved = [];

    protected static function getFacadeBinding(): string {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    protected static function resolveBinding(string $binding) {
        if(isset(static::$resolved[$binding]))
            return static::$resolved[$binding];

        if(isset(static::$app)) {
            return static::$resolved[$binding] = static::$app->make($binding);
        }
        return null;
    }

    protected static function getFacadeRoot() {
        return static::resolveBinding(static::getFacadeBinding());
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $root = static::getFacadeRoot();

        if(!isset($root))
            throw new RuntimeException('Facades are not initialized.');

        return $root->$name(...$arguments);
    }

    public static function getFacadeApplication()
    {
        return static::$app;
    }


    public static function setFacadeApplication($app)
    {
        static::$app = $app;
    }
}