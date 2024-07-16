<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Make commands =============
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CliActions;
use celionatti\Bolt\CLI\CommandInterface;

class MakeCommand extends CliActions implements CommandInterface
{
    public $basePath;

    private const CONTROLLER = 'controller';
    private const MODEL = 'model';
    private const MIGRATION = 'migration';
    private const VIEW = 'view';

    private const ACTIONS = [
        self::CONTROLLER => 'Create a new controller class',
        self::MODEL => 'Create a new model class',
        self::MIGRATION => 'Create a new migration class',
        self::VIEW => 'Create a new view file',
        // Add other actions and their descriptions here
    ];

    public function __construct()
    {
        $this->configure();
    }

    public function execute(array $args)
    {
        // Check if no action is provided
        if (empty($args) || empty($args["args"])) {
            $this->listAvailableActions();
            return;
        }

        $action = $args["args"][0] ?? null;

        if (isset($action) && $action === "model" && isset($args["options"]["m"])) {
            $this->createModel(true);
            return;
        }

        if ($action === null) {
            $this->listAvailableActions();
            return;
        }

        $this->callAction($action);
    }

    private function callAction($action)
    {
        // Check for the action type.
        switch ($action) {
            case self::CONTROLLER:
                $this->createController();
                break;
            case self::MODEL:
                $this->createModel(false);
                break;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
    }

    private function createController()
    {
        $controllerName = $this->prompt("Enter the controller name");

        if (empty($controllerName)) {
            $this->message("Error: Controller name cannot be empty.", true, true, "error");
            return;
        }

        $controllerDir = $this->basePath . DIRECTORY_SEPARATOR . "controllers" . DIRECTORY_SEPARATOR;

        if (!is_dir($controllerDir)) {
            if (!mkdir($controllerDir, 0755, true)) {
                $this->message("Error: Unable to create the controller directory.", true, true, "error");
                return;
            }
        }

        $controllerFile = $controllerDir . ucfirst($controllerName) . 'Controller.php';

        if (file_exists($controllerFile)) {
            $this->message("Error: Controller file already exists.", true, true, "warning");
            return;
        }

        $sampleFile = __DIR__ . "/samples/controller-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Error: Controller sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($controllerName) . 'Controller';

        $content = file_get_contents($sampleFile);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($controllerFile, $content) === false) {
            $this->message("Error: Unable to create the controller file.", true, true, "error");
            return;
        }

        $this->message("Controller file created successfully: '$controllerFile'", false, true, "info");
    }

    private function createModel($migration = false)
    {
        $modelOptions = [
            '1' => 'Empty Model',
            '2' => 'Basic Model'
        ];

        $migrationOptions = [
            '1' => 'No Migration',
            '2' => 'Create Migration'
        ];

        $modelName = $this->prompt("Enter the model name");

        if (empty($modelName)) {
            $this->message("Error: Model name cannot be empty.", true, true, "error");
            return;
        }

        $choice = $this->promptOptions("Choose a model to create:", $modelOptions, '1');

        // Check if the model directory already exists
        $modelDir = $this->basePath . DIRECTORY_SEPARATOR . "models" . DIRECTORY_SEPARATOR;

        if (!is_dir($modelDir)) {
            // Create the model directory
            if (!mkdir($modelDir, 0755, true)) {
                $this->message("Error: Unable to create the model directory.", true, true, "error");
                return;
            }
        }

        // Check if Model file already exists.
        $modelFile = $modelDir . ucfirst($modelName) . '.php';
        if (file_exists($modelFile)) {
            $m = ucfirst($modelName);
            $this->message("Model File {$m} already exists.", true, true, "warning");
            return;
        }

        // Create the model file, if not existing.
        touch($modelFile);

        // Customize the content of model class here from the sample class.
        if($choice === 2) {
            $sample_file = __DIR__ . "/samples/model/basic-sample.php";
        } else {
            $sample_file = __DIR__ . "/samples/model/empty-sample.php";
        }

        if (!file_exists($sample_file)) {
            $this->message("Error: Model Sample file not found in: {$sample_file}", true, true, "error");
            return;
        }

        $class_name = $this->rename_camel_case($modelName);

        $table_name = strtolower($class_name);

        $content = file_get_contents($sample_file);
        $content = str_replace("{TABLENAME}", $table_name, $content);
        $content = str_replace("{CLASSNAME}", $class_name, $content);

        if (file_put_contents($modelFile, $content) === false) {
            $this->message("Error: Unable to create the model file.", true, true, "error");
            return;
        }

        // Create Migration.
        $migrationOpt = $this->promptOptions("Create Migration for Model?:", $migrationOptions, '1');

        if ($migrationOpt === '2') {
            $this->migrationFile($modelName);
        }

        $this->message("Model file created successfully: '$modelName'", false, true, "info");
    }

    private function migrationFile($modelName)
    {
        // Check if the model directory already exists
        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            // Create the model directory
            if (!mkdir($migrationDir, 0755, true)) {
                $this->message("Error: Unable to create the migration directory.", true, true, "error");
                return;
            }
        }

        // Check if Migration file already exists.
        $migrationFile = $migrationDir . date("Y_m_d_His_") . 'create_' . strtolower($modelName) . '_table' . '.php';
        if (file_exists($migrationFile)) {
            $mg = ucfirst($modelName);
            $this->message("Migration File {$mg} already exists.", true, true, "warning");
            return;
        }

        // Create the migration file
        if (!touch($migrationFile)) {
            $this->message("Error: Unable to create the migration file.", true, true, "error");
            return;
        }

        // Customize the content of migration class here from the sample class.
        $sample_file = __DIR__ . "/samples/migration/migration-sample.php";

        if (!file_exists($sample_file)) {
            $this->message("Error: Migration Sample file not found in: {$sample_file}", true, true, "error");
            return;
        }

        $class_name = ucfirst($modelName);
        $table_name = strtolower($modelName);

        $content = file_get_contents($sample_file);
        $content = str_replace("{TABLENAME}", $table_name, $content);
        $content = str_replace("{CLASSNAME}", $class_name, $content);

        if (file_put_contents($migrationFile, $content) === false) {
            $this->message("Error: Unable to write content to the migration file.", true, true, "error");
            return;
        }

        $this->message("Migration file created successfully: '$migrationFile'", false, true, "info");
    }

    private function listAvailableActions()
    {
        $this->message("Available Make Commands:", false, false, 'info');
        foreach (self::ACTIONS as $action => $description) {
            $this->output("  \033[0;37m{$action}\033[0m: \033[0;36m{$description}\033[0m", 1);
        }
    }

    private function configure()
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
                return;
            }
        }

        $this->basePath = $currentDirectory;
    }
}
