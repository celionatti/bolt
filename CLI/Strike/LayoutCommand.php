<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Layout commands =============
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CommandInterface;

class LayoutCommand implements CommandInterface
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
        if (count($args["args"]) < 2) {
            $this->message("Strike Usage: layout <action> <filename> - For creating layouts. The layoutName or filename is compulsory, while others are Optional.", true, true, "warning");
        }

        $action = $args["args"][0];
        $filename = $args["args"][1];

        $this->callAction($action, $filename);
    }

    private function callAction($action, $filename)
    {
        // Check for the action type.
        switch ($action) {
            case self::ACTION_CREATE:
                $this->createLayout($filename);
                break;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
    }

    private function createLayout($layoutName)
    {
        // Check if the layout directory already exists.
        $layoutDir = $this->basePath . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "layouts" . DIRECTORY_SEPARATOR;

        if (!is_dir($layoutDir)) {
            // Create the layout directory
            if (!mkdir($layoutDir, 0755, true)) {
                $this->message("Error: Unable to create the layouts directory.", true, true, "error");
            }
        }

        /**
         * Check if layout file already exists.
         */
        $layoutFile = $layoutDir . $layoutName . ".php";
        if (file_exists($layoutFile)) {
            $m = ucfirst($layoutFile);
            $this->message("Layout File {$m} already exists.", true, true, "warning");
        }

        /**
         * Create the layout file, if not existing.
         */
        touch($layoutFile);

        /**
         * Customize the content of layout file here.
         * From the sample file.
         */
        $sample_file = __DIR__ . "/samples/layout-sample.php";

        if (!file_exists($sample_file))
            $this->message("Error: Layout Sample file not found in: " . $sample_file, true, true, "error");


        $content = file_get_contents($sample_file);

        if (file_put_contents($layoutFile, $content) === false) {
            $this->message("Error: Unable to create the layout file.", true, true, "error");
        }

        $m = ucfirst($layoutName);

        $this->message("Layout file created successfully, FileName: '$m'!");
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
