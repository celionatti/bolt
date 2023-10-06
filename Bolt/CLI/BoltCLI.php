<?php

declare(strict_types=1);

/**
 * ============================================
 * Bolt - BoltCLI =============================
 * ============================================
 */

namespace Bolt\Bolt\CLI;

class BoltCLI
{
    protected $commands = [];

    public function registerCommand($name, $description, $callback)
    {
        $this->commands[$name] = [
            'description' => $description,
            'callback' => $callback,
        ];
    }

    public function parseArguments()
    {
        global $argv;

        // Remove the script name from the arguments
        array_shift($argv);

        if (count($argv) < 1) {
            $this->printHelp();
            exit(1);
        }

        $commandName = array_shift($argv);
        if (!isset($this->commands[$commandName])) {
            $this->printHelp();
            exit(1);
        }

        $command = $this->commands[$commandName];
        $args = [];

        foreach ($argv as $arg) {
            if (strpos($arg, '=') !== false) {
                list($key, $value) = explode('=', $arg, 2);
                $args[$key] = $value;
            }
        }

        return [$command, $args];
    }

    public function run()
    {
        list($command, $args) = $this->parseArguments();
        $callback = $command['callback'];

        if (is_callable($callback)) {
            call_user_func($callback, $args);
        } else {
            echo "Invalid callback for the command.\n";
            exit(1);
        }
    }

    protected function printHelp()
    {
        echo "Available commands:\n";
        foreach ($this->commands as $name => $command) {
            echo "$name: {$command['description']}\n";
        }
    }
}
