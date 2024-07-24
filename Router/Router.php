<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Router Class ===========
 * =================================
 */

namespace celionatti\Bolt\Router;

use celionatti\Bolt\Http\Request;
use celionatti\Bolt\Http\Response;
use celionatti\Bolt\BoltException\BoltException;

class Router
{
    protected $routes = [];
    protected $request;
    protected $response;
    protected $currentRoute = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function addRoute($method, $path, $action)
    {
        $path = preg_replace('/{\:(\w+)}/', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'action' => $action,
            'name' => null,
            'middleware' => []
        ];
        $this->currentRoute = &$this->routes[count($this->routes) - 1];
        return $this;
    }

    public function get($path, $action)
    {
        return $this->addRoute('GET', $path, $action);
    }

    public function post($path, $action)
    {
        return $this->addRoute('POST', $path, $action);
    }

    public function put($path, $action)
    {
        return $this->addRoute('PUT', $path, $action);
    }

    public function delete($path, $action)
    {
        return $this->addRoute('DELETE', $path, $action);
    }

    public function patch($path, $action)
    {
        return $this->addRoute('PATCH', $path, $action);
    }

    public function head($path, $action)
    {
        return $this->addRoute('HEAD', $path, $action);
    }

    public function name($name)
    {
        $this->currentRoute['name'] = $name;
        return $this;
    }

    public function middleware($middleware)
    {
        $this->currentRoute['middleware'][] = $middleware;
        return $this;
    }

    public function resolve()
    {
        foreach ($this->routes as $route) {
            if ($this->matchRoute($route)) {
                return $this->runRoute($route);
            }
        }

        throw new BoltException('Route not found');
    }

    protected function matchRoute($route)
    {
        $pattern = "@^" . $route['path'] . "$@D";
        return preg_match($pattern, $this->request->getPath(), $matches) &&
            $this->request->getMethod() === $route['method'];
    }

    protected function runRoute($route)
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

    protected function executeAction($route)
    {
        $pattern = "@^" . $route['path'] . "$@D";
        preg_match($pattern, $this->request->getPath(), $matches);

        foreach ($matches as $key => $value) {
            if (is_int($key)) {
                unset($matches[$key]);
            }
        }

        if (is_callable($route['action'])) {
            return call_user_func_array($route['action'], array_merge([$this->request, $this->response], array_values($matches)));
        } elseif (is_array($route['action']) && count($route['action']) === 2) {
            return $this->runControllerAction($route['action'], array_values($matches));
        }

        throw new BoltException('Invalid route action');
    }

    protected function runControllerAction($action, $parameters)
    {
        list($controller, $method) = $action;
        $controllerInstance = new $controller();
        if (!method_exists($controllerInstance, $method)) {
            throw new BoltException('Controller method not found');
        }
        return call_user_func_array([$controllerInstance, $method], array_merge([$this->request, $this->response], $parameters));
    }
}
