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
use celionatti\Bolt\CLI\Strike\ServerCommand;
use celionatti\Bolt\CLI\Strike\DatabaseCommand;
use celionatti\Bolt\CLI\Strike\GenerateCommand;
use celionatti\Bolt\CLI\Strike\MigrationCommand;
use celionatti\Bolt\CLI\Strike\SchedulerCommand;
use celionatti\Bolt\CLI\Strike\AuthenticationCommand;
use RuntimeException;

class BoltCLI
{
    private const COLOR_PRIMARY = "\033[1;36m";
    private const COLOR_SECONDARY = "\033[0;35m";
    private const COLOR_SUCCESS = "\033[1;32m";
    private const COLOR_ERROR = "\033[1;31m";
    private const COLOR_RESET = "\033[0m";

    private array $commands = [];
    private array $aliases = [];
    private bool $interactive = false;
    private int $verbosity = 1;
    private CliActions $cli;

    public function __construct()
    {
        $this->cli = new CliActions();
        $this->registerCoreCommands();
    }

    public function registerCommand(string $name, string $description, CommandInterface $command): void
    {
        $this->commands[$name] = [
            'description' => $description,
            'instance' => $command,
            'help' => $command->getHelp()
        ];
    }

    public function registerAlias(string $alias, string $commandName): void
    {
        if (!isset($this->commands[$commandName])) {
            throw new RuntimeException("Cannot alias unknown command: {$commandName}");
        }
        $this->aliases[$alias] = $commandName;
    }

    public function run(array $args = []): void
    {
        try {
            $args = empty($args) ? $_SERVER['argv'] : $args;
            $parsed = $this->parseArguments($args);

            $this->handleGlobalOptions($parsed['options']);

            if (empty($parsed['command']) && $this->interactive) {
                $this->startInteractiveSession();
                return;
            }

            $this->executeCommand($parsed);
        } catch (RuntimeException $e) {
            $this->cli->message($e->getMessage(), 'error', true);
        }
    }

    private function parseArguments(array $args): array
    {
        array_shift($args); // Remove script name
        $result = ['command' => null, 'args' => [], 'options' => []];

        while (!empty($args)) {
            $arg = array_shift($args);

            if ($arg === '--') {
                break; // Stop parsing options
            }

            if (str_starts_with($arg, '--')) {
                $this->parseLongOption($arg, $args, $result);
            } elseif (str_starts_with($arg, '-')) {
                $this->parseShortOption($arg, $args, $result);
            } elseif ($result['command'] === null) {
                $result['command'] = $arg;
            } else {
                $result['args'][] = $arg;
            }
        }

        return $result;
    }

    private function executeCommand(array $parsed): void
    {
        $commandName = $this->resolveCommandName($parsed['command']);

        if (!isset($this->commands[$commandName])) {
            $this->showError("Unknown command: {$parsed['command']}");
            $this->showAvailableCommands();
            return;
        }

        $command = $this->commands[$commandName]['instance'];
        $command->execute(
            $parsed['args'],
            $parsed['options']
        );
    }

    private function resolveCommandName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }
        return $this->aliases[$name] ?? $name;
    }

    private function startInteractiveSession(): void
    {
        $this->showWelcome();
        $this->cli->message("Interactive mode activated", 'success');

        while (true) {
            $input = $this->cli->prompt("bolt", "Enter command");

            if ($input === 'exit' || $input === 'quit') {
                break;
            }

            if ($input === 'help') {
                $this->showInteractiveHelp();
                continue;
            }

            $this->executeCommand(
                $this->parseArguments(explode(' ', $input))
            );
        }

        $this->cli->message("Exiting interactive mode", 'info');
    }

    private function registerCoreCommands(): void
    {
        $commands = [
            'migration' => [
                'class' => MigrationCommand::class,
                'desc' => 'Database migration management. migrate, rollback, refresh, create.'
            ],
            'make' => [
                'class' => MakeCommand::class,
                'desc' => 'Code generation commands'
            ],
            'serve' => [
                'class' => ServerCommand::class,
                'desc' => 'Start development server'
            ],
            'database' => [
                'class' => DatabaseCommand::class,
                'desc' => 'Start database commands'
            ],
            'generate' => [
                'class' => GenerateCommand::class,
                'desc' => 'Code generation commands - Keys'
            ],
            // ... other commands
        ];

        foreach ($commands as $name => $config) {
            $this->registerCommand(
                $name,
                $config['desc'],
                new $config['class']()
            );
        }

        $this->registerAliases();
    }

    private function registerAliases(): void
    {
        $aliases = [
            'mk' => 'make',
            'db' => 'database',
            'gen' => 'generate',
            'auth' => 'authentication'
        ];

        foreach ($aliases as $alias => $command) {
            $this->registerAlias($alias, $command);
        }
    }

    private function showWelcome(): void
    {
        $this->cli->message(
            "PHPStrike(Bolt) Framework CLI v2",
            'info'
        );
        $this->cli->message(
            "Type 'help' for available commands",
            'secondary'
        );
    }

    private function showInteractiveHelp(): void
    {
        $this->cli->message("Available Commands:", 'info');
        foreach ($this->commands as $name => $cmd) {
            $this->cli->output(
                sprintf("%-15s %s", $name, $cmd['description'])
            );
        }
        $this->cli->output("\nUse 'help <command>' for detailed usage");
    }

    private function handleGlobalOptions(array $options): void
    {
        $this->interactive = $options['interactive'] ?? false;
        $this->verbosity = (int)($options['verbose'] ?? 1);

        if (isset($options['quiet'])) {
            $this->verbosity = 0;
        }
    }

    private function showError(string $message): void
    {
        $this->cli->message($message, 'error');
    }

    private function showAvailableCommands(): void
    {
        $this->cli->message("Available commands:", 'info');
        foreach ($this->commands as $name => $command) {
            $this->cli->output(
                sprintf("  %s%-20s%s %s",
                    self::COLOR_PRIMARY,
                    $name,
                    self::COLOR_RESET,
                    $command['description']
                )
            );
        }
    }
}
