<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Make commands =============
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CommandInterface;

class MakeCommand implements CommandInterface
{
    public $basePath;

    private const ACTION_CREATE = 'create';

    public function __construct()
    {
        // Get the current file's directory
        $currentDirectory = __DIR__;

        // Navigate up the directory tree until you reach the project's root
        while (!file_exists($currentDirectory . '/vendor')) {
            // Go up one level
            $currentDirectory = dirname($currentDirectory);

            // Check if you have reached the filesystem root (to prevent infinite loop)
            if ($currentDirectory === '/') {
                $this->message("Error: Project root not found.", true, true, "error");
            }
        }

        $this->basePath = $currentDirectory;
    }
    
    public function execute(array $args)
    {
        // Logic for creating views, generating migrations, etc.
        $action = $args[0];

        $this->callAction($action);
    }

    private function callAction($action)
    {
        // Check for the action type.
        switch ($action) {
            case self::ACTION_CREATE:
                // $this->createController($filename, $crudMethod);
                break;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
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

        echo $output . PHP_EOL;

        if ($die) {
            die();
        }
    }
}