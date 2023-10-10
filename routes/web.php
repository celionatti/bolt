<?php

declare(strict_types=1);

use Bolt\controllers\SiteController;
use Bolt\controllers\UserController;

/** @var TYPE_NAME $bolt */

/**
 * ========================================
 * Bolt - Router Usage ====================
 * ========================================
 */

// $bolt->router->get("/user", function() {
//     echo "User function routing...";
// });

// // Create a SubRouter for nested routing
// $subRouter = $bolt->router->BoltRouter();

// // Define a route using the SubRouter
// $subRouter->get('/admin', [UserController::class, 'admin']);

$bolt->router->get("/", [SiteController::class, "welcome"]);
$bolt->router->get("/users", [SiteController::class, "users"]);