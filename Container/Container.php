<?php

declare(strict_types=1);

/**
 * =====================================
 * Bolt - Container Class ==============
 * =====================================
 */

namespace celionatti\Bolt\Container;

use Closure;
use Psr\Container\ContainerInterface;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Container\Exception\NotFoundException;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $aliases = [];

    public function bind(string $abstract, $concrete = null)
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }

        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, $concrete = null)
    {
        $this->bind($abstract, function () use ($concrete, $abstract) {
            static $instance;

            if ($instance === null) {
                $instance = $this->build($concrete ?? $abstract, []);
            }

            return $instance;
        });
    }

    public function alias(string $abstract, string $alias)
    {
        $this->aliases[$alias] = $abstract;
    }

    public function make(string $abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

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

        return $this->resolve($abstract, $parameters);
    }

    private function build($concrete, array $parameters)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, ...$parameters);
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

    private function resolveDependencies(\ReflectionFunctionAbstract $function, array $parameters)
    {
        $dependencies = [];

        foreach ($function->getParameters() as $parameter) {
            $paramName = $parameter->getName();
            $paramClass = $parameter->getClass();

            if (array_key_exists($paramName, $parameters)) {
                $dependencies[] = $parameters[$paramName];
            } elseif ($paramClass) {
                $dependencies[] = $this->make($paramClass->getName());
            } elseif ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
            } else {
                throw new BoltException("Unable to resolve dependency: {$paramName}", 424, "info");
            }
        }

        return $dependencies;
    }

    private function getAlias(string $abstract): string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    private function resolve(string $abstract, array $parameters = [])
    {
        if (!class_exists($abstract)) {
            throw new NotFoundException("No entry was found for '{$abstract}'.");
        }

        return $this->build($abstract, $parameters);
    }

    public function has($id)
    {
        $id = $this->getAlias($id);

        return isset($this->bindings[$id]) || isset($this->instances[$id]);
    }

    public function get($id)
    {
        try {
            return $this->make($id);
        } catch (BoltException $e) {
            throw new NotFoundException("No entry was found for '{$id}'.");
        }
    }

    public function call($callback, array $parameters = [])
    {
        if (is_array($callback)) {
            [$class, $method] = $callback;

            $instance = is_object($class) ? $class : $this->make($class);

            $reflector = new \ReflectionMethod($instance, $method);

            $dependencies = $this->resolveDependencies($reflector, $parameters);

            return $reflector->invokeArgs($instance, $dependencies);
        } elseif ($callback instanceof Closure) {
            $reflector = new \ReflectionFunction($callback);

            $dependencies = $this->resolveDependencies($reflector, $parameters);

            return $callback(...$dependencies);
        }

        throw new BoltException("Invalid callback provided for method injection.", 424, "info");
    }

    public function __get($name)
    {
        return $this->make($name);
    }
}
