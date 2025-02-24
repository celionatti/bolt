<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Migration commands ==========
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CliActions;
use celionatti\Bolt\CLI\CommandInterface;
use RuntimeException;

class MigrationCommand extends CliActions implements CommandInterface
{
    private const MIGRATION_ACTIONS = [
        'migrate' => [
            'description' => 'Run all outstanding migrations',
            'method' => 'handleMigrate'
        ],
        'rollback' => [
            'description' => 'Rollback the last migration',
            'method' => 'handleRollback'
        ],
        'refresh' => [
            'description' => 'Rollback all migrations and re-run them',
            'method' => 'handleRefresh'
        ],
        'create' => [
            'description' => 'Create a new migration class',
            'method' => 'handleCreateMigration'
        ]
    ];

    public function execute(array $args, array $options = []): void
    {
        $action = $args[0] ?? null;

        if (!$action || !isset(self::MIGRATION_ACTIONS[$action])) {
            $this->showAvailableActions();
            return;
        }

        $methodName = self::MIGRATION_ACTIONS[$action]['method'];
        if (method_exists($this, $methodName)) {
            $this->$methodName($options);
        } else {
            $this->message("Action handler not implemented: {$action}", 'error');
        }
    }

    public function getHelp(): string
    {
        return implode(PHP_EOL, [
            "Usage: migration <action> [options]",
            "Available actions:",
            ...array_map(
                fn($action, $config) => "  {$action}: {$config['description']}",
                array_keys(self::MIGRATION_ACTIONS),
                self::MIGRATION_ACTIONS
            ),
            "Options:",
            "  --force    Overwrite existing files (for create action)",
        ]);
    }

    private function handleMigrate(array $options): void
    {
        $migrationDir = $this->getMigrationPath();
        $migrationFiles = $this->getMigrationFiles($migrationDir);

        foreach ($migrationFiles as $file) {
            $this->runMigration($file, 'up');
        }
    }

    private function handleRollback(array $options): void
    {
        $migrationDir = $this->getMigrationPath();
        $migrationFiles = $this->getMigrationFiles($migrationDir);

        if (!empty($migrationFiles)) {
            $this->runMigration(end($migrationFiles), 'down');
        }
    }

    private function handleRefresh(array $options): void
    {
        $migrationDir = $this->getMigrationPath();
        $migrationFiles = $this->getMigrationFiles($migrationDir);

        // Rollback all
        foreach (array_reverse($migrationFiles) as $file) {
            $this->runMigration($file, 'down');
        }

        // Migrate all
        foreach ($migrationFiles as $file) {
            $this->runMigration($file, 'up');
        }
    }

    private function handleCreateMigration(array $options): void
    {
        $name = $this->promptMigrationName();
        $className = 'Create' . $this->pascalCase($name) . 'Table';
        $fileName = date('Y_m_d_His') . "_create_{$name}_table.php";
        $path = "database/migrations/{$fileName}";

        $this->createFromTemplate(
            'migrations/migration',
            $path,
            [
                'CLASSNAME' => $className,
                'TABLENAME' => strtolower($name)
            ],
            $options
        );
    }

    private function runMigration(string $filePath, string $method): void
    {
        try {
            $migrationClass = include $filePath;

            if ($migrationClass instanceof \Bolt\CLI\Database\Migration) {
                if (method_exists($migrationClass, $method)) {
                    $migrationClass->$method();
                    $this->message("Executed {$method}: " . basename($filePath), 'success');
                } else {
                    $this->message("Missing {$method} method in: " . basename($filePath), 'warning');
                }
            } else {
                $this->message("Invalid migration class: " . basename($filePath), 'error');
            }
        } catch (\Throwable $e) {
            $this->message("Error processing " . basename($filePath) . ": " . $e->getMessage(), 'error');
        }
    }

    private function showAvailableActions(): void
    {
        $this->message("Available migration actions:", 'info');
        foreach (self::MIGRATION_ACTIONS as $action => $config) {
            $this->output(sprintf(
                "  %s%-12s%s %s",
                self::COLORS['primary'],
                $action,
                self::COLORS['reset'],
                $config['description']
            ));
        }
    }

    private function getMigrationPath(): string
    {
        $path = $this->basePath . DIRECTORY_SEPARATOR . 'database/migrations';
        $this->ensureDirectoryExists($path);
        return $path;
    }

    private function getMigrationFiles(string $migrationDir): array
    {
        $files = glob($migrationDir . DIRECTORY_SEPARATOR . '*.php');
        return is_array($files) ? $files : [];
    }

    private function promptMigrationName(): string
    {
        while (true) {
            $name = $this->prompt("Enter migration name (e.g., users)");
            $name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $name);

            if (!empty($name)) {
                return $name;
            }

            $this->message(
                "Invalid name. Use only letters, numbers and underscores",
                'warning'
            );
        }
    }

    protected function pascalCase(string $input): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $input)));
    }

    private function createFromTemplate(string $template, string $path, array $replacements, array $options): void
    {
        $fullPath = $this->basePath . DIRECTORY_SEPARATOR . ltrim($path, '/');
        $templatePath = __DIR__ . "/samples/{$template}.php";

        if (!file_exists($templatePath)) {
            throw new RuntimeException("Template not found: {$template}");
        }

        if (file_exists($fullPath) && !($options['force'] ?? false)) {
            $this->message("File already exists: {$path}", 'warning');
            return;
        }

        $content = strtr(
            file_get_contents($templatePath),
            array_map(
                fn($value) => (string)$value,
                array_combine(
                    array_map(fn($key) => "{{{$key}}}", array_keys($replacements)),
                    $replacements
                )
            )
        );

        $this->ensureDirectoryExists(dirname($fullPath));

        if (file_put_contents($fullPath, $content) === false) {
            throw new RuntimeException("Failed to create file: {$path}");
        }

        $this->message("Created successfully: {$path}", 'success');
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            throw new RuntimeException("Failed to create directory: {$path}");
        }
    }
}
