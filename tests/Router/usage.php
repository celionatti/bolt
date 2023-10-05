<?php

public function group($options, $callback)
{
    // Extract options
    $middleware = $options['middleware'] ?? [];
    $prefix = $options['prefix'] ?? '';

    // Store the current middleware to restore after the group
    $originalMiddleware = $this->middleware;

    // Apply middleware for the group
    foreach ($middleware as $groupMiddleware) {
        $this->middleware[] = $groupMiddleware;
    }

    // Apply the prefix for the group
    $originalRoutes = self::$routeMap;
    self::$routeMap = [];

    // Execute the group callback
    call_user_func($callback, $this);

    // Restore the original middleware and routes
    $this->middleware = $originalMiddleware;
    self::$routeMap = $originalRoutes;
}

// Example usage:
$router->group(['middleware' => ['auth'], 'prefix' => 'admin'], function ($router) {
    $router->get('/dashboard', 'AdminController@dashboard');
    $router->get('/profile', 'AdminController@profile');
});
