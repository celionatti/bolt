<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Make commands =============
 * ====================================
 */

namespace Bolt\Bolt\CLI\Strike;

use Bolt\Bolt\CLI\CommandInterface;

class MakeCommand implements CommandInterface
{
    public function execute(array $args)
    {
        // Logic for creating views, generating migrations, etc.
        $viewName = $args[0] ?? "main";
        var_dump($viewName);
        // ... (implementation specific to the 'view' command)
    }

    public function message(string $message, bool $die = false, bool $timestamp = true, string $level = 'info'): void
    {
        $output = '';

        if ($timestamp) {
            $output .= "[" . date("Y-m-d H:i:s") . "] - ";
        }

        $output .= ucfirst($message) . PHP_EOL;

        switch ($level) {
            case 'info':
                $output = "\033[0;32m" . $output; // Green color for info
                break;
            case 'warning':
                $output = "\033[0;33m" . $output; // Yellow color for warning
                break;
            case 'error':
                $output = "\033[0;31m" . $output; // Red color for error
                break;
            default:
                break;
        }

        $output .= "\033[0m"; // Reset color

        echo $output;

        if ($die) {
            die();
        }
    }
}