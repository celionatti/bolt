<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Router Class ===========
 * =================================
 */

namespace Bolt\Bolt\Router;

use Bolt\Bolt\Http\Request;
use Bolt\Bolt\Http\Response;

class Router
{
    private $routes = [];
    private $middleware = [];

    public function addRoute($method, $uri, $controllerMethod, $middleware = [])
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controllerMethod' => $controllerMethod,
            'middleware' => $middleware,
        ];
    }

    public function addMiddleware($name, $callback)
    {
        $this->middleware[$name] = $callback;
    }

    public function group($prefix, $middleware = [])
    {
        $currentMiddleware = array_merge($this->getCurrentMiddleware(), $middleware);
        return new RouteGroup($this, $prefix, $currentMiddleware);
    }

    public function getCurrentMiddleware()
    {
        return end($this->middleware);
    }

    public function handleRequest()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            $pattern = $this->buildPattern($route['uri']);
            if ($route['method'] == $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove the full match
                $this->executeMiddleware($route['middleware']);
                list($controller, $method) = explode('@', $route['controllerMethod']);
                $controllerInstance = new $controller();
                call_user_func_array([$controllerInstance, $method], $matches);
                return;
            }
        }

        // Handle 404 (Not Found)
        http_response_code(404);
        echo '404 - Page not found';
    }

    private function buildPattern($uri)
    {
        $pattern = preg_replace('/\//', '\/', $uri);
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<\1>[^\/]+)', $pattern);
        $pattern = '/^' . $pattern . '$/';
        return $pattern;
    }

    private function executeMiddleware($middleware)
    {
        foreach ($middleware as $middlewareName) {
            $middlewareCallback = $this->middleware[$middlewareName];
            call_user_func($middlewareCallback);
        }
    }
}




/** GRoup */

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