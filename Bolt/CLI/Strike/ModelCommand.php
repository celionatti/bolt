<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Model commands ============
 * ====================================
 */

namespace Bolt\Bolt\CLI\Strike;

use Bolt\Bolt\Bolt;
use Bolt\Bolt\CLI\CommandInterface;

/**
 * Logic for creating Models
 * === and generating migrations.
 */

class ModelCommand implements CommandInterface
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
                echo "Error: Project root not found.\n";
                exit(1);
            }
        }

        $this->basePath = $currentDirectory;
    }

    public function execute(array $args)
    {
        // Check if the required arguments are provided
        if (count($args["args"]) < 1) {
            $this->message("Strike Usage: model <ModelName>");
            exit(1);
        }

        $modelName = $args["args"][0];

        // Create the model folder and file
        $this->createModel($modelName);

        if (isset($args["options"]["m"])) {
            $this->createMigration($modelName);
        }
    }

    private function createModel($modelName)
    {
        // Check if the model directory already exists
        $modelDir = $this->basePath . DIRECTORY_SEPARATOR . "models" . DIRECTORY_SEPARATOR;

        if (!is_dir($modelDir)) {
            // Create the model directory
            if (!mkdir($modelDir, 0755, true)) {
                $this->message("Error: Unable to create the model directory.", true);
            }
        }

        /**
         * Check if Model file already exists.
         */
        $modelFile = $modelDir . ucfirst($modelName) . '.php';
        if (file_exists($modelFile)) {
            $m = ucfirst($modelName);
            $this->message("Model File {$m} already exists.", true);
        }

        /**
         * Create the model file, if not existing.
         */
        touch($modelFile);

        /**
         * Customize the content of model class here.
         * From the sample class.
         */
        $sample_file = __DIR__ . "/samples/model-sample.php";

        if (!file_exists($sample_file))
            $this->message("Error: Model Sample file not found in: " . $sample_file, true);


        $class_name = $this->rename_camel_case($modelName);

        $table_name = strtolower($class_name);

        $content = file_get_contents($sample_file);
        $content = str_replace("{TABLENAME}", $table_name, $content);
        $content = str_replace("{CLASSNAME}", $class_name, $content);

        if (file_put_contents($modelFile, $content) === false) {
            $this->message("Error: Unable to create the model file.", true);
        }

        $this->message("Model file created successfully, FileName: '$modelName'!");
    }

    private function createMigration($modelName)
    {
        // Check if the model directory already exists
        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            // Create the model directory
            mkdir($migrationDir, 0755, true);
        }

        // Check if the directory was created successfully
        if (!is_dir($migrationDir)) {
            $this->message("Error: Unable to create the migration directory.", true);
        }

        /**
         * Check if Migration file already exists.
         */
        $migrationFile = $migrationDir . date("Y-m-d_His_") . $modelName . '.php';
        if (file_exists($migrationFile)) {
            $mg = ucfirst($modelName);
            $this->message("Migration File {$mg} already exists.", true);
        }

        // Create the migration file
        if (!touch($migrationFile)) {
            $this->message("Error: Unable to create the migration file.", true);
        }

        /**
         * Customize the content of model class here.
         * From the sample class.
         */
        $sample_file = __DIR__ . "/samples/migration-sample.php";

        if (!file_exists($sample_file))
            $this->message("Error: Migration Sample file not found in: " . $sample_file, true);

        $class_name = "BM_" . pathinfo($migrationFile, PATHINFO_FILENAME);
        $class_name = str_replace("-", "_", $class_name);

        $table_name = strtolower($class_name);

        $content = file_get_contents($sample_file);
        $content = str_replace("{TABLENAME}", $table_name, $content);
        $content = str_replace("{CLASSNAME}", $class_name, $content);

        // file_put_contents($migrationFile, $content);
        if (file_put_contents($migrationFile, $content) === false) {
            $this->message("Error: Unable to write content to the migration file.", true);
        }
        $this->message("Migration file created successfully, FileName: '$migrationFile'!");
    }

    public function message(string $message, bool $die = false): void
    {
        echo "\n\r" . "[" . date("Y-m-d H:i:s") . "] - " . ucfirst($message) . PHP_EOL;

        if ($die) exit(1);
    }

    private function rename_camel_case($value)
    {
        $name = preg_replace('/[_-]/', ' ', $value);
        $parts = explode(' ', $name);
        $value = '';
        foreach ($parts as $part) {
            $value .= ucfirst($part);
        }

        return $value;
    }
}