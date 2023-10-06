<?php

declare(strict_types=1);

/**
 * ============================================
 * Bolt - BoltCommands ========================
 * ============================================
 */

namespace Bolt\Bolt\CLI;


abstract class StrikeCommands
{
    public abstract function execute($args, $options);
}