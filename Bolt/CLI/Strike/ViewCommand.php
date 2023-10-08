<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - View commands =============
 * ====================================
 */

namespace Bolt\Bolt\CLI\Strike;

use Bolt\Bolt\CLI\CommandInterface;

class ViewCommand implements CommandInterface
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
        var_dump($args);
        die;
        // Check if the required arguments are provided
        if (count($args["args"]) < 1) {
            $this->message("Strike Usage: view <ViewName> - For creating view with .php extension");
            exit(1);
        }

        $viewName = $args["args"][0];

        // Create the view folder and file
        $this->createView($viewName);

        if (isset($args["options"]["blade"])) {
            // $this->createMigration($modelName);
        }

        if (isset($args["options"]["twig"])) {
            // $this->createMigration($modelName);
        }
    }

    private function createView($viewName)
    {
    }

    private function create_folders_n_file($path)
    {
        $parts = explode("/", $path);
        $numParts = count($parts);

        if ($numParts === 1) {
            // If there is only one part (no "/"), treat it as the filename
            $filename = $path;
            $fullPath = $filename;

            // Create the file if it doesn't exist
            if (!file_exists($fullPath)) {
                file_put_contents($fullPath, '');
            }

            return $fullPath;
        }

        // Initialize the root path
        $currentPath = '';

        for ($i = 0; $i < $numParts - 1; $i++) {
            // Append each part to the current path
            $currentPath .= $parts[$i] . '/';

            // Create the directory if it doesn't exist
            if (!is_dir($currentPath)) {
                mkdir($currentPath);
            }
        }

        // The last part is the filename
        $filename = $parts[$numParts - 1];

        // Create the file within the last directory
        $fullPath = $currentPath . $filename;
        if (!file_exists($fullPath)) {
            file_put_contents($fullPath, '');
        }

        return $fullPath;
    }

    private function create_file($path)
    {
        $parts = explode('/', $path);
        $filePath = '';
        $basePath = __DIR__; // You can specify your base directory here

        foreach ($parts as $part) {
            $filePath = rtrim($filePath, '/') . '/' . $part;

            if (!is_dir($basePath . $filePath)) {
                mkdir($basePath . $filePath);
            }
        }

        $fullFilePath = $basePath . $filePath . '.txt'; // Append the file extension you want

        if (!file_exists($fullFilePath)) {
            file_put_contents($fullFilePath, $fileContent);
            echo "File created successfully: $fullFilePath";
        } else {
            echo "File already exists: $fullFilePath";
        }
    }

    public function message(string $message, bool $die = false): void
    {
        echo "\n\r" . "[" . date("Y-m-d H:i:s") . "] - " . ucfirst($message) . PHP_EOL;

        if ($die) exit(1);
    }
}
