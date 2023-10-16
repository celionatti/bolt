<?php

declare(strict_types=1);

use Bolt\controllers\AuthController;
use Bolt\controllers\SiteController;

/** @var TYPE_NAME $bolt */

/**
 * ========================================
 * Bolt - Router Usage ====================
 * ========================================
 */

// $bolt->router->get("/user", function() {
//     echo "User function routing...";
// });

// $bolt->router->get("/", [SiteController::class, "welcome"]);
// $bolt->router->get("/testing", "SiteController@testing");

$bolt->router->get("/", [SiteController::class, "welcome"]);
$bolt->router->get("/login", [AuthController::class, "login"]);
$bolt->router->get("/signup", [AuthController::class, "signup"]);
$bolt->router->post("/signup", [AuthController::class, "signup"]);
