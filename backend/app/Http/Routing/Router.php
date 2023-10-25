<?php

namespace App\Http\Routing;

use Closure;

interface Router
{
    public function get(string $path, array|Closure $endpoint): void;

    public function post(string $path, array|Closure $endpoint): void;

    public function put(string $path, array|Closure $endpoint): void;

    public function delete(string $path, array|Closure $endpoint): void;

    public function patch(string $path, array|Closure $endpoint): void;

    public function any(string $path, array|Closure $endpoint): void;

    public function group(Closure|string $endpoint): void;

    public function prefix(string $path): static;

    public function middleware(string|array $middleware): static;

    public function name(string $name): static;

    public function responseType(string $responseType): static;
}