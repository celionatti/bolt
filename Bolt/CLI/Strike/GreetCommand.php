<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Greet commands ============
 * ====================================
 */

namespace Bolt\Bolt\CLI\Strike;

use Bolt\Bolt\CLI\CommandInterface;

class GreetCommand implements CommandInterface
{
    public function execute(array $args)
    {
        $name = $args["args"][0] ?? 'Guest';
        echo "Hello, $name!\n";
    }
}