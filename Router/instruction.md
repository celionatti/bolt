# Example of Router

## Basic Route

``` $router->get('/home', function ($request, $response) {
    return $response->setContent('Welcome to the home page');
});

$router->post('/user', [UserController::class, 'store']);
```

## Named Routes

``` $router->get('/user/{id}', [UserController::class, 'show'])->name('user.show');
echo $router->url('user.show', ['id' => 1]);
```

## Route Group

``` $router->group(['prefix' => 'admin', 'middleware' => [AuthMiddleware::class]], function ($router) {
    $router->get('/dashboard', [AdminController::class, 'dashboard']);
    $router->get('/settings', [AdminController::class, 'settings']);
});
```

## Middleware

``` $router->get('/profile', [ProfileController::class, 'show'])->middleware(AuthMiddleware::class);
```
