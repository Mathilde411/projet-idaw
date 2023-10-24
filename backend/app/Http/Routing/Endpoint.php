<?php

namespace App\Http\Routing;

use App\Application;
use App\Reflection\ReflectionUtil;
use Closure;

class Endpoint
{
    protected array $reflectiveCallParam;
    public function __construct(protected Application $app, protected string $method, protected ?string $name, Closure|array $closure)
    {
        $this->reflectiveCallParam = ReflectionUtil::getReflectiveCallParameters($closure);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function call($args = []) : mixed {
        return $this->app->makeInjectedCall($this->reflectiveCallParam, $args);
    }
}