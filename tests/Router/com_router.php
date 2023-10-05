<?php

namespace Bolt\Bolt;

use Bolt\Bolt\Http\Request;
use Bolt\Bolt\Http\Response;

class Router
{
    private Request $request;
    private Response $response;
    protected array $routes = [];
    private static array $routeMap = [];
    private array $middleware = [];
    private string $groupPrefix = '';
    private array $groupMiddleware = [];

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

    private function getCallback($method, $url)
    {
        // Implement route parameter matching and validation
        foreach (self::$routeMap[$method] as $route => $callback) {
            $route = rtrim($this->groupPrefix . '/' . $route, '/');
            $urlParts = explode('/', trim($url, '/'));
            $routeParts = explode('/', $route);

            if (count($urlParts) !== count($routeParts)) {
                continue;
            }

            $routeParams = [];
            $match = true;

            foreach ($routeParts as $index => $part) {
                if (strpos($part, '{') === 0 && strpos($part, '}') === strlen($part) - 1) {
                    // This part is a parameter
                    $paramName = substr($part, 1, -1);
                    $routeParams[$paramName] = $urlParts[$index];
                } elseif ($part !== $urlParts[$index]) {
                    // This part doesn't match
                    $match = false;
                    break;
                }
            }

            if ($match) {
                $this->request->setParameters($routeParams);
                return $callback;
            }
        }

        return false;
    }

    private function applyMiddleware()
    {
        foreach ($this->middleware as $middleware) {
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
