<?php

namespace App\Facade;

use Closure;

/**
 * @method static string getBasePath(string $basePath = "")
 * @method static void bind(string $abstract, Closure|string|null $concrete = null, bool $shared = false)
 * @method static void singleton(string $abstract, Closure|string|null $concrete = null)
 * @method static void instance(string $abstract, mixed $instance = null)
 * @method static bool isBound(string $abstract)
 * @method static bool instanceExists(string $abstract)
 * @method static mixed make(string $abstract, array $args = [])
 */
class App extends Facade
{
    protected static function getFacadeBinding(): string
    {
        return 'app';
    }
}