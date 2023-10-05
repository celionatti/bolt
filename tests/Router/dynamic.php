<?php


private function getCallback($method, $url)
{
    // Iterate through routes to find a matching route with dynamic parameters
    foreach (self::$routeMap[$method] as $route => $callback) {
        // Trim slashes
        $route = trim($route, '/');

        if (!$route) {
            continue;
        }

        // Convert route to regex pattern with dynamic parameters
        $routeRegex = preg_replace_callback('/\{(\w+)(:[^}]+)?\}/', function ($matches) {
            $paramName = $matches[1];
            $paramPattern = isset($matches[2]) ? $matches[2] : '\w+';
            return "(?P<$paramName>$paramPattern)";
        }, $route);

        // Add start and end delimiters to the pattern
        $routeRegex = "@^$routeRegex$@";

        // Test and match current route against $routeRegex
        if (preg_match($routeRegex, $url, $matches)) {
            $routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            
            $this->request->setParameters($routeParams);
            return $callback;
        }
    }

    return false;
}
