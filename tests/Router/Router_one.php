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

class Router_one
{
    private Request $request;
    private Response $response;
    protected array $routes = [];
    private static array $routeMap = [];
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public static function get(string $url, $callback)
    {
        self::$routeMap['GET'][$url] = $callback;
    }

    public static function post(string $url, $callback)
    {
        self::$routeMap['POST'][$url] = $callback;
    }

    public static function put(string $url, $callback)
    {
        self::$routeMap['PUT'][$url] = $callback;
    }

    public static function patch(string $url, $callback)
    {
        self::$routeMap['PATCH'][$url] = $callback;
    }

    public static function delete(string $url, $callback)
    {
        self::$routeMap['DELETE'][$url] = $callback;
    }

    /**
     * @param $method
     * @return array
     */
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

        // Get all routes for current request method
        $routes = $this->getRouteMap($method);

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

    /**
     * @throws Exception
     */
    public function resolve()
    {
        $method = $this->request->method();
        $url = $this->request->getPath();
        $callback = self::$routeMap[$method][$url] ?? false;

        if (!$callback) {

            $callback = $this->getCallback();

            if ($callback === false) {
                $this->abort(Response::BAD_REQUEST, "Bad Request");
            }
        }
        if (is_string($callback)) {
            return require Bolt::$bolt->pathResolver->base_path($callback);
            // return require base_path($callback);
        }

        if (is_callable($callback)) {
            return call_user_func($callback);
        }

        if (is_array($callback)) {

            /**
             * @var $controller Controller
             */
            $controller = new $callback[0];
            $controller->action = $callback[1];
            // Application::$app->controller = $controller;

            $callback[0] = $controller;
        }

        return call_user_func($callback, $this->request, $this->response);
    }


    /**
     * @param int $code
     * @return void
     * @throws Exception
     */
    protected function abort($statusCode, $content): void
    {
        $response = new Response();
        $response->setContent($content);
        $response->setStatusCode($statusCode);
        $response->send();
    }
}
