<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Router Class ===========
 * =================================
 */

namespace celionatti\Bolt\Router;

use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use celionatti\Bolt\Http\Request;
use celionatti\Bolt\Http\Response;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Debug\Error;

class Router
{
    protected array $routes = [];
    protected array $groupStack = [];
    protected Request $request;
    protected Response $response;
    protected array $currentRoute = [];

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function addRoute(string $method, string $path, $action): self
    {
        $path = preg_replace('/{\:(\w+)}/', '(?P<$1>[^/]+)', $path);

        $group = end($this->groupStack);
        if ($group) {
            $path = rtrim($group['prefix'], '/') . '/' . ltrim($path, '/');
            $middleware = array_merge($group['middleware'] ?? [], $this->currentRoute['middleware'] ?? []);
        } else {
            $middleware = $this->currentRoute['middleware'] ?? [];
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $action,
            'name' => null,
            'middleware' => $middleware
        ];
        $this->currentRoute = &$this->routes[count($this->routes) - 1];
        return $this;
    }

    public function get(string $path, $action): self
    {
        return $this->addRoute('GET', $path, $action);
    }

    public function post(string $path, $action): self
    {
        return $this->addRoute('POST', $path, $action);
    }

    public function put(string $path, $action): self
    {
        return $this->addRoute('PUT', $path, $action);
    }

    public function delete(string $path, $action): self
    {
        return $this->addRoute('DELETE', $path, $action);
    }

    public function patch(string $path, $action): self
    {
        return $this->addRoute('PATCH', $path, $action);
    }

    public function head(string $path, $action): self
    {
        return $this->addRoute('HEAD', $path, $action);
    }

    public function name(string $name): self
    {
        $this->currentRoute['name'] = $name;
        return $this;
    }

    public function middleware($middleware): self
    {
        if (!isset($this->currentRoute['middleware'])) {
            $this->currentRoute['middleware'] = [];
        }
        $this->currentRoute['middleware'][] = $middleware;
        return $this;
    }

    public function group(array $attributes, callable $callback): void
    {
        // Ensure middleware is always an array
        if (!isset($attributes['middleware'])) {
            $attributes['middleware'] = [];
        }

        // Add group to the stack
        $this->groupStack[] = $attributes;

        // Execute the callback with this router instance
        call_user_func($callback, $this);

        // Remove the last group from the stack
        array_pop($this->groupStack);
    }

    public function resolve()
    {
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route)) {
                return $this->runRoute($route);
            }
        }

        // throw new BoltException('Route not found');
        Error::render("Route Not Found", 404);
    }

    public function url(string $name, array $parameters = []): string
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                $url = $route['path'];
                foreach ($parameters as $key => $value) {
                    $url = str_replace('{' . $key . '}', $value, $url);
                }
                $url = preg_replace('/{[a-zA-Z0-9_]+}/', '', $url);
                return $url;
            }
        }
        throw new BoltException("Route not found for name: {$name}");
    }

    protected function matchRoute(array $route): bool
    {
        $pattern = "@^" . $route['path'] . "$@D";
        return preg_match($pattern, $this->request->getPath(), $matches) &&
            $this->request->getMethod() === $route['method'];
    }

    protected function runRoute(array $route)
    {
        $middlewareQueue = array_reverse($route['middleware']);
        $controllerAction = function () use ($route) {
            return $this->executeAction($route);
        };

        $next = array_reduce($middlewareQueue, function ($next, $middleware) {
            return function () use ($middleware, $next) {
                $middlewareInstance = new $middleware();
                return $middlewareInstance->handle($this->request, $next);
            };
        }, $controllerAction);

        return $next();
    }

    protected function executeAction(array $route)
    {
        $pattern = "@^" . $route['path'] . "$@D";
        preg_match($pattern, $this->request->getPath(), $matches);

        $matches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        if (is_callable($route['action'])) {
            return $this->callAction($route['action'], $matches);
        } elseif (is_array($route['action']) && count($route['action']) === 2) {
            return $this->runControllerAction($route['action'], $matches);
        }

        throw new BoltException('Invalid route action');
    }

    protected function runControllerAction(array $action, array $parameters)
    {
        [$controller, $method] = $action;
        $controllerInstance = new $controller();
        if (!method_exists($controllerInstance, $method)) {
            throw new BoltException('Controller method not found');
        }

        return $this->callAction([$controllerInstance, $method], $parameters);
    }

    protected function callAction(callable $action, array $parameters)
    {
        $reflection = is_array($action)
            ? new ReflectionMethod($action[0], $action[1])
            : new ReflectionFunction($action);

        $args = [];
        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (array_key_exists($name, $parameters)) {
                $args[] = $parameters[$name];
            } elseif ($parameter->getClass()) {
                $args[] = $this->resolveClass($parameter);
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } else {
                throw new BoltException("Cannot resolve parameter '$name'");
            }
        }

        return call_user_func_array($action, $args);
    }

    protected function resolveClass(ReflectionParameter $parameter)
    {
        $class = $parameter->getClass()->getName();
        if ($class === Request::class) {
            return $this->request;
        } elseif ($class === Response::class) {
            return $this->response;
        } else {
            throw new BoltException("Cannot resolve class '$class'");
        }
    }
}
