<?php

declare(strict_types=1);

/**
 * ==================================
 * Bolt - SessionHandler ============
 * ==================================
 */

namespace celionatti\Bolt\Sessions\Handlers;

use celionatti\Bolt\Sessions\SessionHandler;

class FileSessionHandler extends SessionHandler
{
    public function __construct($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        session_save_path($path);
        $this->start();
    }
}