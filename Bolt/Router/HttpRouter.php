<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - HttpRouter Class ===========
 * =================================
 */

namespace Bolt\Bolt\Router;


class HttpRouter
{
    protected $routes = [];
    protected $middleware = [];

    public function addRoute($method, $uri, $action)
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
        ];
    }

    public function middleware($middleware)
    {
        $this->middleware[] = $middleware;
    }

    public function match($method, $uri)
    {
        foreach ($this->routes as $route) {
            if ($route['method'] == $method && preg_match($this->compileRouteRegex($route['uri']), $uri, $matches)) {
                array_shift($matches); // Remove the full match
                return [
                    'action' => $route['action'],
                    'params' => $matches,
                ];
            }
        }
        return null;
    }

    public function run($method, $uri)
    {
        $route = $this->match($method, $uri);

        if (!$route) {
            // Handle 404 - Not Found
            // You can throw an exception, render a 404 page, etc.
            return "404 Not Found";
        }

        // Apply middleware
        foreach ($this->middleware as $middleware) {
            // Execute middleware logic here
        }

        list($controller, $method) = explode('@', $route['action']);

        // You can create a controller instance and call the method here
        $controllerInstance = new $controller;
        return $controllerInstance->$method(...$route['params']);
    }

    protected function compileRouteRegex($uri)
    {
        // Convert named parameters like {id} to regular expression
        return "@^" . preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $uri) . "$@";
    }
}
