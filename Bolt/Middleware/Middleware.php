<?php

declare(strict_types=1);

/**
 * ==============================================
 * Bolt - Middleware ============================
 * ==============================================
 */

namespace Bolt\Bolt\Middleware;


abstract class Middleware
{
    abstract public function execute();
}
