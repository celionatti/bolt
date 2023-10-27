<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Server commands =============
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CommandInterface;

class ServerCommand implements CommandInterface
{
    public $basePath;

    public function __construct()
    {
        // Get the current file's directory
        $currentDirectory = __DIR__;

        // Navigate up the directory tree until you reach the project's root
        while (!file_exists($currentDirectory . '/composer.json')) {
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
        // Check if the required arguments are provided
        if (count($args["args"]) < 2) {
            $this->message("Strike Usage: serve <host> <port>", true, true, 'warning');
        }

        $host = $args["args"][0];
        $port = $args["args"][1];

        // Start the PHP web server
        $this->startServer($host, $port);
    }

    private function startServer($host, $port)
    {
        // Change the working directory to the "public" folder
        chdir($this->basePath . '/public');

        $command = "php -S $host:$port";

        // Display a message indicating that the server is running
        $this->message("Bolt Framework PHP server is running on $host:$port. Press Ctrl+C to stop.", false, true, 'info');

        // Use the `exec` function to run the PHP web server command
        exec($command);

        // Keep the script running to allow the server to continue serving
        while (true) {
            sleep(1);
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

        echo $output;

        if ($die) {
            die();
        }
    }
}
