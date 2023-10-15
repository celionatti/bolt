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
use GuzzleHttp\Client;

class Router_t
{
    public Request $request;
    public Response $response;
    protected array $routes = [];
    private static array $routeMap = [];
    private array $middleware = [];
    private array $groupAttributes = [];
    protected array $namedRoutes = [];
    private string $currentPrefix = '';

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function BoltRouter()
    {
        return new BoltRouter($this);
    }

    public static function get(string $url, $callback)
    {
        // Add the route to the route map and support for named routes
        self::$routeMap['GET'][$url] = $callback;
    }

    // Implement similar methods for POST, PUT, PATCH, DELETE
    public static function post(string $url, $callback)
    {
        // Add the route to the route map and support for named routes
        self::$routeMap['POST'][$url] = $callback;
    }

    public static function put(string $url, $callback)
    {
        // Add the route to the route map and support for named routes
        self::$routeMap['PUT'][$url] = $callback;
    }

    public static function patch(string $url, $callback)
    {
        // Add the route to the route map and support for named routes
        self::$routeMap['PATCH'][$url] = $callback;
    }

    public static function delete(string $url, $callback)
    {
        // Add the route to the route map and support for named routes
        self::$routeMap['DELETE'][$url] = $callback;
    }

    public function namedRoute(string $name, string $url, $callback)
    {
        // Add the route to the route map and support for named routes
        self::$routeMap['GET'][$url] = $callback;
        $this->namedRoutes[$name] = $url;
    }

    public function generateUrl(string $name, array $params = []): string
    {
        // Generate URLs using named routes
        if (isset($this->namedRoutes[$name])) {
            $url = $this->namedRoutes[$name];

            foreach ($params as $key => $value) {
                $url = str_replace("{{$key}}", $value, $url);
            }

            return $url;
        }

        return '';
    }

    // public function group(array $attributes, callable $callback)
    // {
    //     // Implement route grouping with common attributes or middleware
    //     // $this->groupAttributes = $attributes;
    //     // $callback($this);
    //     // $this->groupAttributes = [];

    //     $previousPrefix = $this->currentPrefix;
    //     $this->currentPrefix .= $attributes['prefix'] ?? '';

    //     $callback($this);

    //     $this->currentPrefix = $previousPrefix;
    // }

    public function group(array $attributes, callable $callback)
    {
        // Store the previous prefix and middleware
        $previousPrefix = $this->currentPrefix;
        $previousMiddleware = $this->middleware;

        // Update the current prefix with the one from the group attributes (if provided)
        $this->currentPrefix .= $attributes['prefix'] ?? '';

        // Apply middleware from the group attributes (if provided)
        if (isset($attributes['middleware'])) {
            // If middleware is an array, merge it with any previous middleware
            if (is_array($attributes['middleware'])) {
                $this->middleware = array_merge($this->middleware, $attributes['middleware']);
            } else {
                $this->middleware[] = $attributes['middleware'];
            }
        }

        // Execute the callback, which may contain nested routes or middleware
        $callback($this);

        // Restore the previous prefix and middleware after the group is finished
        $this->currentPrefix = $previousPrefix;
        $this->middleware = $previousMiddleware;
    }


    public function getCurrentPrefix(): string
    {
        return $this->currentPrefix;
    }

    public function middleware(string $middleware)
    {
        // Add middleware to the current route or group
        $this->middleware[] = $middleware;
    }

    public function resource(string $url, string $controller)
    {
        // Implement resource controllers for CRUD routes
        self::get("$url", "$controller@index");
        self::get("$url/create", "$controller@create");
        self::post("$url", "$controller@store");
        self::get("$url/{id}", "$controller@show");
        self::get("$url/{id}/edit", "$controller@edit");
        self::put("$url/{id}", "$controller@update");
        self::delete("$url/{id}", "$controller@destroy");
    }

    public function getRouteMap($method): array
    {
        return self::$routeMap[$method] ?? [];
    }


    public function getCallback()
    {
        $method = $this->request->method();
        $url = $this->request->getPath();
        // Trim slashes
        $url = trim($url, '/');

        // Get all routes for the current request method
        $routes = $this->getRouteMap($method);

        $routeParams = false;

        // Start iterating registered routes
        foreach ($routes as $route => $callback) {
            // Trim slashes
            $route = trim($route, '/');
            $routeNames = [];

            if (!$route) {
                continue;
            }

            // Find all route names from the route and save in $routeNames
            if (preg_match_all('/\{(\w+)(:[^}]+)?}/', $route, $matches)) {
                $routeNames = $matches[1];
            }

            // Convert route name into a regex pattern
            $routeRegex = "@^" . preg_replace_callback('/\{\w+(:([^}]+))?}/', fn ($m) => isset($m[2]) ? "({$m[2]})" : '(\w+)', $route) . "$@";

            // Test and match the current route against $routeRegex
            if (preg_match_all($routeRegex, $url, $valueMatches)) {
                $values = [];
                for ($i = 1; $i < count($valueMatches); $i++) {
                    $values[] = $valueMatches[$i][0];
                }
                $routeParams = array_combine($routeNames, $values);

                $this->request->setParameters($routeParams);
                return $callback;
            }
        }

        return false;
    }


    public function resolve()
    {
        $method = $this->request->method();
        $url = $this->request->getPath();
        $callback = self::$routeMap[$method][$url] ?? false;

        if (!$callback) {
            $callback = $this->getCallback();
            if ($callback === false) {
                $this->abort(Response::BAD_REQUEST);
            }
        }

        // Apply middleware
        foreach ($this->middleware as $middleware) {
            // Implement middleware execution logic here
        }

        if (is_string($callback)) {
            echo "Is String - " . $callback;
            // return require base_path($callback);
        }

        if (is_callable($callback)) {
            return call_user_func($callback);
        }

        if (is_array($callback)) {
            $controller = new $callback[0];
            $controller->action = $callback[1];
            $callback[0] = $controller;
        }

        return call_user_func($callback, $this->request, $this->response);
    }

    protected function abort(int $code = Response::NOT_FOUND)
    {
        http_response_code($code);
        echo "errors/{$code}";
        die();
    }
}
