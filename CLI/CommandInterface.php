<?php

declare(strict_types=1);

/**
 * ============================================
 * Bolt - BoltCommands ========================
 * ============================================
 */

namespace celionatti\Bolt\CLI;


interface CommandInterface
{
    public function execute(array $args);
}