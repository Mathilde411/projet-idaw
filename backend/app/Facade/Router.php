<?php

namespace App\Facade;

use Closure;

/**
 * @method static void get(string $path, array|Closure $endpoint)
 * @method static void post(string $path, array|Closure $endpoint)
 * @method static void put(string $path, array|Closure $endpoint)
 * @method static void delete(string $path, array|Closure $endpoint)
 * @method static void patch(string $path, array|Closure $endpoint)
 * @method static void any(string $path, array|Closure $endpoint)
 * @method static void group(Closure|string $endpoint)
 * @method static \App\Http\Routing\Router prefix(string $path)
 * @method static \App\Http\Routing\Router middleware(string|array $middleware)
 * @method static \App\Http\Routing\Router name(string $name)
 * @method static \App\Http\Routing\Router responseType(string $responseType)
 */
class Router extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return \App\Http\Routing\Router::class;
    }

    protected static function configureRoot($root)
    {
        return $root->resetVars();
    }
}