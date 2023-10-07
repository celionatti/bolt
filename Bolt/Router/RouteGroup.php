<?php

declare(strict_types=1);

/**
 * ==========================================
 * ================         =================
 * Route Group
 * ================         =================
 * ==========================================
 */

namespace Bolt\Bolt\Router;

class RouteGroup
{
    private $router;
    private $prefix;
    private $middleware;

    public function __construct($router, $prefix, $middleware)
    {
        $this->router = $router;
        $this->prefix = $prefix;
        $this->middleware = $middleware;
    }

    public function addRoute($method, $uri, $controllerMethod, $middleware = [])
    {
        $uri = $this->prefix . $uri;
        $middleware = array_merge($this->middleware, $middleware);
        $this->router->addRoute($method, $uri, $controllerMethod, $middleware);
    }
}