<?php

declare(strict_types=1);

/**
 * =================================
 * Bolt - RouterGroup Class ========
 * =================================
 */

namespace celionatti\Bolt\Router;

use celionatti\Bolt\Bolt;
use celionatti\Bolt\Http\Request;
use celionatti\Bolt\Http\Response;
use celionatti\Bolt\BoltException\BoltException;

class RouterGroup
{
    public Request $request;
    public Response $response;
    private array $routeMap = [];
    protected array $namedRoutes = [];
    protected string $currentPrefix = '';
    protected ?string $currentRouteName = null;
    protected array $currentMiddlewares = [];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    private function addRoute(string $method, string $url, $callback): self
    {
        $url = $this->currentPrefix . $url;
        $this->routeMap[$method][$url] = [
            'callback' => $callback,
            'name' => $this->currentRouteName,
            'middlewares' => $this->currentMiddlewares,
        ];

        if ($this->currentRouteName) {
            $this->namedRoutes[$this->currentRouteName] = $url;
        }

        // Reset the current route name and middlewares
        $this->currentRouteName = null;
        $this->currentMiddlewares = [];

        return $this;
    }

    public function get(string $url, $callback): self
    {
        return $this->addRoute('GET', $url, $callback);
    }

    public function post(string $url, $callback): self
    {
        return $this->addRoute('POST', $url, $callback);
    }

    public function put(string $url, $callback): self
    {
        return $this->addRoute('PUT', $url, $callback);
    }

    public function delete(string $url, $callback): self
    {
        return $this->addRoute('DELETE', $url, $callback);
    }

    public function patch(string $url, $callback): self
    {
        return $this->addRoute('PATCH', $url, $callback);
    }

    public function resource(string $url, string $controller): self
    {
        $this->get("$url", "$controller@index");
        $this->get("$url/create", "$controller@create");
        $this->post("$url", "$controller@store");
        $this->get("$url/{id}", "$controller@show");
        $this->get("$url/{id}/edit", "$controller@edit");
        $this->put("$url/{id}", "$controller@update");
        $this->delete("$url/{id}", "$controller@destroy");

        return $this;
    }

    public function name(string $name): self
    {
        $this->currentRouteName = $name;
        return $this;
    }

    public function middleware(array $middlewares): self
    {
        $this->currentMiddlewares = $middlewares;
        return $this;
    }

    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->currentPrefix;
        $this->currentPrefix .= $attributes['prefix'] ?? '';
        $callback($this);
        $this->currentPrefix = $previousPrefix;
    }

    public function getRouteMap($method): array
    {
        return $this->routeMap[$method] ?? [];
    }

    public function generateUrl(string $name, array $params = []): string
    {
        if (isset($this->namedRoutes[$name])) {
            $url = $this->namedRoutes[$name];
            foreach ($params as $key => $value) {
                $url = str_replace("{{$key}}", $value, $url);
            }
            return $url;
        }
        return '';
    }

    private function getCallback()
    {
        $method = $this->request->getMethod();
        $url = trim($this->request->getPath(), '/');
        $routes = $this->getRouteMap($method);
        $routeParams = false;

        foreach ($routes as $route => $routeInfo) {
            $route = trim($route, '/');
            $routeNames = [];

            if (!$route) continue;

            if (preg_match_all('/\{(\w+)(:[^}]+)?}/', $route, $matches)) {
                $routeNames = $matches[1];
            }

            $routeRegex = "@^" . preg_replace_callback('/\{(\w+)(:([^}]+))?}/', function ($m) {
                $paramPattern = isset($m[3]) ? $m[3] : '\w+';
                return "($paramPattern)";
            }, $route) . "$@";

            if (preg_match_all($routeRegex, $url, $valueMatches)) {
                $values = [];
                for ($i = 1; $i < count($valueMatches); $i++) {
                    $values[] = $valueMatches[$i][0];
                }
                $routeParams = array_combine($routeNames, $values);
                $this->request->setParameters($routeParams);
                return $routeInfo;
            }
        }

        return false;
    }

    public function resolve()
    {
        $method = $this->request->getMethod();
        $url = $this->request->getPath();
        $routeInfo = $this->routeMap[$method][$url] ?? $this->getCallback();

        if (!$routeInfo) {
            throw new BoltException("Callback - [ Method: {$method}, Path: {$url} ] - Not Found", 404, 'info');
        }

        $callback = $routeInfo['callback'];
        if (is_string($callback)) {
            $callbackParts = explode('@', $callback);
            if (count($callbackParts) === 2) {
                $controllerName = $callbackParts[0];
                $actionName = $callbackParts[1];
                if (!method_exists($controllerName, $actionName)) {
                    throw new BoltException("[{$controllerName}] - [{$actionName}] Method Not Found", 404, "error");
                }
                $controllerClass = "\\PhpStrike\\controllers\\$controllerName";
                $controller = new $controllerClass();
                $controller->action = $actionName;
                Bolt::$bolt->controller = $controller;
                foreach ($routeInfo['middlewares'] as $middleware) {
                    (new $middleware())->execute();
                }
                $callback = [$controller, $actionName];
            }
        } elseif (is_array($callback)) {
            $controller = new $callback[0];
            $controller->action = $callback[1];
            Bolt::$bolt->controller = $controller;
            foreach ($routeInfo['middlewares'] as $middleware) {
                (new $middleware())->execute();
            }
            $callback[0] = $controller;
        }
        return call_user_func($callback, $this->request, $this->response);
    }
}
