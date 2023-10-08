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
        // Check if the required arguments are provided
        if (count($args["args"]) < 1) {
            $this->message("Strike Usage: view <ViewName> <folderName> -<extension> - For creating view with {Blade: .blade.php, Twig: .twig, PHP: .php} extension. The viewName is compulsory, while others are Optional. Also Note: If not define <folederName> only <fileName> will be created. If not define -<extension> the default extension will be .php");
            exit(1);
        }

        $viewName = $args["args"][0];
        $folders = $args["args"][1] ?? null;

        if (isset($args["options"]["blade"])) {
            $extension = ".blade.php";
        } elseif (isset($args["options"]["twig"])) {
            $extension = ".twig";
        } else {
            $extension = ".php";
        }

        // Create the view folder's and file
        $this->createView($viewName, $folders, $extension);
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
                $this->message("Error: Unable to create the view directory.", true);
            }
        }

        /**
         * Check if View file already exists.
         */
        $viewFile = $viewDir . DIRECTORY_SEPARATOR . $viewName . $extension;
        if (file_exists($viewFile)) {
            $m = ucfirst($viewName . $extension);
            $this->message("View File {$m} already exists.", true);
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
            $this->message("Error: View Sample file not found in: " . $sample_file, true);


        $content = file_get_contents($sample_file);

        if (file_put_contents($viewFile, $content) === false) {
            $this->message("Error: Unable to create the view file.", true);
        }

        $m = ucfirst($viewName . $extension);

        $this->message("View file created successfully, FileName: '$m'!");
    }

    public function message(string $message, bool $die = false): void
    {
        echo "\n\r" . "[" . date("Y-m-d H:i:s") . "] - " . ucfirst($message) . PHP_EOL;

        if ($die) exit(1);
    }
}
