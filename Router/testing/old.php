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

class oldRouter
{
    protected array $routes = [];
    protected array $groupStack = [];
    protected Request $request;
    protected Response $response;
    protected array $currentRoute = [];
    protected string $defaultApiVersion = 'v1';

    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    public function setDefaultApiVersion(string $version): self
    {
        $this->defaultApiVersion = $version;
        return $this;
    }

    public function apiGroup(string $version, array $attributes, callable $callback): void
    {
        $attributes = $this->prepareApiAttributes($version, $attributes);
        $this->group($attributes, $callback);
    }

    public function api(array $attributes, callable $callback): void
    {
        $this->apiGroup($this->defaultApiVersion, $attributes, $callback);
    }

    protected function prepareApiAttributes(string $version, array $attributes): array
    {
        // Handle prefix
        $apiPrefix = 'api/' . $version;
        if (isset($attributes['prefix'])) {
            $attributes['prefix'] = $apiPrefix . '/' . ltrim($attributes['prefix'], '/');
        } else {
            $attributes['prefix'] = $apiPrefix;
        }

        // Handle name prefix
        $namePrefix = 'api.' . $version . '.';
        if (isset($attributes['as'])) {
            $attributes['as'] = $namePrefix . ltrim($attributes['as'], '.');
        } else {
            $attributes['as'] = $namePrefix;
        }

        // Set default API middleware if none provided
        if (!isset($attributes['middleware'])) {
            $attributes['middleware'] = ['api'];
        }

        return $attributes;
    }

    public function addRoute(string $method, string $path, $action): self
    {
        $group = end($this->groupStack);
        $middleware = [];
        $originalPath = $path;

        if ($group) {
            $originalPath = rtrim($group['prefix'], '/') . '/' . ltrim($originalPath, '/');
            $middleware = $group['middleware'] ?? [];
        }

        $this->routes[] = [
            'method' => $method,
            'original_path' => $originalPath,
            'action' => $action,
            'name' => null,
            'middleware' => $middleware,
            'wheres' => [],
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
        $prefix = '';
        foreach ($this->groupStack as $group) {
            if (isset($group['as'])) {
                $prefix .= $group['as'];
            }
        }
        $this->currentRoute['name'] = $prefix . $name;
        return $this;
    }

    public function middleware($middleware): self
    {
        $this->currentRoute['middleware'][] = $middleware;
        return $this;
    }

    public function group(array $attributes, callable $callback): void
    {
        $attributes['middleware'] = $attributes['middleware'] ?? [];
        $this->groupStack[] = $attributes;
        call_user_func($callback, $this);
        array_pop($this->groupStack);
    }

    public function resolve()
    {
        foreach ($this->routes as $route) {
            $matches = [];
            if ($this->matchRoute($route, $matches)) {
                return $this->runRoute($route, $matches);
            }
        }
        Error::render("Route Not Found", 404);
    }

    public function url(string $name, array $parameters = []): string
    {
        foreach ($this->routes as $route) {
            if ($route['name'] === $name) {
                $url = $route['original_path'];
                foreach ($parameters as $key => $value) {
                    $url = str_replace('{' . $key . '}', $value, $url);
                }
                $url = preg_replace('/{[a-zA-Z0-9_]+}/', '', $url);
                return $url;
            }
        }
        throw new BoltException("Route not found for name: {$name}");
    }

    public function where(string $param, string $regex): self
    {
        $this->currentRoute['wheres'][$param] = $regex;
        return $this;
    }

    protected function matchRoute(array $route, array &$matches): bool
    {
        $pattern = $this->compileRouteRegex($route['original_path'], $route['wheres']);
        return preg_match($pattern, $this->request->getPath(), $matches) &&
            $this->request->getMethod() === $route['method'];
    }

    protected function compileRouteRegex(string $path, array $wheres): string
    {
        $regexPath = preg_replace_callback('/{\:(\w+)}/', function ($matches) use ($wheres) {
            $param = $matches[1];
            $regex = $wheres[$param] ?? '[^/]+';
            return "(?P<{$param}>{$regex})";
        }, $path);

        return "@^{$regexPath}$@D";
    }

    protected function runRoute(array $route, array $matches)
    {
        $middlewareQueue = array_reverse($route['middleware']);
        $controllerAction = function () use ($route, $matches) {
            return $this->executeAction($route, $matches);
        };

        $next = array_reduce($middlewareQueue, function ($next, $middleware) {
            return function () use ($middleware, $next) {
                $middlewareInstance = new $middleware();
                return $middlewareInstance->handle($this->request, $next);
            };
        }, $controllerAction);

        return $next();
    }

    protected function executeAction(array $route, array $matches)
    {
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
