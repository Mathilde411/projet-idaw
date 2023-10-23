<?php

namespace App\Util;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class ReflectionUtil
{
    /**
     * @throws ReflectionException
     */
    public static function getConstructor(string $class) : ?ReflectionMethod
    {
        $rClass = new ReflectionClass($class);

        if(!$rClass->isInstantiable())
            return null;

        return $rClass->getConstructor();
    }


    /**
     * @throws ReflectionException
     */
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
}