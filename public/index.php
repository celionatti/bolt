<?php

declare(strict_types=1);

use Bolt\Bolt\Bolt;
use Dotenv\Dotenv;

/**
 * =======================================
 * Index Page ============================
 * =======================================
 */

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$bolt = new Bolt();

require $bolt->pathResolver->router_path("web.php");

$bolt->run();
