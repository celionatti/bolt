<?php

declare(strict_types=1);

/**
 * ============================================
 * Bolt - BoltCLI =============================
 * ============================================
 */

namespace celionatti\Bolt\CLI;

use celionatti\Bolt\CLI\CommandInterface;
use celionatti\Bolt\CLI\Strike\MakeCommand;
use celionatti\Bolt\CLI\Strike\GreetCommand;
use celionatti\Bolt\CLI\Strike\SeederCommand;
use celionatti\Bolt\CLI\Strike\ServerCommand;
use celionatti\Bolt\CLI\Strike\GenerateCommand;
use celionatti\Bolt\CLI\Strike\MigrationCommand;
use celionatti\Bolt\CLI\Strike\SchedulerCommand;
use celionatti\Bolt\CLI\Strike\AuthenticationCommand;

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

    public function run($inputArgs = null): void
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
                $this->output("No Command Provided.", 2);
                $this->printHelp();
                exit;
            }
        }

        // Resolve aliases to actual command names
        $commandName = $this->aliases[$commandName] ?? $commandName;

        if (!isset($this->commands[$commandName])) {
            $this->output("Unknown Command: $commandName", 2);
            $this->printHelp();
            exit;
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
            exit;
        }
    }

    protected function runInteractiveMode()
    {
        $this->welcomeMessage();

        if (function_exists('readline_read_history')) {
            readline_read_history();
        }

        while (true) {
            $input = function_exists('readline') ? readline("Strike ⚡︎> ") : $this->simplePrompt("Strike ⚡︎> ");

            if ($input === false) {
                break; // User pressed Ctrl+D or an error occurred
            }

            $input = trim($input);

            if ($input !== '') {
                if (function_exists('readline_add_history')) {
                    readline_add_history($input);
                }
            }

            if ($input === 'exit' || $input === 'quit') {
                $confirm = function_exists('readline') ? readline("Are you sure you want to exit? (y/n): ") : $this->simplePrompt("Are you sure you want to exit? (y/n): ");
                if (strtolower($confirm) === 'y') {
                    break;
                } else {
                    continue; // Skip running any command if the exit was not confirmed
                }
            } elseif ($input === 'help') {
                $this->printHelp();
            } else {
                // Parse and execute the entered command
                $inputArgs = explode(' ', $input);
                $this->run($inputArgs);
            }
        }

        if (function_exists('readline_write_history')) {
            readline_write_history();
        }

        $this->output("Exiting interactive mode.");
        exit;
    }

    protected function simplePrompt(string $prompt): string
    {
        echo $prompt;
        return trim(fgets(STDIN));
    }

    protected function printHelp(): void
    {
        $this->output("Available commands:", 1);
        foreach ($this->commands as $name => $command) {
            $this->output("  \033[0;32m$name\033[0m: {$command['description']}", 1); // Green command names
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
                $output = "\033[0;32m{$output}"; // Green color for info
                break;
            case 'warning':
                $output = "\033[0;33m{$output}"; // Yellow color for warning
                break;
            case 'error':
                $output = "\033[0;31m{$output}"; // Red color for error
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

    public function activeCommands($strike)
    {
        $strike->registerCommand('greet', 'Greet the user', new GreetCommand());
        $strike->registerCommand('migration', 'Create a migration file, migrate, rollback, refresh, create', new MigrationCommand);
        $strike->registerCommand('make', 'Make Command is for creating complete Package, commands like Resource,', new MakeCommand);
        $strike->registerCommand('serve', 'Serve Bolt Framework with the PHP web server,', new ServerCommand);
        $strike->registerCommand('schedule', 'Create new Schedule file', new SchedulerCommand);
        $strike->registerCommand('generate', 'Generate Command is for , commands like app key,', new GenerateCommand);
        $strike->registerCommand('seeder', 'Create a seeder file, generate, drop', new SeederCommand);
        $strike->registerCommand('authentication', 'Create an authentication Resources. For the users Model, User Sessions, Migrations - users, login_attempts, and user_sessions. Also auth controller, view - login and signup page.', new AuthenticationCommand);

        // Register an alias
        $strike->registerAlias('add', 'calculate');
        $strike->registerAlias('seed', 'seeder');
        $strike->registerAlias('auth', 'authentication');
        $strike->registerAlias('gen', 'generate');
    }

    public function welcomeMessage()
    {
        $this->output("\033[0;36m********************************************\033[0m", 1);
        $this->output("\033[0;36m*                                          *\033[0m", 1);
        $this->output("\033[0;36m*" . str_pad("*** Strike ⚡︎ ***", 42, " ", STR_PAD_BOTH) . "*\033[0m", 1);
        $this->output("\033[0;36m*" . str_pad("Welcome to Strike CLI Interactive Mode.", 42, " ", STR_PAD_BOTH) . "*\033[0m", 1);
        $this->output("\033[0;36m*" . str_pad("Type 'help' to see a list of available", 42, " ", STR_PAD_BOTH) . "*\033[0m", 1);
        $this->output("\033[0;36m*" . str_pad("commands.", 42, " ", STR_PAD_BOTH) . "*\033[0m", 1);
        $this->output("\033[0;36m*" . str_pad("Type 'exit' to quit the interactive", 42, " ", STR_PAD_BOTH) . "*\033[0m", 1);
        $this->output("\033[0;36m*" . str_pad("mode.", 42, " ", STR_PAD_BOTH) . "*\033[0m", 1);
        $this->output("\033[0;36m*" . str_pad("For detailed command descriptions, type", 42, " ", STR_PAD_BOTH) . "*\033[0m", 1);
        $this->output("\033[0;36m*" . str_pad("'help [command]'.", 42, " ", STR_PAD_BOTH) . "*\033[0m", 1);
        $this->output("\033[0;36m*" . str_pad("You can also use aliases for quicker", 42, " ", STR_PAD_BOTH) . "*\033[0m", 1);
        $this->output("\033[0;36m*" . str_pad("access to commands.", 42, " ", STR_PAD_BOTH) . "*\033[0m", 1);
        $this->output("\033[0;36m*                                          *\033[0m", 1);
        $this->output("\033[0;36m********************************************\033[0m", 1);
    }
}
