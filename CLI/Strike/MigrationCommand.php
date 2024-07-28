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

class MigrationCommand extends CliActions implements CommandInterface
{
    private const MIGRATE = 'migrate';
    private const ROLLBACK = 'rollback';
    private const REFRESH = 'refresh';
    private const CREATE = 'create';

    private const ACTIONS = [
        self::MIGRATE => 'Run all outstanding migrations',
        self::ROLLBACK => 'Rollback the last migration',
        self::REFRESH => 'Rollback all migrations and re-run them',
        self::CREATE => 'Create a new migration class',
        // Add other actions and their descriptions here
    ];

    public function __construct()
    {
        $this->configure();
    }

    public function execute(array $args)
    {
        // Check if no action is provided
        if (empty($args) || empty($args["args"])) {
            $this->listAvailableActions();
            return;
        }

        $action = $args["args"][0] ?? null;

        if ($action === null) {
            $this->listAvailableActions();
            return;
        }

        $this->callAction($action);
    }

    private function callAction($action)
    {
        // Check for the action type.
        switch ($action) {
            case self::MIGRATE:
                $this->migrate();
                return;
            case self::ROLLBACK:
                $this->rollback();
                return;
            case self::REFRESH:
                $this->refresh();
                return;
            case self::CREATE:
                $this->makeMigration();
                return;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
                return;
        }
    }

    private function migrate()
    {
        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            $this->message("Migrations directory not found.", true, true, "error");
            return;
        }

        $migrationFiles = glob($migrationDir . '*.php');

        if (empty($migrationFiles)) {
            $this->message("No migrations found.", false, true, "info");
            return;
        }

        foreach ($migrationFiles as $file) {
            $migrationClass = include $file;

            if (method_exists($migrationClass, 'up')) {
                $migrationClass->up();
                $this->message("Migrated: " . basename($file), false, true, "info");
            } else {
                $this->message("Migration class does not have an up method: " . basename($file), true, true, "error");
            }
        }
    }

    private function rollback()
    {
        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            $this->message("Migrations directory not found.", true, true, "error");
            return;
        }

        $migrationFiles = glob($migrationDir . '*.php');

        if (empty($migrationFiles)) {
            $this->message("No migrations found.", false, true, "info");
            return;
        }

        $latestMigrationFile = end($migrationFiles);
        $migrationClass = include $latestMigrationFile;

        if (method_exists($migrationClass, 'down')) {
            $migrationClass->down();
            $this->message("Rolled back: " . basename($latestMigrationFile), false, true, "info");
        } else {
            $this->message("Migration class does not have a down method: " . basename($latestMigrationFile), true, true, "error");
        }
    }

    private function refresh()
    {
        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            $this->message("Migrations directory not found.", true, true, "error");
            return;
        }

        // $migrationFiles = glob($migrationDir . '*.php');
        $migrationFiles = glob("{$migrationDir}*.php");

        if (empty($migrationFiles)) {
            $this->message("No migrations found.", false, true, "info");
            return;
        }

        foreach (array_reverse($migrationFiles) as $file) {
            $migrationClass = include $file;

            if (method_exists($migrationClass, 'down')) {
                $migrationClass->down();
                $this->message("Rolled back: " . basename($file), false, true, "info");
            } else {
                $this->message("Migration class does not have a down method: " . basename($file), true, true, "error");
            }
        }

        foreach ($migrationFiles as $file) {
            $migrationClass = include $file;

            if (method_exists($migrationClass, 'up')) {
                $migrationClass->up();
                $this->message("Migrated: " . basename($file), false, true, "info");
            } else {
                $this->message("Migration class does not have an up method: " . basename($file), true, true, "error");
            }
        }
    }

    private function makeMigration()
    {
        $migrationName = $this->prompt("Enter the migration name (e.g., users)");

        if (empty($migrationName)) {
            $this->message("Migration name cannot be empty.", true, true, "error");
            return;
        }

        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            if (!mkdir($migrationDir, 0755, true)) {
                $this->message("Unable to create the migrations directory.", true, true, "error");
                return;
            }
        }

        $migrationFile = $migrationDir . date("Y_m_d_His_") . 'create_' . strtolower($migrationName) . '_table' . '.php';

        $sampleFile = __DIR__ . "/samples/migration/migration-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Migration sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($migrationName);
        $table_name = strtolower($migrationName);

        $content = file_get_contents($sampleFile);
        $content = str_replace("{TABLENAME}", $table_name, $content);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($migrationFile, $content) === false) {
            $this->message("Unable to create the migration file.", true, true, "error");
            return;
        }

        $this->message("Migration file {$className} created successfully", false, true, "created");
    }

    private function listAvailableActions()
    {
        $this->message("Available Migration Commands:", false, false, 'info');
        foreach (self::ACTIONS as $action => $description) {
            $this->output("  \033[0;37m{$action}\033[0m: \033[0;36m{$description}\033[0m", 1);
        }
    }
}
