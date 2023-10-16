<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Router Class ===========
 * =================================
 */

namespace Bolt\Bolt\Router;

use Bolt\Bolt\Bolt;
use GuzzleHttp\Client;
use Bolt\Bolt\Http\Request;
use Bolt\Bolt\Http\Response;
use Bolt\Bolt\BoltException\BoltException;

class Router
{
    public Request $request;
    public Response $response;
    private array $routeMap = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $url, $callback)
    {
        $this->routeMap['GET'][$url] = $callback;

        return $this;
    }

    public function post(string $url, $callback)
    {
        $this->routeMap['POST'][$url] = $callback;
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
