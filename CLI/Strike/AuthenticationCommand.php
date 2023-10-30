<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Model commands ============
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CommandInterface;

/**
 * Logic for creating Complete Authentication
 */

class AuthenticationCommand implements CommandInterface
{
    public $basePath;

    private const ACTION_CREATE = 'create';

    public function __construct()
    {
        // Get the current file's directory
        $currentDirectory = __DIR__;

        // Navigate up the directory tree until you reach the project's root
        while (!file_exists($currentDirectory . '/vendor')) {
            // Go up one level
            $currentDirectory = dirname($currentDirectory);

            // Check if you have reached the filesystem root (to prevent infinite loop)
            if ($currentDirectory === '/') {
                $this->message("Error: Project root not found.", true, true, "error");
            }
        }

        $this->basePath = $currentDirectory;
    }

    public function execute(array $args)
    {
        // Check if the required arguments are provided
        if (count($args["args"]) < 1) {
            $this->message("Strike Usage: authentication <action>", true, true, "warning");
        }

        $action = $args["args"][0];

        // Create the view folder's and file
        $this->callAction($action);
    }

    private function callAction($action)
    {
        // Check for the action type.
        switch ($action) {
            case self::ACTION_CREATE:
                $this->createMigrations();
                $this->createModel();
                $this->createView();
                $this->createController();
                break;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
    }

    private function createMigrations()
    {
        $migrationsData = [
            'users' => __DIR__ . "/samples/authentication/user-migration-sample.php",
            'login_attempts' => __DIR__ . "/samples/authentication/login-attempts-migration-sample.php",
            'user_sessions' => __DIR__ . "/samples/authentication/user-sessions-migration-sample.php",
        ];
        // Check if the model directory already exists
        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            // Create the migration directory
            mkdir($migrationDir, 0755, true);
        }

        // Check if the directory was created successfully
        if (!is_dir($migrationDir)) {
            $this->message("Error: Unable to create the migration directory.", true, true, "error");
        }

        foreach ($migrationsData as $migrationName => $sampleFile) {
            /**
             * Check if Migration file already exists.
             */
            $migrationFile = $migrationDir . date("Y-m-d_His_") . $migrationName . '.php';
            if (file_exists($migrationFile)) {
                $mg = ucfirst($migrationName);
                $this->message("Migration File {$mg} already exists.", false, true, "warning");
                continue; // Skip creating this migration file
            }

            // Create the migration file
            if (!touch($migrationFile)) {
                $this->message("Error: Unable to create the migration file for {$migrationName}.", true, true, "error");
                continue;
            }

            /**
             * Customize the content of migration class here.
             * Use the sample file for this specific model.
             */
            if (!file_exists($sampleFile)) {
                $this->message("Error: Sample file not found for {$migrationName} in: " . $sampleFile, true, true, "error");
                continue;
            }

            $class_name = "BM_" . pathinfo($migrationFile, PATHINFO_FILENAME);
            $class_name = str_replace("-", "_", $class_name);

            $table_name = strtolower($class_name);

            $content = file_get_contents($sampleFile);
            $content = str_replace("{TABLENAME}", $table_name, $content);
            $content = str_replace("{CLASSNAME}", $class_name, $content);

            if (file_put_contents($migrationFile, $content) === false) {
                $this->message("Error: Unable to write content to the migration file for {$migrationName}.", true, true, "error");
            } else {
                $this->message("Migration file created successfully for {$migrationName}, FileName: '$migrationFile'!");
            }
        }
    }

    private function createModel()
    {
        $modelsData = [
            'users' => __DIR__ . "/samples/authentication/user-model-sample.php",
            'userSessions' => __DIR__ . "/samples/authentication/user-session-model-sample.php",
        ];
        // Check if the model directory already exists
        $modelDir = $this->basePath . DIRECTORY_SEPARATOR . "models" . DIRECTORY_SEPARATOR;

        if (!is_dir($modelDir)) {
            // Create the model directory
            mkdir($modelDir, 0755, true);
        }

        // Check if the directory was created successfully
        if (!is_dir($modelDir)) {
            $this->message("Error: Unable to create the model directory.", true, true, "error");
        }

        foreach ($modelsData as $modelName => $sampleFile) {
            /**
             * Check if Migration file already exists.
             */
            $modelFile = $modelDir . ucfirst($modelName) . '.php';
            if (file_exists($modelFile)) {
                $m = ucfirst($modelName);
                $this->message("Model File {$m} already exists.", false, true, "warning");
                continue; // Skip creating this migration file
            }

            // Create the migration file
            if (!touch($modelFile)) {
                $this->message("Error: Unable to create the model file for {$modelName}.", true, true, "error");
                continue;
            }

            /**
             * Customize the content of migration class here.
             * Use the sample file for this specific model.
             */
            if (!file_exists($sampleFile)) {
                $this->message("Error: Sample file not found for {$modelName} in: " . $sampleFile, true, true, "error");
                continue;
            }

            $class_name = $this->rename_camel_case($modelName);

            $table_name = strtolower($class_name);

            $content = file_get_contents($sampleFile);
            $content = str_replace("{TABLENAME}", $table_name, $content);
            $content = str_replace("{CLASSNAME}", $class_name, $content);

            if (file_put_contents($modelFile, $content) === false) {
                $this->message("Error: Unable to write content to the model file for {$modelName}.", true, true, "error");
            } else {
                $this->message("Model file created successfully for {$modelName}, FileName: '$modelFile'!");
            }
        }
    }

    private function createView()
    {
        $viewsData = [
            'signup' => __DIR__ . "/samples/authentication/signup-view-sample.php",
            'login' => __DIR__ . "/samples/authentication/login-view-sample.php",
        ];
        // Check if the model directory already exists
        $viewDir = $this->basePath . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "auth" . DIRECTORY_SEPARATOR;

        if (!is_dir($viewDir)) {
            // Create the model directory
            mkdir($viewDir, 0755, true);
        }

        // Check if the directory was created successfully
        if (!is_dir($viewDir)) {
            $this->message("Error: Unable to create the templates directory.", true, true, "error");
        }

        foreach ($viewsData as $viewName => $sampleFile) {
            /**
             * Check if View file already exists.
             */
            $viewFile = $viewDir . $viewName . '.php';
            if (file_exists($viewFile)) {
                $m = ucfirst($viewName);
                $this->message("View File {$m} already exists.", false, true, "warning");
                continue; // Skip creating this view file
            }

            // Create the view file
            if (!touch($viewFile)) {
                $this->message("Error: Unable to create the model file for {$viewName}.", true, true, "error");
                continue;
            }

            /**
             * Customize the content of view class here.
             * Use the sample file for this specific model.
             */
            if (!file_exists($sampleFile)) {
                $this->message("Error: Sample file not found for {$viewName} in: " . $sampleFile, true, true, "error");
                continue;
            }

            $content = file_get_contents($sampleFile);

            if (file_put_contents($viewFile, $content) === false) {
                $this->message("Error: Unable to write content to the model file for {$viewName}.", true, true, "error");
            } else {
                $this->message("View file created successfully for {$viewName}, FileName: '$viewFile'!");
            }
        }
    }

    private function createController()
    {
        $controllersData = [
            'authController' => __DIR__ . "/samples/authentication/auth-controller-sample.php",
        ];
        // Check if the controllers directory already exists
        $controllerDir = $this->basePath . DIRECTORY_SEPARATOR . "controllers" . DIRECTORY_SEPARATOR;

        if (!is_dir($controllerDir)) {
            // Create the controllers directory
            mkdir($controllerDir, 0755, true);
        }

        // Check if the directory was created successfully
        if (!is_dir($controllerDir)) {
            $this->message("Error: Unable to create the controllers directory.", true, true, "error");
        }

        foreach ($controllersData as $controllerName => $sampleFile) {
            /**
             * Check if Controller file already exists.
             */
            $controllerFile = $controllerDir . ucfirst($controllerName) . '.php';
            if (file_exists($controllerFile)) {
                $m = ucfirst($controllerName);
                $this->message("Controller File {$m} already exists.", false, true, "warning");
                continue; // Skip creating this controller file
            }

            // Create the controller file
            if (!touch($controllerFile)) {
                $this->message("Error: Unable to create the controller file for {$controllerName}.", true, true, "error");
                continue;
            }

            /**
             * Customize the content of controller class here.
             * Use the sample file for this specific model.
             */
            if (!file_exists($sampleFile)) {
                $this->message("Error: Sample file not found for {$controllerName} in: " . $sampleFile, true, true, "error");
                continue;
            }

            $content = file_get_contents($sampleFile);

            if (file_put_contents($controllerFile, $content) === false) {
                $this->message("Error: Unable to write content to the controller file for {$controllerName}.", true, true, "error");
            } else {
                $this->message("Controller file created successfully for {$controllerName}, FileName: '$controllerFile'!");
            }
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

        echo $output;

        if ($die) {
            die();
        }
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
