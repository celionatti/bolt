<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - View commands =============
 * ====================================
 */

namespace Bolt\Bolt\CLI\Strike;

use Bolt\Bolt\CLI\CommandInterface;

class ViewCommand implements CommandInterface
{
    public function execute(array $args)
    {
        // Logic for creating views, generating migrations, etc.
        $viewName = $args[0] ?? "main";
        var_dump($viewName);
        // ... (implementation specific to the 'view' command)
    }

    public function message(string $message, bool $die = false): void
    {
        echo "\n\r" . "[" . date("Y-m-d H:i:s") . "] - " . ucfirst($message) . PHP_EOL;
        
        if ($die) exit(1);
    }
}