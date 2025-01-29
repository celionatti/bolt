# API ROUTING

## API Routing

// Create versioned API group
$router->apiGroup('v1', ['middleware' => 'throttle'], function ($router) {
    $router->get('/users', [UserController::class, 'index'])->name('users.index');
});

// Use default API version
$router->api([], function ($router) {
    $router->post('/posts', [PostController::class, 'store']);
});

## Automatic config / Flexible mode

// Set default API version
$router->setDefaultApiVersion('v2');

// Nested API groups
$router->apiGroup('v1', ['prefix' => 'admin'], function ($router) {
    // Creates /api/v1/admin/users
    $router->get('/users', 'AdminController@users')->name('admin.users');
});


### Examples

$router->apiGroup('v1', [], function ($router) {
    $router->get('/profile/{:id}', [ProfileController::class, 'show'])
        ->middleware('auth:api')
        ->name('profile.show')
        ->where('id', '\d+');
});

$router->apiGroup('v2', [
    'prefix' => 'mobile',
    'middleware' => ['cors', 'throttle']
], function ($router) {
    $router->post('/login', [AuthController::class, 'mobileLogin'])->name('auth.login');
});

$router->setDefaultApiVersion('v3');
$router->api([], function ($router) {
    $router->put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});