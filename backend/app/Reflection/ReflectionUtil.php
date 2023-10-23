<?php

namespace App\Reflection;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class ReflectionUtil
{

    public static function getConstructor(string $class) : ?ReflectionMethod
    {
        $rClass = new ReflectionClass($class);

        if(!$rClass->isInstantiable())
            return null;

        return $rClass->getConstructor();
    }


    public static function getClosureArguments(Closure|ReflectionMethod|null $closure) : array {
        if(!isset($closure))
            return [];

        $func = $closure instanceof Closure ? new ReflectionFunction($closure) : $closure;
        $params = $func->getParameters();
        $res = [];
        foreach ($params as $param) {
            $res[$param->getName()] = [
                'class' => $param->getType() instanceof ReflectionNamedType ? $param->getType()->getName() : null,
                'nullable' => $param->allowsNull(),
                'optional' => $param->isOptional()
            ];
        }
        return $res;
    }

    public static function getObjectBuildingClosure(string $class) : ?Closure
    {
        $rClass = new ReflectionClass($class);

        if(!$rClass->isInstantiable())
            return null;

        return function (...$args) use ($rClass) {
            return $rClass->newInstanceArgs($args);
        };
    }

    private static function extractMethod(array $closure): ReflectionMethod
    {
        $rClass = new ReflectionClass($closure[0]);
        return $rClass->getMethod($closure[1]);
    }

    public static function getReflectiveCallParameters(Closure|string|array $closure) {
        if($closure instanceof Closure) {
            $builder = $closure;
            $type = 'function';
            $parameters = static::getClosureArguments($closure);
        } elseif(is_array($closure)) {
            $builder = $closure;
            $method = static::extractMethod($closure);
            $type = $method->isStatic() ? 'static' : 'method';
            $parameters = static::getClosureArguments($method);
        } else {
            $builder = static::getObjectBuildingClosure($closure);
            $type = 'constructor';
            $parameters = static::getClosureArguments(static::getConstructor($closure));
        }

        return [
            'builder' => $builder,
            'type' => $type,
            'parameters' => $parameters
        ];
    }


}