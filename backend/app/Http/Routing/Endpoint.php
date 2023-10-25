<?php

namespace App\Http\Routing;

use App\Application;
use App\Reflection\ReflectionUtil;
use Closure;

class Endpoint
{
    private array $callChain;
    private array $controllerCall;

    public function __construct(
        protected Application $app,
        protected string $method,
        protected ?string $name,
        protected string $responseType,
        array $middleware,
        Closure|array $closure)
    {
        $this->callChain = array_map(function ($mid) {
            return ReflectionUtil::getReflectiveCallParameters([$mid, 'handle']);
        }, $middleware);
        $this->controllerCall = ReflectionUtil::getReflectiveCallParameters($closure);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getResponseType(): string
    {
        return $this->responseType;
    }

    public function call($args = []) : mixed {

        $previousClosure = function () use ($args) {
            return $this->app->makeInjectedCall($this->controllerCall, $args);
        };

        foreach (array_reverse($this->callChain) as $call) {
            $previousClosure = function () use ($previousClosure, $call, $args) {
                return $this->app->makeInjectedCall(
                    $call,
                    array_merge(['next' => $previousClosure], $args)
                );
            };
        }
        return $previousClosure();
    }
}