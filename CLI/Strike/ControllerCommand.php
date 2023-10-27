<?php

declare(strict_types=1);

/**
 * ==================================================
 * ==================           =====================
 * Strike - Controller commands
 * ==================           =====================
 * ==================================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CommandInterface;

class ControllerCommand implements CommandInterface
{
    public $basePath;

    private const ACTION_CREATE = 'create';

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
                $this->message("Error: Project root not found.", true, true, "error");
            }
        }

        $this->basePath = $currentDirectory;
    }

    public function execute(array $args)
    {
        // Check if the required arguments are provided
        if (count($args["args"]) < 2) {
            $this->message("Strike Usage: controller <action> <controllername> <folders: --Optional (--c)>", true, true, 'warning');
        }

        $action = $args["args"][0];
        $filename = $args["args"][1];
        $crudMethod = $args["options"]["c"] ?? null;

        $this->callAction($action, $filename, $crudMethod);
    }

    private function callAction($action, $filename, $crudMethod)
    {
        // Check for the action type.
        switch ($action) {
            case self::ACTION_CREATE:
                $this->createController($filename, $crudMethod);
                break;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
    }

    private function createController($controllerName, $allowCrud = null)
    {
        // Check if the controller directory already exists
        $controllerDir = $this->basePath . DIRECTORY_SEPARATOR . "controllers" . DIRECTORY_SEPARATOR;

        if (!is_dir($controllerDir)) {
            // Create the controller directory
            if (!mkdir($controllerDir, 0755, true)) {
                $this->message("Error: Unable to create the controller directory.", true, true, 'error');
            }
        }

        /**
         * Check if Controller file already exists.
         */
        $controllerFile = $controllerDir . ucfirst($controllerName) . "Controller" . '.php';
        if (file_exists($controllerFile)) {
            $m = ucfirst($controllerName) . "Controller";
            $this->message("Controller File {$m} already exists.", true, true, 'warning');
        }

        /**
         * Create the controller file, if not existing.
         */
        touch($controllerFile);

        /**
         * Customize the content of controller class here.
         * From the sample class.
         */
        if ($allowCrud) {
            $sample_file = __DIR__ . "/samples/controller-with-crud-sample.php";
        } else {
            $sample_file = __DIR__ . "/samples/controller-sample.php";
        }

        if (!file_exists($sample_file))
            $this->message("Error: Controller Sample file not found in: " . $sample_file, true, true, 'warning');


        $class_name = pathinfo($controllerFile, PATHINFO_FILENAME);

        $view_path = strtolower($controllerName);

        $content = file_get_contents($sample_file);
        $content = str_replace("{VIEWPATH}", $view_path, $content);
        $content = str_replace("{CLASSNAME}", $class_name, $content);

        if (file_put_contents($controllerFile, $content) === false) {
            $this->message("Error: Unable to create the controller file.", true, true, 'error');
        }

        $m = ucfirst($controllerName) . "Controller";

        $this->message("Controller file created successfully, FileName: '$m'!");
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
