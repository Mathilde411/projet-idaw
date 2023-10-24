<?php

namespace App\Http\Routing;

use App\Application;
use App\Http\Error\BadRequestError;
use App\Http\Error\MethodNotAllowedError;
use App\Http\Error\NotFoundError;
use App\Http\Request;
use Closure;
use TypeError;

class Router
{

    private RoutePart $root;
    private array $namedEndpoints = [];
    private array $routingStack = [];

    protected array $pathFragments = [];
    protected ?string $name = null;

    public function __construct(protected Application $app)
    {
        $this->root = new RoutePart();
    }

    public function resetVars(): static
    {
        $this->pathFragments = [];
        $this->name = null;
        return $this;
    }

    public function get(string $path, array|Closure $endpoint)
    {
        $this->endpoint(['GET', 'HEAD'], $path, $endpoint);
    }

    public function post(string $path, array|Closure $endpoint)
    {
        $this->endpoint('POST', $path, $endpoint);
    }

    public function put(string $path, array|Closure $endpoint)
    {
        $this->endpoint('PUT', $path, $endpoint);
    }

    public function delete(string $path, array|Closure $endpoint)
    {
        $this->endpoint('DELETE', $path, $endpoint);
    }

    public function patch(string $path, array|Closure $endpoint)
    {
        $this->endpoint('PATH', $path, $endpoint);
    }

    public function any(string $path, array|Closure $endpoint)
    {
        $this->endpoint('ANY', $path, $endpoint);
    }

    public function endpoint(string|array $method, string $path, array|Closure $closure)
    {
        $this->prefix($path);

        $name = $this->name;
        $pathFragments = [];
        foreach ($this->routingStack as $item) {
            $pathFragments = array_merge($pathFragments, $item['path']);
        }
        $pathFragments = array_merge($pathFragments, $this->pathFragments);

        $route = $this->root;
        foreach ($pathFragments as $fragment) {
            $route = $route->constructBranch($fragment);
        }

        if(is_string($method))
            $method = [$method];

        foreach ($method as $m) {
            $endpoint = new Endpoint($this->app, $m, $name, $closure);
            $route->addEndpoint($endpoint);
            if(isset($name)) {
                $this->namedEndpoints[$name] = $endpoint;
                $name = null;
            }
        }

    }

    public function group(Closure|string $endpoint)
    {
        $this->routingStack[] = [
            'name' => $this->name,
            'path' => $this->pathFragments
        ];

        $this->resetVars();
        if(is_string($endpoint))
            include $endpoint;
        else
            call_user_func($endpoint);

        array_pop($this->routingStack);
    }

    public function prefix(string $path): static
    {

        $this->pathFragments = array_filter(explode('/', trim($path, '/')), function ($p) {
            return $p != '';
        });
        return $this;
    }

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getRoot(): RoutePart
    {
        return $this->root;
    }

    public function getNamedEndpoints(): array
    {
        return $this->namedEndpoints;
    }

    public function getEndpointByName(string $name) : void {
        $this->namedEndpoints[$name]?->call();
    }

    /**
     * @throws MethodNotAllowedError
     * @throws NotFoundError
     * @throws BadRequestError
     */
    private function routeByFragmentedPath(array $path, string $method) : mixed {
        $route = $this->root;

        /* @var RoutePart $r
         * @var string $p
         */
        $param = [];
        foreach ($path as $fragment) {
            $found = false;
            foreach ($route->getBranches() as $p => $r) {
                if(preg_match('#^{(.*)}$#', $p, $matches)){
                    $param[$matches[1]] = $fragment;
                    $route = $r;
                    $found = true;
                    break;
                } elseif($fragment == $p) {
                    $route = $r;
                    $found = true;
                    break;
                }
            }

            if(!$found)
                throw new NotFoundError();
        }

        /* @var Endpoint $endpoint
         */
        foreach ($route->getEnpoints() as $endpoint) {
            $endpointMethod = $endpoint->getMethod();
            if($endpointMethod == $method or $endpointMethod == 'ANY') {
                try {
                    return $endpoint->call($param);
                } catch(TypeError) {
                    throw new BadRequestError();
                }
            }
        }
        throw new MethodNotAllowedError();
    }

    /**
     * @throws MethodNotAllowedError
     * @throws NotFoundError
     * @throws BadRequestError
     */
    public function routeByPath(string $path, string $method = 'GET') : mixed {
        return $this->routeByFragmentedPath(explode('/', trim($path, '/')), $method);
    }

    /**
     * @throws MethodNotAllowedError
     * @throws NotFoundError
     * @throws BadRequestError
     */
    public function route(Request $request): mixed
    {
        return $this->routeByFragmentedPath($request->getPathComponents(), $request->getMethod());
    }


}