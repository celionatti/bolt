<?php

declare(strict_types=1);

use Bolt\controllers\UserController;

/** @var TYPE_NAME $bolt */

/**
 * ========================================
 * Bolt - Router Usage ====================
 * ========================================
 */

// $bolt->router->get('/', function () {
//     return 'Hello, World!';
// });

// $bolt->router->get('/about', function () {
//     return 'About Us';
// });

// $bolt->router->get('/user/{id}', function ($id) {
//     return 'User Profile for ID ' . $id;
// });

// $bolt->router->get('/post/{slug}', function ($slug) {
//     return 'Post with Slug ' . $slug;
// });


// $bolt->router->group(['middleware' => 'auth', 'prefix' => '/admin'], function ($router) {
//     $router->get('/dashboard', function () {
//         return 'Admin Dashboard';
//     });
// });

// $bolt->router::get('/user', UserController::class, 'index');
// $bolt->router::get('/user/{id}', UserController::class, 'user');

// $bolt->router->get('/about', function () {
//     return 'About Us';
// });

// $bolt->router->get('/post/{slug}', function ($slug) {
//     return 'Post with Slug ' . $slug;
// });

$bolt->router->addRoute('GET', '/', 'Bolt\controllers\UserController@index', ['auth']);
$bolt->router->addRoute('GET', '/about', 'UserController@about');

$bolt->router->addMiddleware('auth', function () {
    // Implement your authentication logic here
    echo "Auth Middlware \n";
});

$bolt->router->addMiddleware('logger', function () {
    // Implement logging logic here
    echo "Logger Middlware \n";
});

$bolt->router->group('/admin', ['auth'], function ($router) {
    $router->addRoute('GET', '/dashboard', 'AdminController@dashboard', ['logger']);
    $router->addRoute('GET', '/users', 'AdminController@users', ['logger']);
});