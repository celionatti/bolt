<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Migration commands ========
 * ====================================
 */

namespace Bolt\Bolt\CLI\Strike;

use Bolt\Bolt\CLI\CommandInterface;

class MigrationCommand implements CommandInterface
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
                $this->message("Error: Project root not found.", true, true, 'error');
            }
        }

        $this->basePath = $currentDirectory;
    }

    public function execute(array $args)
    {
        // Check if the required arguments are provided
        if (count($args["args"]) < 1) {
            $this->message("Strike Usage: migration <action> <filename: Optional>", true, true, 'warning');
        }

        $action = $args["args"][0];
        $filename = $args["args"][1] ?? null;

        // Create the view folder's and file
        $this->callAction($action, $filename);
    }

    private function callAction($action, $filename = null)
    {
        // Check for the action type.
        if ($action === "migrate") {
            $this->migrate($action, $filename);
        } elseif ($action === "rollback") {
            $this->rollback($action, $filename);
        } elseif ($action === "refresh") {
            $this->refresh($action, $filename);
        } elseif ($action === "create") {
            $this->create($action, $filename);
        } else {
            $this->message("Unknown Command - You can check help or docs, to see the lists of command and method of calling.", true, true, 'warning');
        }
    }

    private function migrate($action, $filename = null)
    {
        // Check if the migrations directory already exists.
        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            // Create the migration directory
            if (!mkdir($migrationDir, 0755, true)) {
                $this->message("Error: Unable to create the migration directory.", true, true, "error");
            }
        }

        if (!empty($filename)) {
            $migrationFile = $migrationDir . $filename . ".php";
            /** Run a single class filename migration */
            $mFile = pathinfo($migrationFile, PATHINFO_FILENAME);
            $this->message("Migrating File: {$mFile}");

            require_once $migrationFile;

            $class_name = basename($migrationFile);
            preg_match("/(\d{4}-\d{2}-\d{2}_\d{6}_\w+)/", $class_name, $match);
            $class_name = ucfirst(str_replace(".php", "", $match[0]));
            $class_name = ucfirst(str_replace("-", "_", $match[1]));
            $class_name = trim($class_name, '_');
            $class_name = "BM_" . $class_name;

            $myclass = new ("\Bolt\migrations\\$class_name");

            /** Call the Up method */
            $myclass->up();
            $this->message("Migration Complete!");
            $this->message("Migrated File: {$mFile}");
        }

        /** Get all the files in the migrations folders */
        $migrationFiles = glob($migrationDir . '*.php');

        if (!empty($migrationFiles)) {
            foreach ($migrationFiles as $migrationFile) {
                $mFile = pathinfo($migrationFile, PATHINFO_FILENAME);
                $this->message("Migrating File: {$mFile}");

                require_once $migrationFile;

                $class_name = basename($migrationFile);
                preg_match("/(\d{4}-\d{2}-\d{2}_\d{6}_\w+)/", $class_name, $match);
                $class_name = ucfirst(str_replace(".php", "", $match[0]));
                $class_name = ucfirst(str_replace("-", "_", $match[1]));
                $class_name = trim($class_name, '_');
                $class_name = "BM_" . $class_name;

                $myclass = new ("\Bolt\migrations\\$class_name");

                /** Call the Up method */
                $myclass->up();
                $this->message("Migration Complete!");
                $this->message("Migrated Class: {$class_name}");
            }
        }
    }

    private function rollback($action, $filename = null)
    {
        // Check if the model directory already exists.
        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            // Create the migration directory
            if (!mkdir($migrationDir, 0755, true)) {
                $this->message("Error: Unable to create the migration directory.", true, true, "error");
            }
        }

        if (!empty($filename)) {
            $migrationFile = $migrationDir . $filename . ".php";
            $mFile = pathinfo($migrationFile, PATHINFO_FILENAME);
            /** Run a single class filename migration */
            $this->message("Migrating File: {$mFile}");

            require_once $migrationFile;

            $class_name = basename($migrationFile);
            preg_match("/(\d{4}-\d{2}-\d{2}_\d{6}_\w+)/", $class_name, $match);
            $class_name = ucfirst(str_replace(".php", "", $match[0]));
            $class_name = ucfirst(str_replace("-", "_", $match[1]));
            $class_name = trim($class_name, '_');
            $class_name = "BM_" . $class_name;

            $myclass = new ("\Bolt\migrations\\$class_name");

            /** Call the Down method */
            $myclass->down();
            $this->message("Migration Complete!");
            $this->message("Migrated File: {$migrationFile}");
        }

        /** Get all the files in the migrations folders */
        $migrationFiles = glob($migrationDir . '*.php');

        if (!empty($migrationFiles)) {
            foreach ($migrationFiles as $migrationFile) {
                $mFile = pathinfo($migrationFile, PATHINFO_FILENAME);
                $this->message("Migrating File: {$mFile}");

                require_once $migrationFile;

                $class_name = basename($migrationFile);
                preg_match("/(\d{4}-\d{2}-\d{2}_\d{6}_\w+)/", $class_name, $match);
                $class_name = ucfirst(str_replace(".php", "", $match[0]));
                $class_name = ucfirst(str_replace("-", "_", $match[1]));
                $class_name = trim($class_name, '_');
                $class_name = "BM_" . $class_name;

                $myclass = new ("\Bolt\migrations\\$class_name");

                /** Call the Down method */
                $myclass->down();
                $this->message("Migration Complete!");
                $this->message("Migrated File: {$migrationFile}");
            }
        }
    }

    private function refresh($action, $filename = null)
    {
        $this->rollback($action, $filename);
        $this->migrate($action, $filename);
    }

    private function create($action, $filename)
    {
        // Check if the model directory already exists
        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            // Create the model directory
            mkdir($migrationDir, 0755, true);
        }

        // Check if the directory was created successfully
        if (!is_dir($migrationDir)) {
            $this->message("Error: Unable to create the migration directory.", true, true, "error");
        }

        /**
         * Check if Migration file already exists.
         */
        $migrationFile = $migrationDir . date("Y-m-d_His_") . $filename . '.php';
        if (file_exists($migrationFile)) {
            $mg = ucfirst($filename);
            $this->message("Migration File {$mg} already exists.", true, true, "warning");
        }

        // Create the migration file
        if (!touch($migrationFile)) {
            $this->message("Error: Unable to create the migration file.", true, true, "error");
        }

        /**
         * Customize the content of migration class here.
         * From the sample class.
         */
        $sample_file = __DIR__ . "/samples/migration-sample.php";

        if (!file_exists($sample_file))
            $this->message("Error: Migration Sample file not found in: " . $sample_file, true, true, "error");

        $class_name = "BM_" . pathinfo($migrationFile, PATHINFO_FILENAME);
        $class_name = str_replace("-", "_", $class_name);

        $table_name = strtolower($class_name);

        $content = file_get_contents($sample_file);
        $content = str_replace("{TABLENAME}", $table_name, $content);
        $content = str_replace("{CLASSNAME}", $class_name, $content);

        // file_put_contents($migrationFile, $content);
        if (file_put_contents($migrationFile, $content) === false) {
            $this->message("Error: Unable to write content to the migration file.", true, true, "error");
        }
        $this->message("Migration file created successfully, FileName: '$migrationFile'!");
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
