<?php

declare(strict_types=1);

/**
 * ============================================
 * Bolt - BoltCLI =============================
 * ============================================
 */

namespace celionatti\Bolt\CLI;

class BoltCLI
{
    protected $commands = [];
    protected $aliases = [];
    protected $interactiveMode = false;
    protected $verbosity = 1; // 0: silent, 1: normal, 2: verbose

    public function registerCommand($name, $description, CommandInterface $commandInstance)
    {
        $this->commands[$name] = [
            'description' => $description,
            'command' => $commandInstance,
        ];
    }


    public function registerAlias($alias, $commandName)
    {
        $this->aliases[$alias] = $commandName;
    }

    public function setInteractiveMode($enabled)
    {
        $this->interactiveMode = $enabled;
    }

    public function setVerbosity($level)
    {
        $this->verbosity = $level;
    }

    public function parseArguments(array $args)
    {
        $parsedArgs = ['command' => null, 'args' => [], 'options' => []];
        $currentOption = null;

        array_shift($args);
        foreach ($args as $arg) {
            // Check if the argument is a command or an option
            if ($arg && $arg[0] === '-') {
                // This is an option
                if ($currentOption !== null) {
                    // If an option is already in progress, store it
                    $parsedArgs['options'][$currentOption] = true;
                }
                $currentOption = ltrim($arg, '-');
            } elseif ($currentOption !== null) {
                // This argument belongs to the current option
                $parsedArgs['options'][$currentOption] = $arg;
                $currentOption = null;
            } elseif ($parsedArgs['command'] === null) {
                // This is the command
                $parsedArgs['command'] = $arg;
            } else {
                // This is a regular argument
                $parsedArgs['args'][] = $arg;
            }
        }

        // If an option is still in progress at the end, store it
        if ($currentOption !== null) {
            $parsedArgs['options'][$currentOption] = true;
        }

        return $parsedArgs;
    }

    public function run($inputArgs = null)
    {
        if ($inputArgs !== null && is_array($inputArgs)) {
            $args = $inputArgs;
        } else {
            $args = $_SERVER['argv'];
        }

        // Advanced argument parsing
        $parsedArgs = $this->parseArguments($args);

        // Determine the command to execute
        $commandName = $parsedArgs['command'] ?? null;

        if (!$commandName) {
            if ($this->interactiveMode) {
                // Enter interactive mode
                $this->runInteractiveMode();
            } else {
                $this->output("No command provided.", 2);
                $this->printHelp();
                exit(1);
            }
        }

        // Resolve aliases to actual command names
        $commandName = $this->aliases[$commandName] ?? $commandName;

        if (!isset($this->commands[$commandName])) {
            $this->output("Unknown command: $commandName", 2);
            $this->printHelp();
            exit(1);
        }

        $commandInfo = $this->commands[$commandName];
        $commandInstance = $commandInfo['command'];
        $commandArgs = $parsedArgs['args'] ?? [];

        if ($commandInstance instanceof CommandInterface) {
            // Execute the command's execute method
            $commandInstance->execute($parsedArgs);
            // $commandInstance->execute($commandArgs);
        } else {
            $this->output("Invalid command instance: $commandName", 2);
            exit(1);
        }
    }



    protected function runInteractiveMode()
    {
        $this->output("Interactive mode. Type 'help' for a list of available commands or 'exit' to quit.");

        while (true) {
            $input = readline("> ");
            if ($input === false) {
                break; // User pressed Ctrl+D or an error occurred
            }

            $input = trim($input);

            if ($input === 'exit' || $input === 'quit') {
                break; // Exit interactive mode
            } elseif ($input === 'help') {
                $this->printHelp();
            } else {
                // Parse and execute the entered command
                $inputArgs = explode(' ', $input);
                $this->run($inputArgs);
            }
        }

        $this->output("Exiting interactive mode.");
    }


    protected function printHelp()
    {
        $this->output("Available commands:");
        foreach ($this->commands as $name => $command) {
            $this->output("*** " . "$name: {$command['description']}");
        }
    }

    protected function output($message, $verbosityLevel = 1)
    {
        if ($verbosityLevel <= $this->verbosity) {
            $this->message($message, false, false);
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
