<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - Container Class ==============
 * =====================================
 */

namespace celionatti\Bolt;

use Closure;
use celionatti\Bolt\BoltException\BoltException;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function bind(string $abstract, $concrete)
    {
        $this->bindings[$abstract] = $concrete;
    }

    public function make(string $abstract, array $parameters = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bindings[$abstract])) {
            $concrete = $this->bindings[$abstract];

            if ($concrete instanceof Closure) {
                $instance = $this->build($concrete, $parameters);
            } else {
                $instance = $this->build($concrete, $parameters);
            }

            $this->instances[$abstract] = $instance;

            return $instance;
        }

        throw new BoltException("Binding for '{$abstract}' not found.", 404, "critical");
    }

    private function build($concrete, array $parameters)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this);
        }

        try {
            $reflector = new \ReflectionClass($concrete);

            if (!$reflector->isInstantiable()) {
                throw new BoltException("Class '{$concrete}' is not instantiable.", 424, "info");
            }

            $constructor = $reflector->getConstructor();

            if ($constructor === null) {
                return new $concrete;
            }

            $dependencies = $this->resolveDependencies($constructor, $parameters);

            return $reflector->newInstanceArgs($dependencies);
        } catch (\ReflectionException $e) {
            throw new BoltException("Error resolving '{$concrete}': " . $e->getMessage(), $e->getCode());
        }
    }

    private function resolveDependencies(\ReflectionMethod $method, array $parameters)
    {
        $dependencies = [];

        foreach ($method->getParameters() as $parameter) {
            $paramName = $parameter->getName();

            if (array_key_exists($paramName, $parameters)) {
                $dependencies[] = $parameters[$paramName];
            } elseif ($parameter->getClass()) {
                $dependencies[] = $this->make($parameter->getClass()->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new BoltException("Unable to resolve dependency: {$paramName}", 424, "info");
            }
        }

        return $dependencies;
    }

    public function singleton(string $abstract, $concrete)
    {
        $this->bind($abstract, function () use ($concrete) {
            static $instance;

            if ($instance === null) {
                $instance = $this->build($concrete, []);
            }

            return $instance;
        });
    }
}

