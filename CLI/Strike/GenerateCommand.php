<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Generate commands =========
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CommandInterface;

class GenerateCommand implements CommandInterface
{
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
                $this->message("Error: Project root not found.", true, true, "error");
            }
        }

        $this->basePath = $currentDirectory;
    }

    public function execute(array $args)
    {
        // Check if the required arguments are provided
        if (count($args["args"]) < 1) {
            $this->message("Strike Usage: generate <Action>", true, true, 'warning');
        }

        $action = $args["args"][0];

        // Create the view folder's and file
        $this->callAction($action);
    }

    private function callAction($action)
    {
        // Check for the action type.
        if ($action === "key") {
            $this->generateKey();
        } else {
            $this->message("Unknown Command - You can check help or docs, to see the lists of command and method of calling.", true, true, 'warning');
        }
    }

    private function generateKey()
    {
        // Check if the migrations directory already exists.
        $configDir = $this->basePath . DIRECTORY_SEPARATOR . "configs" . DIRECTORY_SEPARATOR;
        $BoltConfigDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "Configs" . DIRECTORY_SEPARATOR;

        if (!is_dir($configDir) || !is_dir($BoltConfigDir)) {
            // Create the configs directory
            if (!mkdir($configDir, 0755, true) || !mkdir($BoltConfigDir, 0755, true)) {
                $this->message("Error: Unable to create the configs directory.", true, true, 'error');
            }
        }

        /**
         * Check if load file already exists.
         */
        $loadFile = $configDir . "load" . '.php';
        $BlotLoadFile = $BoltConfigDir . "load" . '.php';
        if (file_exists($loadFile) || file_exists($BlotLoadFile)) {
            $m = ucfirst("load");
            $this->message("Config File {$m} already exists.", true, true, 'warning');
        }

        /**
         * Create the load file, if not existing.
         */
        touch($loadFile);
        touch($BlotLoadFile);

        /**
         * Customize the content of controller class here.
         * From the sample class.
         */
        $sample_file = __DIR__ . "/samples/config-load-sample.php";
        $bolt_sample_file = __DIR__ . "/samples/bolt-load-sample.php";

        if (!file_exists($sample_file))
            $this->message("Error: Config load Sample file not found in: " . $sample_file, true, true, 'warning');

        if (!file_exists($bolt_sample_file))
            $this->message("Error: Bolt Config load Sample file not found in: " . $bolt_sample_file, true, true, 'warning');

        
        $key = $this->create_random_key();

        $content = file_get_contents($sample_file);
        $boltcontent = file_get_contents($bolt_sample_file);
        $content = str_replace("{KEY}", $key, $content);
        $boltcontent = str_replace("{KEY}", $key, $boltcontent);

        if (file_put_contents($loadFile, $content) === false) {
            $this->message("Error: Unable to create the load file.", true, true, 'error');
        }

        if (file_put_contents($BlotLoadFile, $boltcontent) === false) {
            $this->message("Error: Unable to create the bolt load file.", true, true, 'error');
        }

        $m = ucfirst($loadFile);

        $this->message("Config file created successfully, FileName: '$m'!");
    }

    private function create_random_key($length = 32)
    {
        // Check if the length is valid
        if ($length <= 0) {
            $this->message("Key length must be a positive integer.", true, true, 'warning');
        }

        // Generate random bytes
        $randomBytes = random_bytes($length);

        // Convert random bytes to a hexadecimal string
        $key = bin2hex($randomBytes);

        return $key;
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
