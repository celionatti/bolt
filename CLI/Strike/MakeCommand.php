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
    private const CONTROLLER = 'controller';
    private const MODEL = 'model';
    private const MIGRATION = 'migration';
    private const VIEW = 'view';
    private const LAYOUT = 'layout';
    private const MIDDLEWARE = 'middleware';
    private const SERVICE = 'service';
    private const COMPONENT = 'component';

    private const ACTIONS = [
        self::CONTROLLER => 'Create a new controller class',
        self::MODEL => 'Create a new model class',
        self::MIGRATION => 'Create a new migration class',
        self::VIEW => 'Create a new view file',
        self::LAYOUT => 'Create a new layout file',
        self::MIDDLEWARE => 'Create a new middleware class',
        self::SERVICE => 'Create a new service class',
        self::COMPONENT => 'Create a new component'
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
            case self::VIEW:
                $this->createView();
                break;
            case self::LAYOUT:
                $this->createLayout();
                break;
            case self::MIDDLEWARE:
                $this->createMiddleware();
                break;
            case self::SERVICE:
                $this->createService();
                break;
            case self::COMPONENT:
                $this->createComponent();
                break;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
    }

    private function createController()
    {
        $controllerName = $this->prompt("Enter the controller name");

        if (empty($controllerName)) {
            $this->message("Controller name cannot be empty.", true, true, "warning");
            return;
        }

        $controllerDir = $this->basePath . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "controllers" . DIRECTORY_SEPARATOR;

        if (!is_dir($controllerDir)) {
            if (!mkdir($controllerDir, 0755, true)) {
                $this->message("Unable to create the controller directory.", true, true, "error");
                return;
            }
        }

        $controllerFile = $controllerDir . ucfirst($controllerName) . 'Controller.php';

        if (file_exists($controllerFile)) {
            $this->message("Controller file already exists.", true, true, "warning");
            return;
        }

        $sampleFile = __DIR__ . "/samples/controller/controller-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Controller sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($controllerName) . 'Controller';

        $content = file_get_contents($sampleFile);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($controllerFile, $content) === false) {
            $this->message("Unable to create the controller file.", true, true, "error");
            return;
        }

        $this->message("Controller file {$className} created successfully", false, true, "created");
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
            $this->message("Model name cannot be empty.", true, true, "error");
            return;
        }

        $choice = $this->promptOptions("Choose a model to create:", $modelOptions, '1');

        // Check if the model directory already exists
        $modelDir = $this->basePath . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "models" . DIRECTORY_SEPARATOR;

        if (!is_dir($modelDir)) {
            // Create the model directory
            if (!mkdir($modelDir, 0755, true)) {
                $this->message("Unable to create the model directory.", true, true, "error");
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
        if ($choice === 2) {
            $sample_file = __DIR__ . "/samples/model/basic-sample.php";
        } else {
            $sample_file = __DIR__ . "/samples/model/empty-sample.php";
        }

        if (!file_exists($sample_file)) {
            $this->message("Model Sample file not found in: {$sample_file}", true, true, "error");
            return;
        }

        $class_name = $this->rename_camel_case($modelName);

        $table_name = strtolower($class_name);

        $content = file_get_contents($sample_file);
        $content = str_replace("{TABLENAME}", $table_name, $content);
        $content = str_replace("{CLASSNAME}", $class_name, $content);

        if (file_put_contents($modelFile, $content) === false) {
            $this->message("Unable to create the model file.", true, true, "error");
            return;
        }

        // Create Migration.
        $migrationOpt = $this->promptOptions("Create Migration for Model?:", $migrationOptions, '1');

        if ($migrationOpt === '2') {
            $this->migrationFile($modelName);
        }

        $this->message("Model file {$class_name} created successfully", false, true, "info");
    }

    private function migrationFile($modelName)
    {
        // Check if the model directory already exists
        $migrationDir = $this->basePath . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "migrations" . DIRECTORY_SEPARATOR;

        if (!is_dir($migrationDir)) {
            // Create the model directory
            if (!mkdir($migrationDir, 0755, true)) {
                $this->message("Unable to create the migration directory.", true, true, "error");
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
            $this->message("Unable to create the migration file.", true, true, "error");
            return;
        }

        // Customize the content of migration class here from the sample class.
        $sample_file = __DIR__ . "/samples/migration/migration-sample.php";

        if (!file_exists($sample_file)) {
            $this->message("Migration Sample file not found in: {$sample_file}", true, true, "error");
            return;
        }

        $class_name = ucfirst($modelName);
        $table_name = strtolower($modelName);

        $content = file_get_contents($sample_file);
        $content = str_replace("{TABLENAME}", $table_name, $content);
        $content = str_replace("{CLASSNAME}", $class_name, $content);

        if (file_put_contents($migrationFile, $content) === false) {
            $this->message("Unable to write content to the migration file.", true, true, "error");
            return;
        }

        $this->message("Migration file {$table_name} created successfully", false, true, "info");
    }

    private function createView()
    {
        $filename = $this->prompt("Enter the view filename");

        if (empty($filename)) {
            $this->message("Filename cannot be empty.", true, true, "error");
            return;
        }

        $createInFolder = $this->promptOptions("Do you want to create the view in a specific folder?", [
            '1' => 'Yes',
            '2' => 'No'
        ], '2');

        $folders = null;
        if ($createInFolder === '1') {
            $folders = $this->prompt("Enter the folder name");
        }

        $extension = $this->promptOptions("Choose the view file extension:", [
            '1' => '.php',
            '2' => '.blade.php',
            '3' => '.twig'
        ], '1');

        $extension = match ($extension) {
            '1' => '.php',
            '2' => '.blade.php',
            '3' => '.twig',
            default => '.php',
        };

        // Determine where to create folders based on the extension
        if ($extension === ".blade.php") {
            $viewDir = $this->basePath . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "blade-views" . DIRECTORY_SEPARATOR . $folders;
        } elseif ($extension === ".twig") {
            $viewDir = $this->basePath . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "twig-views" . DIRECTORY_SEPARATOR . $folders;
        } else {
            $viewDir = $this->basePath . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . $folders;
        }

        if (!is_dir($viewDir)) {
            if (!mkdir($viewDir, 0755, true)) {
                $this->message("Unable to create the view directory.", true, true, "error");
                return;
            }
        }

        $viewFile = $viewDir . DIRECTORY_SEPARATOR . $filename . $extension;
        if (file_exists($viewFile)) {
            $this->message("View File {$filename}{$extension} already exists.", true, true, "warning");
            return;
        }

        touch($viewFile);

        // Customize the content of the view file here
        if ($extension === ".blade.php") {
            $sampleFile = __DIR__ . "/samples/view/blade-view-sample.php";
        } elseif ($extension === ".twig") {
            $sampleFile = __DIR__ . "/samples/view/twig-view-sample.php";
        } else {
            $sampleFile = __DIR__ . "/samples/view/view-sample.php";
        }

        if (!file_exists($sampleFile)) {
            $this->message("View Sample file not found in: {$sampleFile}", true, true, "error");
            return;
        }

        $content = file_get_contents($sampleFile);

        if (file_put_contents($viewFile, $content) === false) {
            $this->message("Unable to create the view file.", true, true, "error");
            return;
        }

        $this->message("View file {$filename} created successfully", false, true, "info");
    }

    private function createLayout()
    {
        $filename = $this->prompt("Enter the layout filename");

        if (empty($filename)) {
            $this->message("Filename cannot be empty.", true, true, "error");
            return;
        }

        $createInFolder = $this->promptOptions("Do you want to create the layout in a specific folder?", [
            '1' => 'Yes',
            '2' => 'No'
        ], '2');

        $folders = null;
        if ($createInFolder === '1') {
            $folders = $this->prompt("Enter the folder name");
        }

        $extension = $this->promptOptions("Choose the layout file extension:", [
            '1' => '.php',
            '2' => '.blade.php',
            '3' => '.twig'
        ], '1');

        $extension = match ($extension) {
            '1' => '.php',
            '2' => '.blade.php',
            '3' => '.twig',
            default => '.php',
        };

        // Determine where to create folders based on the extension
        if ($extension === ".blade.php") {
            $layoutDir = $this->basePath . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "layouts" . DIRECTORY_SEPARATOR . "blade-views" . DIRECTORY_SEPARATOR . $folders;
        } elseif ($extension === ".twig") {
            $layoutDir = $this->basePath . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "layouts" . DIRECTORY_SEPARATOR . "twig-views" . DIRECTORY_SEPARATOR . $folders;
        } else {
            $layoutDir = $this->basePath . DIRECTORY_SEPARATOR . "resources" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . "layouts" . DIRECTORY_SEPARATOR . $folders;
        }

        if (!is_dir($layoutDir)) {
            if (!mkdir($layoutDir, 0755, true)) {
                $this->message("Unable to create the layout directory.", true, true, "error");
                return;
            }
        }

        $layoutFile = $layoutDir . DIRECTORY_SEPARATOR . $filename . $extension;
        if (file_exists($layoutFile)) {
            $this->message("Layout File {$filename}{$extension} already exists.", true, true, "warning");
            return;
        }

        touch($layoutFile);

        // Customize the content of the view file here
        $sampleFile = __DIR__ . "/samples/view/layout-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Layout Sample file not found in: {$sampleFile}", true, true, "error");
            return;
        }

        $content = file_get_contents($sampleFile);

        if (file_put_contents($layoutFile, $content) === false) {
            $this->message("Unable to create the layout file.", true, true, "error");
            return;
        }

        $this->message("Layout file {$filename} created successfully", false, true, "info");
    }

    private function createMiddleware()
    {
        $middlewareName = $this->prompt("Enter the middleware name");

        if (empty($middlewareName)) {
            $this->message("Middleware name cannot be empty.", true, true, "error");
            return;
        }

        $middlewareDir = $this->basePath . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "middlewares";

        if (!is_dir($middlewareDir)) {
            if (!mkdir($middlewareDir, 0755, true)) {
                $this->message("Unable to create the middleware directory.", true, true, "error");
                return;
            }
        }

        $middlewareFile = $middlewareDir . ucfirst($middlewareName) . 'Middleware.php';

        if (file_exists($middlewareFile)) {
            $this->message("Middleware file already exists.", true, true, "warning");
            return;
        }

        $sampleFile = __DIR__ . "/samples/middleware/middleware-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Middleware sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($middlewareName) . 'Middleware';

        $content = file_get_contents($sampleFile);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($middlewareFile, $content) === false) {
            $this->message("Unable to create the middleware file.", true, true, "error");
            return;
        }

        $this->message("Middleware file {$middlewareName} created successfully", false, true, "info");
    }

    private function createService()
    {
        $serviceName = $this->prompt("Enter service provider name");

        if (empty($serviceName)) {
            $this->message("Service name cannot be empty.", true, true, "error");
            return;
        }

        $serviceDir = $this->basePath . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "providers" . DIRECTORY_SEPARATOR;

        if (!is_dir($serviceDir)) {
            if (!mkdir($serviceDir, 0755, true)) {
                $this->message("Unable to create the services directory.", true, true, "error");
                return;
            }
        }

        $serviceFile = $serviceDir . ucfirst($serviceName) . 'ServiceProvider.php';

        if (file_exists($serviceFile)) {
            $this->message("Service file already exists.", true, true, "warning");
            return;
        }

        $sampleFile = __DIR__ . "/samples/service/service-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Service sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($serviceName) . 'ServiceProvider';

        $content = file_get_contents($sampleFile);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($serviceFile, $content) === false) {
            $this->message("Unable to create the service file.", true, true, "error");
            return;
        }

        $this->message("Service file {$className} created successfully", false, true, "info");
    }

    private function createComponent()
    {
        $componentName = $this->prompt("Enter the component name");

        if (empty($componentName)) {
            $this->message("Component name cannot be empty.", true, true, "error");
            return;
        }

        $componentDir = $this->basePath . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . "components";

        if (!is_dir($componentDir)) {
            if (!mkdir($componentDir, 0755, true)) {
                $this->message("Unable to create the components directory.", true, true, "error");
                return;
            }
        }

        $componentFile = $componentDir . ucfirst($componentName) . 'Component.php';

        if (file_exists($componentFile)) {
            $this->message("Component file already exists.", true, true, "warning");
            return;
        }

        $sampleFile = __DIR__ . "/samples/component-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Component sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($componentName) . 'Component';

        $content = file_get_contents($sampleFile);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($componentFile, $content) === false) {
            $this->message("Unable to create the component file.", true, true, "error");
            return;
        }

        $this->message("Component file {$className} created successfully", false, true, "info");
    }

    private function listAvailableActions()
    {
        $this->message("Available Make Commands:", false, false, 'info');
        foreach (self::ACTIONS as $action => $description) {
            $this->output("  \033[0;37m{$action}\033[0m: \033[0;36m{$description}\033[0m", 1);
        }
    }
}
