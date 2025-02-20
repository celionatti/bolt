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
    private const COLORS = [
        'primary' => "\033[1;36m",
        'secondary' => "\033[0;35m",
        'success' => "\033[1;32m",
        'error' => "\033[1;31m",
        'reset' => "\033[0m"
    ];

    private array $commands = [];
    private array $aliases = [];
    private readonly CliActions $cli;
    private array $config = [
        'interactive' => false,
        'verbosity' => 1
    ];

    public function __construct()
    {
        $this->cli = new CliActions();
        $this->registerCoreCommands();
    }

    private function registerCoreCommands(): void
    {
        $coreCommands = [
            'migration' => [
                'class' => MigrationCommand::class,
                'desc' => 'Database migration management'
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
                'desc' => 'Database management commands'
            ],
            'generate' => [
                'class' => GenerateCommand::class,
                'desc' => 'Generate application keys'
            ]
        ];

        foreach ($coreCommands as $name => $config) {
            $this->registerCommand($name, $config['desc'], new $config['class']());
        }

        $this->registerDefaultAliases();
    }

    private function registerDefaultAliases(): void
    {
        $defaultAliases = [
            'mk' => 'make',
            'db' => 'database',
            'gen' => 'generate',
            'mg' => 'migration'
        ];

        foreach ($defaultAliases as $alias => $command) {
            $this->registerAlias($alias, $command);
        }
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
            $commandData = $this->parseCommandLine($args);

            $this->config = array_merge($this->config, $commandData['options']);

            if (empty($commandData['command']) && $this->config['interactive']) {
                $this->runInteractiveMode();
                return;
            }

            $this->executeCommand($commandData);
        } catch (RuntimeException $e) {
            $this->cli->message($e->getMessage(), 'error', true);
        }
    }

    private function parseCommandLine(array $args): array
    {
        array_shift($args); // Remove script name
        $parsed = ['command' => null, 'args' => [], 'options' => []];

        foreach ($args as $arg) {
            if (str_starts_with($arg, '--')) {
                $option = substr($arg, 2);
                $parsed['options'][$option] = true;
            } elseif (str_starts_with($arg, '-')) {
                $chars = str_split(substr($arg, 1));
                foreach ($chars as $char) {
                    $parsed['options'][$char] = true;
                }
            } elseif ($parsed['command'] === null) {
                $parsed['command'] = $arg;
            } else {
                $parsed['args'][] = $arg;
            }
        }

        return $parsed;
    }

    private function executeCommand(array $commandData): void
    {
        $commandName = $this->resolveCommandName($commandData['command']);

        if (!isset($this->commands[$commandName])) {
            $this->showError("Unknown command: {$commandData['command']}");
            $this->showAvailableCommands();
            return;
        }

        $command = $this->commands[$commandName]['instance'];
        $command->execute($commandData['args'], $commandData['options']);
    }

    private function resolveCommandName(?string $name): ?string
    {
        return $name ? ($this->aliases[$name] ?? $name) : null;
    }

    private function runInteractiveMode(): void
    {
        $this->showWelcome();
        $this->cli->message("Interactive mode activated", 'success');

        while (true) {
            $input = $this->cli->prompt("bolt");

            if (in_array($input, ['exit', 'quit'])) {
                break;
            }

            if ($input === 'help') {
                $this->showHelp();
                continue;
            }

            $this->executeCommand($this->parseCommandLine(explode(' ', $input)));
        }

        $this->cli->message("Exiting interactive mode", 'info');
    }

    private function showWelcome(): void
    {
        $this->cli->message("PHPStrike(Bolt) Framework CLI v2", 'info');
        $this->cli->message("Type 'help' for available commands", 'secondary');
    }

    private function showHelp(): void
    {
        $this->cli->message("Available Commands:", 'info');
        foreach ($this->commands as $name => $cmd) {
            $this->cli->output(sprintf(
                "%s%-15s%s %s",
                self::COLORS['primary'],
                $name,
                self::COLORS['reset'],
                $cmd['description']
            ));
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
            $this->cli->output(sprintf(
                "  %s%-20s%s %s",
                self::COLORS['primary'],
                $name,
                self::COLORS['reset'],
                $command['description']
            ));
        }
    }
}
