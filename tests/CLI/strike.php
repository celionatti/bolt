<?php

declare(strict_types=1);

/**
 * ============================================
 * Bolt - BoltCLI =============================
 * ============================================
 */

namespace Bolt\Bolt\CLI;

class BoltCLI_strike
{
    protected $commands = [];
    protected $aliases = [];
    protected $interactiveMode = false;
    protected $verbosity = 1; // 0: silent, 1: normal, 2: verbose

    public function registerCommand($name, $description, $callback)
    {
        $this->commands[$name] = [
            'description' => $description,
            'callback' => $callback,
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

    // public function parseArguments(array $args)
    // {
    //     $parsedArgs = ['command' => null, 'args' => [], 'options' => []];
    //     $currentOption = null;

    //     foreach ($args as $arg) {
    //         $this->dd($arg);
    //         // Check if the argument is a command or an option
    //         if ($arg && $arg[1] === '-') {
    //             // This is an option
    //             if ($currentOption !== null) {
    //                 // If an option is already in progress, store it
    //                 $parsedArgs['options'][$currentOption] = true;
    //             }
    //             $currentOption = ltrim($arg, '-');
    //         } elseif ($currentOption !== null) {
    //             // This argument belongs to the current option
    //             $parsedArgs['options'][$currentOption] = $arg;
    //             $currentOption = null;
    //         } elseif ($parsedArgs['command'] === null) {
    //             // This is the command
    //             $parsedArgs['command'] = $arg;
    //         } else {
    //             // This is a regular argument
    //             $parsedArgs['args'][] = $arg;
    //         }
    //     }

    //     // If an option is still in progress at the end, store it
    //     if ($currentOption !== null) {
    //         $parsedArgs['options'][$currentOption] = true;
    //     }

    //     return $parsedArgs;
    // }

    public function parseArguments(array $args)
    {
        $parsedArgs = ['command' => null, 'args' => [], 'options' => []];
        $currentOption = null;

        array_shift($args);
        foreach ($args as $arg) {
            // Check if the argument is an option
            if (strpos($arg, '-') === 0) {
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
                $this->printHelp();
                exit(1);
            }
        }

        // Resolve aliases to actual command names
        $commandName = $this->aliases[$commandName] ?? $commandName;

        if (!isset($this->commands[$commandName])) {
            $this->printHelp();
            exit(1);
        }

        $command = $this->commands[$commandName];
        $commandArgs = $parsedArgs['args'] ?? [];

        // Execute the command's callback function
        $callback = $command['callback'];
        if (is_callable($callback)) {
            call_user_func($callback, $commandArgs);
        } else {
            $this->output("Invalid callback for the command: $commandName", 2);
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
            $this->output("$name: {$command['description']}");
        }
    }

    protected function output($message, $verbosityLevel = 1)
    {
        if ($verbosityLevel <= $this->verbosity) {
            echo "$message\n";
        }
    }

    protected function dd($data)
    {
        var_dump($data);
        die;
    }
}
