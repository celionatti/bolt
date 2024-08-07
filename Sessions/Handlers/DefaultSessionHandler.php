<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - DefaultSessionHandler =====
 * ==================================
 */

namespace celionatti\Bolt\Sessions\Handlers;

use celionatti\Bolt\Sessions\SessionHandler;

class DefaultSessionHandler extends SessionHandler
{
    public function __construct()
    {
        $this->start();
    }
}