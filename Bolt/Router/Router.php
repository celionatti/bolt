<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Router Class ===========
 * =================================
 */

namespace Bolt\Bolt\Router;

use Bolt\Bolt\Bolt;
use Bolt\Bolt\Http\Request;
use Bolt\Bolt\Http\Response;
use Bolt\Bolt\BoltException\BoltException;

class Router
{
    public Request $request;
    public Response $response;
    private array $routeMap = [];
    protected array $namedRoutes = [];
    private string $currentPrefix = '';

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $url, $callback)
    {
        $this->routeMap['GET'][$url] = $callback;
    }

    public function post(string $url, $callback)
    {
        $this->routeMap['POST'][$url] = $callback;
    }

    public function put(string $url, $callback)
    {
        $this->routeMap['PUT'][$url] = $callback;
    }

    public function delete(string $url, $callback)
    {
        $this->routeMap['DELETE'][$url] = $callback;
    }

    public function patch(string $url, $callback)
    {
        $this->routeMap['PATCH'][$url] = $callback;
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

    public function group(array $attributes, callable $callback)
    {
        // Store the previous prefix and middleware
        $previousPrefix = $this->currentPrefix;

        // Update the current prefix with the one from the group attributes (if provided)
        $this->currentPrefix .= $attributes['prefix'] ?? '';

        // Execute the callback, which may contain nested routes or middleware
        $callback($this);

        // Restore the previous prefix and middleware after the group is finished
        $this->currentPrefix = $previousPrefix;
    }


    public function getCurrentPrefix(): string
    {
        return $this->currentPrefix;
    }

    /**
     * @return array
     */
    public function getRouteMap($method): array
    {
        return $this->routeMap[$method] ?? [];
    }

    public function getCallback()
    {
        $method = $this->request->method();
        $url = $this->request->getPath();
        // Trim slashes
        $url = trim($url, '/');

        // Get all routes for current request method
        $routes = $this->getRouteMap($method);

        $routeParams = false;

        // Start iterating registed routes
        foreach ($routes as $route => $callback) {
            // Trim slashes
            $route = trim($route, '/');
            $routeNames = [];

            if (!$route) {
                continue;
            }

            // Find all route names from route and save in $routeNames
            if (preg_match_all('/\{(\w+)(:[^}]+)?}/', $route, $matches)) {
                $routeNames = $matches[1];
            }

            // Convert route name into regex pattern
            $routeRegex = "@^" . preg_replace_callback('/\{\w+(:([^}]+))?}/', fn ($m) => isset($m[2]) ? "({$m[2]})" : '(\w+)', $route) . "$@";

            // Test and match current route against $routeRegex
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
        $callback = $this->routeMap[$method][$url] ?? false;
        if (!$callback) {

            $callback = $this->getCallback();

            if ($callback === false) {
                throw new BoltException("Not Found", 404);
            }
        }
        if (is_string($callback)) {
            // Split the string based on the "@" symbol
            $callbackParts = explode('@', $callback);

            // Ensure we have both controller and action parts
            if (count($callbackParts) === 2) {
                $controllerName = $callbackParts[0];
                $actionName = $callbackParts[1];

                // Create the controller instance
                $controllerClass = "\\Bolt\\controllers\\$controllerName";
                $controller = new $controllerClass();
                $controller->action = $actionName;

                // Set the controller in your application (you'll need to modify this according to your application's structure)
                Bolt::$bolt->controller = $controller;

                // Execute any middlewares
                $middlewares = $controller->getMiddlewares();
                foreach ($middlewares as $middleware) {
                    $middleware->execute();
                }

                // Replace the $callback variable with the controller and action
                $callback = [$controller, $actionName];
            }
        }
        if (is_array($callback)) {
            /**
             * @var $controller \Bolt\Bolt\Controller
             */
            $controller = new $callback[0];
            $controller->action = $callback[1];
            Bolt::$bolt->controller = $controller;
            $middlewares = $controller->getMiddlewares();
            foreach ($middlewares as $middleware) {
                $middleware->execute();
            }
            $callback[0] = $controller;
        }
        return call_user_func($callback, $this->request, $this->response);
    }
}
