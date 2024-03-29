<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - View commands =============
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CommandInterface;

class ViewCommand implements CommandInterface
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
            $this->message("Strike Usage: view <action> <filename> <folders> <Optional: --extension> - For creating view with {Blade: .blade.php, Twig: .twig, PHP: .php} extension. The action and filename is compulsory, while others are Optional. Also Note: If not define <folders> only <fileName> will be created. If not define --<extension> the default extension will be .php", true, true, "warning");
        }

        $action = $args["args"][0];
        $filename = $args["args"][1];
        $folders = $args["args"][2] ?? null;

        if (isset($args["options"]["blade"])) {
            $extension = ".blade.php";
        } elseif (isset($args["options"]["twig"])) {
            $extension = ".twig";
        } else {
            $extension = ".php";
        }

        $this->callAction($action, $filename, $folders, $extension);
    }

    private function callAction($action, $filename, $folders, $extension)
    {
        // Check for the action type.
        switch ($action) {
            case self::ACTION_CREATE:
                $this->createView($filename, $folders, $extension);
                break;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
    }

    private function createView($viewName, $folders = null, $extension = ".php")
    {
        // Check for the extension to determine where to create folders.
        // Check if the model directory already exists.
        if ($extension == ".blade.php") {
            $viewDir = $this->basePath . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "blade-views" . DIRECTORY_SEPARATOR . $folders;
        } elseif ($extension == ".twig") {
            $viewDir = $this->basePath . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "twig-views" . DIRECTORY_SEPARATOR . $folders;
        } else {
            $viewDir = $this->basePath . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $folders;
        }

        if (!is_dir($viewDir)) {
            // Create the model directory
            if (!mkdir($viewDir, 0755, true)) {
                $this->message("Error: Unable to create the view directory.", true, true, "error");
            }
        }

        /**
         * Check if View file already exists.
         */
        $viewFile = $viewDir . DIRECTORY_SEPARATOR . $viewName . $extension;
        if (file_exists($viewFile)) {
            $m = ucfirst($viewName . $extension);
            $this->message("View File {$m} already exists.", true, true, "warning");
        }

        /**
         * Create the view file, if not existing.
         */
        touch($viewFile);

        /**
         * Customize the content of view file here.
         * From the sample file.
         */

        if ($extension == ".blade.php") {
            $sample_file = __DIR__ . "/samples/blade-view-sample.php";
        } elseif ($extension == ".twig") {
            $sample_file = __DIR__ . "/samples/twig-view-sample.php";
        } else {
            $sample_file = __DIR__ . "/samples/view-sample.php";
        }

        if (!file_exists($sample_file))
            $this->message("Error: View Sample file not found in: " . $sample_file, true, true, "error");


        $content = file_get_contents($sample_file);

        if (file_put_contents($viewFile, $content) === false) {
            $this->message("Error: Unable to create the view file.", true, true, "error");
        }

        $m = ucfirst($viewName . $extension);

        $this->message("View file created successfully, FileName: '$m'!");
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

        echo $output . PHP_EOL;

        if ($die) {
            die();
        }
    }
}
