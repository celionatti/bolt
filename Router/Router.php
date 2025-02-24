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
use celionatti\Bolt\Container\Container;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Debug\Error;
use celionatti\Bolt\Middleware\MiddlewarePipeline;
use celionatti\Bolt\Router\RouteBinding;

class Router
{
    protected array $routes = [];
    protected array $wildcardRoutes = [];
    protected array $groupStack = [];
    protected Request $request;
    protected Response $response;
    protected Container $container;
    protected array $currentRoute = [];
    protected string $defaultApiVersion = 'v1';
    protected array $globalMiddleware = [];
    protected array $routeCache = [];
    protected bool $cacheRoutes = false;

    public function __construct(Request $request, Response $response, Container $container)
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;

        // Register core dependencies
        $this->container->singleton(Request::class, $request);
        $this->container->singleton(Response::class, $response);

        // Configure RouteBinding
        RouteBinding::setContainer($this->container);
    }

    public function enableRouteCaching(bool $enabled = true): self
    {
        $this->cacheRoutes = $enabled;
        return $this;
    }

    public function setGlobalMiddleware(array $middleware): self
    {
        $this->globalMiddleware = $middleware;
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
        $attributes['prefix'] = 'api/' . $version . (isset($attributes['prefix']) ? '/' . ltrim($attributes['prefix'], '/') : '');
        $attributes['as'] = 'api.' . $version . (isset($attributes['as']) ? '.' . ltrim($attributes['as'], '.') : '');
        $attributes['middleware'] = $attributes['middleware'] ?? ['api'];
        return $attributes;
    }

    public function addRoute(string $method, string $path, $action): self
    {
        $method = strtoupper($method);
        $route = $this->createRoute($method, $path, $action);

        if ($this->isWildcardRoute($path)) {
            $this->wildcardRoutes[$method][] = $route;
        } else {
            $this->routes[$method][] = $route;
        }

        $this->currentRoute = &$route;
        return $this;
    }

    protected function isWildcardRoute(string $path): bool
    {
        return $path === '*' || strpos($path, '/*') !== false;
    }

    protected function createRoute(string $method, string $path, $action): array
    {
        $group = end($this->groupStack);
        $route = [
            'method' => $method,
            'original_path' => $this->applyGroupPrefix($path, $group),
            'action' => $action,
            'name' => $this->generateRouteName($group),
            'middleware' => $this->mergeGroupMiddleware($group),
            'wheres' => [],
            'bindings' => [],
            'compiled_regex' => '',
        ];

        $route['compiled_regex'] = $this->compileRouteRegex(
            $route['original_path'],
            $route['wheres']
        );

        return $route;
    }

    protected function applyGroupPrefix(string $path, ?array $group): string
    {
        if (!$group) return $path;

        $prefix = $group['prefix'] ?? '';
        return rtrim($prefix, '/') . '/' . ltrim($path, '/');
    }

    protected function generateRouteName(?array $group): string
    {
        $name = '';
        if ($group && isset($group['as'])) {
            $name = rtrim($group['as'], '.') . '.';
        }
        return $name;
    }

    protected function mergeGroupMiddleware(?array $group): array
    {
        return array_merge(
            $group['middleware'] ?? [],
            $this->globalMiddleware
        );
    }

    public function resolve()
    {
        if ($this->cacheRoutes && !empty($this->routeCache)) {
            return $this->resolveFromCache();
        }

        $method = $this->request->getMethod();
        $path = $this->request->getPath();

        // Check regular routes first
        foreach ($this->routes[$method] ?? [] as $route) {
            $matches = [];
            if ($this->matchRoute($route, $path, $matches)) {
                return $this->runRoute($route, $matches);
            }
        }

        // Check wildcard routes next
        foreach ($this->wildcardRoutes[$method] ?? [] as $route) {
            $matches = [];
            if ($this->matchRoute($route, $path, $matches)) {
                return $this->runRoute($route, $matches);
            }
        }

        $this->handleNotFound();
    }

    protected function resolveFromCache()
    {
        // Cached route resolution implementation
    }

    protected function matchRoute(array $route, string $path, array &$matches): bool
    {
        return (bool) preg_match($route['compiled_regex'], $path, $matches);
    }

    protected function compileRouteRegex(string $path, array $wheres): string
    {
        // Handle wildcard routes
        if ($path === '*') {
            return '@^.*$@D';
        }

        // Convert path parameters to regex patterns
        $regexPath = preg_replace_callback('/{\:(\w+)}/', function ($matches) use ($wheres) {
            $param = $matches[1];
            $regex = $wheres[$param] ?? '[^/]+';
            return "(?P<{$param}>{$regex})";
        }, $path);

        // Handle trailing wildcards
        $regexPath = str_replace('/*', '(?:/.*)?', $regexPath);

        return "@^{$regexPath}$@D";
    }

    protected function runRoute(array $route, array $matches)
    {
        $this->applyRouteBindings($route, $matches);
        $middleware = array_merge($this->globalMiddleware, $route['middleware']);
        $pipeline = new MiddlewarePipeline($middleware, $this->container);

        return $pipeline->handle(
            $this->request,
            function ($request) use ($route, $matches) {
                return $this->executeAction($route, $matches);
            }
        );
    }

    protected function applyRouteBindings(array $route, array &$matches)
    {
        foreach ($route['bindings'] as $param => $binding) {
            if (isset($matches[$param])) {
                $matches[$param] = RouteBinding::resolve($binding, $matches[$param]);
            }
        }
    }

    // HTTP method shortcuts
    public function get(string $path, $action): self { return $this->addRoute('GET', $path, $action); }
    public function post(string $path, $action): self { return $this->addRoute('POST', $path, $action); }
    public function put(string $path, $action): self { return $this->addRoute('PUT', $path, $action); }
    public function delete(string $path, $action): self { return $this->addRoute('DELETE', $path, $action); }
    public function patch(string $path, $action): self { return $this->addRoute('PATCH', $path, $action); }
    public function head(string $path, $action): self { return $this->addRoute('HEAD', $path, $action); }
    public function options(string $path, $action): self { return $this->addRoute('OPTIONS', $path, $action); }

    // Wildcard method
    public function any(string $path, $action): self
    {
        $this->addRoute('GET', $path, $action);
        $this->addRoute('POST', $path, $action);
        $this->addRoute('PUT', $path, $action);
        $this->addRoute('PATCH', $path, $action);
        $this->addRoute('DELETE', $path, $action);
        $this->addRoute('HEAD', $path, $action);
        $this->addRoute('OPTIONS', $path, $action);
        return $this;
    }

    public function name(string $name): self
    {
        $this->currentRoute['name'] = $name;
        return $this;
    }

    public function middleware($middleware): self
    {
        $this->currentRoute['middleware'] = array_merge(
            $this->currentRoute['middleware'],
            (array)$middleware
        );
        return $this;
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = array_merge([
            'prefix' => '',
            'as' => '',
            'middleware' => []
        ], $attributes);

        call_user_func($callback, $this);
        array_pop($this->groupStack);
    }

    public function where(array $constraints): self
    {
        $this->currentRoute['wheres'] = array_merge(
            $this->currentRoute['wheres'],
            $constraints
        );
        return $this;
    }

    public function bind(string $param, string $model): self
    {
        $this->currentRoute['bindings'][$param] = $model;
        return $this;
    }

    protected function executeAction(array $route, array $matches)
    {
        $matches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

        if (is_callable($route['action'])) {
            return $this->callAction($route['action'], $matches);
        }

        if (is_array($route['action']) && count($route['action']) === 2) {
            return $this->runControllerAction($route['action'], $matches);
        }

        if (is_string($route['action']) && str_contains($route['action'], '@')) {
            return $this->runControllerStringAction($route['action'], $matches);
        }

        throw new BoltException('Invalid route action');
    }

    protected function runControllerStringAction(string $action, array $parameters)
    {
        [$controller, $method] = explode('@', $action, 2);
        return $this->runControllerAction([$controller, $method], $parameters);
    }

    protected function runControllerAction(array $action, array $parameters)
    {
        [$controllerClass, $method] = $action;
        $controller = $this->container->make($controllerClass);

        if (!method_exists($controller, $method)) {
            throw new BoltException("Controller method {$method} not found");
        }

        return $this->callAction([$controller, $method], $parameters);
    }

    protected function callAction(callable $action, array $parameters)
    {
        return $this->container->call($action, $parameters);
    }

    public function url(string $name, array $parameters = [], bool $absolute = false): string
    {
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $route) {
                if ($route['name'] === $name) {
                    return $this->generateUrl($route, $parameters, $absolute);
                }
            }
        }
        throw new BoltException("Route '{$name}' not found");
    }

    protected function generateUrl(array $route, array $parameters, bool $absolute): string
    {
        $url = $route['original_path'];
        $missing = [];

        $url = preg_replace_callback('/{\:(\w+)}/', function ($m) use (&$parameters, &$missing) {
            if (!isset($parameters[$m[1]])) {
                $missing[] = $m[1];
                return '';
            }
            $value = $parameters[$m[1]];
            unset($parameters[$m[1]]);
            return $value;
        }, $url);

        if (!empty($missing)) {
            throw new BoltException("Missing parameters: " . implode(', ', $missing));
        }

        if ($absolute) {
            $url = $this->request->getBaseUrl() . $url;
        }

        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }

    protected function handleNotFound()
    {
        if ($this->request->prefersJson()) {
            $this->response->json(['error' => 'Not Found'], 404)->send();
        } else {
            Error::render("Route Not Found", 404);
        }
        exit;
    }
}
