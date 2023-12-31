<?php
namespace App;

use App\Config\Config;
use App\Facade\Facade;
use App\Services\ServiceProvider;
use App\Reflection\ReflectionUtil;
use Closure;
use ReflectionClass;
use ReflectionException;

class Application
{
    protected static ?Application $instance = null;

    public static function getInstance() : ?Application {
        return static::$instance;
    }

    public static function setInstance(?Application $instance) : void {
        static::$instance = $instance;
    }

    protected array $bindings = [];

    protected array $sharedInstances = [];

    protected array $serviceProviders = [];

    protected $basePath;

    /**
     * @throws ReflectionException
     */
    public function __construct($basePath)
    {
        $this->setBasePath($basePath);
        $this->setupFirstBindings();
        $this->bootstrap();

    }

    public function start() {
        $this->bootServiceProviders();
    }

    public function stop() {
        $this->shutdownServiceProviders();
    }

    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
    }

    public function getBasePath(string $path = ""): string
    {
        return $this->basePath . '/' . trim($path, '/');
    }


    public function bind(string $abstract, Closure|string|null $concrete = null, bool $shared = false): void
    {
        if(isset($this->sharedInstances[$abstract]))
            unset($this->sharedInstances[$abstract]);

        if(!isset($concrete))
            $concrete = $abstract;

        $this->bindings[$abstract] = [
            'callParam' => ReflectionUtil::getReflectiveCallParameters($concrete),
            'shared' => $shared
        ];
    }

    public function singleton(string $abstract, Closure|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }

    public function isBound(string $abstract) : bool {
        return array_key_exists($abstract, $this->bindings);
    }

    public function instanceExists(string $abstract) : bool {
        return array_key_exists($abstract, $this->sharedInstances);
    }

    public function instance(string $abstract, mixed $instance = null) : void {
        $this->bindings[$abstract] = [
            'callParam' => null,
            'shared' => true
        ];

        $this->sharedInstances[$abstract] = $instance ?? $this->make($abstract);
    }

    public function makeInjectedCall(array $reflectiveCallParams, array $args = [], mixed $target = null) : mixed {
        $fnArgs = [];
        foreach($reflectiveCallParams['parameters'] as $name => $opt) {
            if(isset($args[$name])) {
                $fnArgs[] = $args[$name];
            } elseif ($this->isBound($opt['class'])) {
                $fnArgs[] = $this->make($opt['class']);
            } elseif ($opt['optional']) {
                break;
            } else {
                $fnArgs[] = null;
            }
        }

        if(isset($target) and in_array($reflectiveCallParams['type'], ['static', 'method'])) {
            $builder = [$target, $reflectiveCallParams['builder'][1]];
        } elseif ($reflectiveCallParams['type'] == 'method') {
            if(is_string($reflectiveCallParams['builder'][0]))
                $builder = [
                    $this->makeImmediateInjectedCall($reflectiveCallParams['builder'][0]),
                    $reflectiveCallParams['builder'][1]
                ];
            else
                $builder = $reflectiveCallParams['builder'];
        } else {
            $builder = $reflectiveCallParams['builder'];
        }

        return call_user_func_array($builder, $fnArgs);
    }

    public function makeImmediateInjectedCall(Closure|string|array $closure, array $args = [], mixed $target = null) {
        $constructorCallParams = ReflectionUtil::getReflectiveCallParameters($closure);
        return $this->makeInjectedCall($constructorCallParams, $args, $target);
    }

    public function make(string $abstract, array $args = []) : mixed
    {
        if(!$this->isBound($abstract))
            return null;

        $binding = $this->bindings[$abstract];

        if($binding['shared'] and $this->instanceExists($abstract))
            return $this->sharedInstances[$abstract];

        $res = $this->makeInjectedCall($binding['callParam'], $args);

        if($binding['shared'])
            $this->sharedInstances[$abstract] = $res;

        return $res;
    }

    private function setupFirstBindings(): void
    {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(Application::class, $this);

        $config = new Config($this);
        $this->instance(Config::class, $config);
        $this->instance('config', $config);
    }

    private function bootstrap(): void
    {
        Facade::setFacadeApplication($this);
        $this->registerServiceProviders();
    }

    private function registerServiceProviders(): void
    {
        $services = \App\Facade\Config::get('app.services', []);

        foreach ($services as $serviceClass) {

            try {
                $class = new ReflectionClass($serviceClass);

                if(!$class->isSubclassOf(ServiceProvider::class))
                    continue;
                $serviceInstance = $class->newInstance($this);
                $serviceInstance->register();
                $this->serviceProviders[] = $serviceInstance;
            } catch (ReflectionException $e) {
                continue;
            }
        }
    }

    private function bootServiceProviders(): void
    {
        foreach ($this->serviceProviders as $provider) {
            $provider->boot();
        }
    }

    private function shutdownServiceProviders(): void
    {
        foreach ($this->serviceProviders as $provider) {
            $provider->boot();
        }
    }
}