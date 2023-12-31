<?php

namespace App\Facade;

use App\Application;
use RuntimeException;

class Facade
{
    protected static Application $app;

    private static array $resolved = [];

    protected static function getFacadeBinding(): string {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    private static function resolveBinding(string $binding) {
        return static::$app->make($binding);
    }

    private static function getFacadeRoot() {
        return static::resolveBinding(static::getFacadeBinding());
    }

    protected static function configureRoot($root) {
        return $root;
    }

    public static function __callStatic(string $name, array $arguments)
    {
        $root = static::getFacadeRoot();

        if(!isset($root))
            throw new RuntimeException('Facades are not initialized.');

        return static::configureRoot($root)->$name(...$arguments);
    }

    public static function setFacadeApplication($app): void
    {
        static::$app = $app;
    }
}