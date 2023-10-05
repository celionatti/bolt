<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - Router Class ===========
 * =================================
 */

namespace Bolt\Bolt;

use Bolt\Bolt\Http\Request;
use Bolt\Bolt\Http\Response;

class Router_g
{
    private Request $request;
    private Response $response;
    protected array $routes = [];
    private static array $routeMap = [];
    private array $middleware = [];
    private string $groupPrefix = '';
    private array $groupMiddleware = [];
    private array $parameterValidation = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public static function get(string $url, $callback)
    {
        self::registerRoute('GET', $url, $callback);
    }

    public static function post(string $url, $callback)
    {
        self::registerRoute('POST', $url, $callback);
    }

    public static function put(string $url, $callback)
    {
        self::registerRoute('PUT', $url, $callback);
    }

    public static function patch(string $url, $callback)
    {
        self::registerRoute('PATCH', $url, $callback);
    }

    public static function delete(string $url, $callback)
    {
        self::registerRoute('DELETE', $url, $callback);
    }

    public function middleware($middleware)
    {
        $this->middleware[] = $middleware;
    }

    private static function registerRoute($method, $url, $callback)
    {
        self::$routeMap[$method][$url] = $callback;
    }

    public function group($options, $callback)
    {
        // Implement route groups with middleware and prefix
        // Example usage:
        // $router->group(['middleware' => 'auth', 'prefix' => 'admin'], function ($router) {
        //     $router->get('/dashboard', 'AdminController@dashboard');
        // });

        // Extract options
        $middleware = $options['middleware'] ?? [];
        $prefix = $options['prefix'] ?? '';

        // Store the current middleware and prefix
        $originalMiddleware = $this->middleware;
        $originalGroupPrefix = $this->groupPrefix;

        // Apply middleware for the group
        foreach ($middleware as $groupMiddleware) {
            $this->middleware[] = $groupMiddleware;
        }

        // Apply the prefix for the group
        $this->groupPrefix = $this->groupPrefix . $prefix;

        // Execute the group callback
        call_user_func($callback, $this);

        // Restore the original middleware and prefix
        $this->middleware = $originalMiddleware;
        $this->groupPrefix = $originalGroupPrefix;
    }

    public function validate(string $parameter, string $rule)
    {
        // Store the validation rule for the parameter
        $this->parameterValidation[$parameter] = $rule;
    }

    private function validateParameters()
    {
        $routeParams = $this->request->parameters(); // Fetch route parameters from the request.

        foreach ($routeParams as $paramName => $paramValue) {
            if (isset($this->parameterValidation[$paramName])) {
                $rule = $this->parameterValidation[$paramName];

                // Implement parameter validation based on the rule.
                if (!$this->validateParam($paramValue, $rule)) {
                    // Handle validation failure (e.g., return a response or throw an exception)
                    $this->abort(Response::BAD_REQUEST, "Invalid parameter: $paramName");
                }
            }
        }
    }

    private function validateParam($paramValue, $rule)
    {
        // Implement the parameter validation logic based on the provided rule.
        // Example rule: 'int' for integer validation.
        switch ($rule) {
            case 'int':
                return is_numeric($paramValue) && intval($paramValue) == $paramValue;
            case 'string':
                return is_string($paramValue);
            // Add more validation rules as needed.
            default:
                // Handle unknown validation rules.
                return false;
        }
    }

    public function resolve()
    {
        $method = $this->request->method();
        $url = $this->request->getPath();
        $callback = self::$routeMap[$method][$url] ?? false;

        if (!$callback) {
            $callback = $this->getCallback($method, $url);

            if ($callback === false) {
                $this->abort(Response::BAD_REQUEST, "Bad Request");
            }
        }

        $this->applyMiddleware();
        $this->validateParameters(); // Validate route parameters before executing the route handler.

        if (is_string($callback)) {
            return require Bolt::$bolt->pathResolver->base_path($callback);
        }

        if (is_callable($callback)) {
            return call_user_func($callback);
        }

        if (is_array($callback)) {
            $controller = new $callback[0];
            $controller->action = $callback[1];
            return call_user_func($callback, $this->request, $this->response);
        }
    }

    public function getCallback()
    {
        $method = $this->request->method();
        $url = $this->request->getPath();
        // Trim slashes
        $url = trim($url, '/');

        // Get all routes for current request method
        $routes = self::$routeMap[$method][$url] ?? false;

        $routeParams = false;

        // Start iterating register routes
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

    private function applyMiddleware()
    {
        foreach ($this->middleware as $middleware) {
            // Implement middleware execution before route handler
            if (is_callable($middleware)) {
                call_user_func($middleware, $this->request, $this->response);
            }
        }
    }

    protected function abort($statusCode, $content)
    {
        $response = new Response();
        $response->setContent($content);
        $response->setStatusCode($statusCode);
        $response->send();
    }
}
