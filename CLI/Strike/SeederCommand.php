<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Seeder commands ===========
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CommandInterface;

class SeederCommand implements CommandInterface
{
    private const ACTION_GENERATE = 'generate';
    private const ACTION_DROP = 'drop';
    private const ACTION_CREATE = 'create';

    public $basePath;

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
                $this->message("Error: Project root not found.", true, true, 'error');
            }
        }

        $this->basePath = $currentDirectory;
    }

    public function execute(array $args)
    {
        // Check if the required arguments are provided
        if (count($args["args"]) < 1) {
            $this->message("Strike Usage: seeder <action> <filename: Optional>", true, true, 'warning');
        }

        $action = $args["args"][0];
        $filename = $args["args"][1] ?? null;

        // Create the view folder's and file
        $this->callAction($action, $filename);
    }

    private function callAction($action, $filename = null)
    {
        // Check for the action type.
        switch ($action) {
            case self::ACTION_GENERATE:
                $this->generate($filename);
                break;
            case self::ACTION_DROP:
                $this->drop($filename);
                break;
            case self::ACTION_CREATE:
                $this->create($filename);
                break;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
    }

    private function generate($filename = null)
    {
        // Check if the seeders directory already exists.
        $seedersDir = $this->basePath . DIRECTORY_SEPARATOR . "seeders" . DIRECTORY_SEPARATOR;

        if (!is_dir($seedersDir)) {
            // Create the seeders directory
            if (!mkdir($seedersDir, 0755, true)) {
                $this->message("Error: Unable to create the seeders directory.", true, true, "error");
            }
        }

        if (!empty($filename)) {
            $seederFile = $seedersDir . $filename . ".php";
            /** Run a single class filename seeder */
            $mFile = pathinfo($seederFile, PATHINFO_FILENAME);
            $this->message("Seeding File: {$mFile}");

            require_once $seederFile;

            $class_name = basename($seederFile);
            preg_match("/(\d{4}-\d{2}-\d{2}_\d{6}_\w+)/", $class_name, $match);
            $class_name = ucfirst(str_replace(".php", "", $match[0]));
            $class_name = ucfirst(str_replace("-", "_", $match[1]));
            $class_name = trim($class_name, '_');
            $class_name = "BM_" . $class_name;

            $myclass = new ("\PhpStrike\seeders\\$class_name");

            /** Call the Up method */
            $myclass->seeding();
            $this->message("Seeding Complete!");
            $this->message("Seeded File: {$mFile}");
        }

        /** Get all the files in the seeders folders */
        $seederFiles = glob($seedersDir . '*.php');

        if (!empty($seederFiles)) {
            foreach ($seederFiles as $seederFile) {
                $mFile = pathinfo($seederFile, PATHINFO_FILENAME);
                $this->message("Seeding File: {$mFile}");

                require_once $seederFile;

                $class_name = basename($seederFile);
                preg_match("/(\d{4}-\d{2}-\d{2}_\d{6}_\w+)/", $class_name, $match);
                $class_name = ucfirst(str_replace(".php", "", $match[0]));
                $class_name = ucfirst(str_replace("-", "_", $match[1]));
                $class_name = trim($class_name, '_');
                $class_name = "BM_" . $class_name;

                $myclass = new ("\PhpStrike\seeders\\$class_name");

                /** Call the Up method */
                $myclass->seeding();
                $this->message("Seeding Complete!");
                $this->message("Seeded Class: {$class_name}");
            }
        }
    }

    private function drop($filename = null)
    {
        // Check if the seeders directory already exists.
        $seedersDir = $this->basePath . DIRECTORY_SEPARATOR . "seeders" . DIRECTORY_SEPARATOR;

        if ($seedersDir && !empty($filename)) {
            $seederFile = $seedersDir . $filename . ".php";

            if (file_exists($seederFile)) {
                if (unlink($seederFile)) {
                    $this->message("Seeder File Deleted!");
                }
            }
        }

        /** Get all the files in the migrations folders */
        $seederFiles = glob($seedersDir . '*.php');

        if (!empty($seederFiles)) {
            foreach ($seederFiles as $seederFile) {
                if (unlink($seederFile)) {
                    $this->message("Seeder File Deleted!");
                }
            }
        }
    }

    private function create($filename)
    {
        // Check if the seeders directory already exists
        $seedersDir = $this->basePath . DIRECTORY_SEPARATOR . "seeders" . DIRECTORY_SEPARATOR;

        if (!is_dir($seedersDir)) {
            // Create the seeders directory
            mkdir($seedersDir, 0755, true);
        }

        // Check if the directory was created successfully
        if (!is_dir($seedersDir)) {
            $this->message("Error: Unable to create the seeders directory.", true, true, "error");
        }

        /**
         * Check if Seeder file already exists.
         */
        $seederFile = $seedersDir . date("Y-m-d_His_") . $filename . '.php';
        if (file_exists($seederFile)) {
            $mg = ucfirst($filename);
            $this->message("Seeder File {$mg} already exists.", true, true, "warning");
        }

        // Create the seeder file
        if (!touch($seederFile)) {
            $this->message("Error: Unable to create the seeder file.", true, true, "error");
        }

        /**
         * Customize the content of seeder class here.
         * From the sample class.
         */
        $sample_file = __DIR__ . "/samples/seeder-sample.php";

        if (!file_exists($sample_file))
            $this->message("Error: Seeder Sample file not found in: " . $sample_file, true, true, "error");

        $class_name = "BM_" . pathinfo($seederFile, PATHINFO_FILENAME);
        $class_name = str_replace("-", "_", $class_name);

        $content = file_get_contents($sample_file);
        $content = str_replace("{CLASSNAME}", $class_name, $content);

        if (file_put_contents($seederFile, $content) === false) {
            $this->message("Error: Unable to write content to the seeder file.", true, true, "error");
        }
        $this->message("Seeder file created successfully, FileName: '$seederFile'!");
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
