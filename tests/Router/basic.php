<?php

// router.php

class Router
{
    private $routes = [];

    public function addRoute($method, $uri, $controllerMethod)
    {
        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controllerMethod' => $controllerMethod,
        ];
    }

    public function handleRequest()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {
            if ($route['method'] == $method && $route['uri'] == $uri) {
                list($controller, $method) = explode('@', $route['controllerMethod']);
                $controllerInstance = new $controller();
                $controllerInstance->$method();
                return;
            }
        }

        // Handle 404 (Not Found)
        http_response_code(404);
        echo '404 - Page not found';
    }
}
