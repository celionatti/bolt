<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Server commands =============
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CliActions;
use celionatti\Bolt\CLI\CommandInterface;

class ServerCommand extends CliActions implements CommandInterface
{
    public function __construct()
    {
        $this->configure();
    }

    public function execute(array $args)
    {
        if (empty($args) || empty($args["args"])) {
            $this->listAvailableActions();
            return;
        }

        $action = $args["args"][0] ?? null;

        switch ($action) {
            case 'start':
                $this->startServer();
                break;
            case 'stop':
                $this->stopServer();
                break;
            case 'restart':
                $this->restartServer();
                break;
            default:
                $this->message("Unknown Command. Usage: server <action> (start/stop/restart)", true, true, 'warning');
        }
    }

    private function startServer()
    {
        $port = $this->prompt("Enter port number (default: 8000):");
        $port = !empty($port) ? (int)$port : 8000;

        $this->message("Starting PHP development server on http://localhost:$port", false, true, 'start');

        // Verify if the public directory exists
        $publicDir = "{$this->basePath}/public";
        if (!is_dir($publicDir)) {
            $this->message("public directory does not exist in the project root.", true, true, 'error');
            return;
        }

        // Change the working directory to the "public" folder
        chdir($publicDir);
        $cmd = "php -S localhost:$port";

        // Add debug message for the command
        $this->message("Executing command: $cmd", false, true, 'execute');

        // Execute server command
        $this->executeCommandInBackground($cmd, $port);
    }

    private function stopServer()
    {
        $this->message("Stopping PHP development server", false, true, 'info');

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows-specific command to stop the server
            exec("taskkill /F /IM php.exe");
        } else {
            // Unix-based command to stop the server
            exec("pkill -f 'php -S'");
        }

        $this->message("Server stopped successfully", false, true, 'close');
    }

    private function restartServer()
    {
        $this->stopServer();
        $this->startServer();
    }

    private function executeCommandInBackground(string $cmd, $port)
    {
        $this->message("Server Started on http://localhost:$port . Press Ctrl+C to stop.", false, true, 'running');
        //Execute command in background
        exec($cmd);
    }

    private function listAvailableActions()
    {
        $this->message("Available Server Commands:", false, false, 'info');
        $this->output("  \033[0;37mstart\033[0m: \033[0;36mStart the PHP development server\033[0m", 1);
        $this->output("  \033[0;37mstop\033[0m: \033[0;36mStop the PHP development server\033[0m", 1);
        $this->output("  \033[0;37mrestart\033[0m: \033[0;36mRestart the PHP development server\033[0m", 1);
    }
}
