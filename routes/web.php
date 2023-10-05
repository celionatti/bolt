<?php

declare(strict_types=1);

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

$bolt->router->get('/', function () {
    return 'Hello, World!';
});

$bolt->router->get('/about', function () {
    return 'About Us';
});

$bolt->router->get('/post/{slug}', function ($slug) {
    return 'Post with Slug ' . $slug;
});