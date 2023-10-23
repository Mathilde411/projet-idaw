<?php
namespace App;

use App\Config\Config;
use App\Facade\Facade;
use App\Services\ServiceProvider;
use App\Util\ReflectionUtil;
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

    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '\/');
    }

    public function getBasePath()
    {
        return $this->basePath;
    }


    public function bind(string $abstract, Closure|string|null $concrete = null, bool $shared = false): void
    {
        if(isset($this->sharedInstances[$abstract]))
            unset($this->sharedInstances[$abstract]);

        if(!isset($concrete))
            $concrete = $abstract;

        if($concrete instanceof Closure) {
            $builder = $concrete;
            $parameters = ReflectionUtil::getClosureArguments($concrete);
        } else {
            $builder = ReflectionUtil::getObjectBuildingClosure($concrete);
            $parameters = ReflectionUtil::getClosureArguments(ReflectionUtil::getConstructor($concrete));
        }

        $this->bindings[$abstract] = [
            'builder' => $builder,
            'shared' => $shared,
            'parameters' => $parameters
        ];
    }

    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    public function isBound(string $abstract) : bool {
        return array_key_exists($abstract, $this->bindings);
    }

    public function sharedInstanceExists(string $abstract) : bool {
        return array_key_exists($abstract, $this->sharedInstances);
    }

    public function instance(string $abstract, mixed $instance = null) : void {
        $this->bindings[$abstract] = [
            'builder' => null,
            'shared' => true,
            'parameters' => []
        ];

        $this->sharedInstances[$abstract] = $instance ?? $this->make($abstract);
    }

    public function make(string $abstract, array $args = []) : mixed
    {
        if(!$this->isBound($abstract))
            return null;

        $binding = $this->bindings[$abstract];

        if($binding['shared'] and $this->sharedInstanceExists($abstract))
            return $this->sharedInstances[$abstract];

        $fnArgs = [];
        foreach($binding['parameters'] as $name => $opt) {
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

        $res = call_user_func_array($binding['builder'], $fnArgs);
        if($binding['shared'])
            $this->sharedInstances[$abstract] = $res;

        return $res;
    }

    /**
     * @throws ReflectionException
     */
    private function setupFirstBindings() {
        static::setInstance($this);
        $this->instance('app', $this);
        $this->instance(Application::class, $this);

        $config = new Config($this);
        $this->instance(Config::class, $config);
        $this->instance('config', $config);
    }

    private function bootstrap()
    {
        Facade::setFacadeApplication($this);
        $this->registerServiceProviders();
    }

    private function registerServiceProviders() {
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
}