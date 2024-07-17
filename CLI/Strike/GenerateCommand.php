<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Generate commands =========
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CliActions;
use celionatti\Bolt\CLI\CommandInterface;

class GenerateCommand extends CliActions implements CommandInterface
{
    private $basePath;

    public function __construct()
    {
        $this->configure();
    }

    public function execute(array $args)
    {
        if (empty($args) || empty($args["args"])) {
            $this->listAvailableActions();
            return;
        }

        $action = $args["args"][0] ?? null;

        switch ($action) {
            case 'class':
                $this->createClass();
                break;
            case 'factory':
                $this->createFactory();
                break;
            case 'key':
                $this->generateKey();
                break;
            default:
                $this->message("Unknown Command. Usage: generate <action> (create)", true, true, 'warning');
        }
    }

    private function createClass()
    {
        $className = $this->prompt("Enter class name:");
        $namespace = $this->prompt("Enter namespace (optional):");
        $methods = $this->prompt("Enter methods (comma-separated, optional):");
        $properties = $this->prompt("Enter properties (comma-separated, optional):");

        $methodsArray = !empty($methods) ? explode(',', $methods) : [];
        $propertiesArray = !empty($properties) ? explode(',', $properties) : [];

        $this->generateClassFile($className, $namespace ?? '', $methodsArray, $propertiesArray);
    }

    private function generateClassFile(string $className, string $namespace, array $methods, array $properties)
    {
        $namespaceLine = !empty($namespace) ? "namespace $namespace;" : "";
        $classContent = "<?php\n\n$namespaceLine\n\nclass $className\n{\n";

        foreach ($properties as $property) {
            $property = trim($property);
            $classContent .= "    private \$$property;\n\n";
        }

        foreach ($methods as $method) {
            $method = trim($method);
            $classContent .= "    public function $method()\n    {\n        // TODO: Implement $method method.\n    }\n\n";
        }

        $classContent .= "}\n";

        $directory = !empty($namespace) ? str_replace('\\', '/', $namespace) : 'src';
        $filePath = "{$this->basePath}/$directory/$className.php";

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        if (file_exists($filePath)) {
            $this->message("Error: Class file already exists.", true, true, 'error');
            return;
        }

        file_put_contents($filePath, $classContent);

        $this->message("Class file created successfully at $filePath", false, true, 'info');
    }

    private function createFactory()
    {
        $factoryName = $this->prompt("Enter factory class name:");
        $modelName = $this->prompt("Enter model class name:");
        $namespace = $this->prompt("Enter namespace (optional):");

        $this->generateFactoryFile($factoryName, $modelName, $namespace ?? '');
    }

    private function generateFactoryFile(string $factoryName, string $modelName, string $namespace)
    {
        $factoryName = ucfirst($factoryName) . "Factory";
        $namespaceLine = !empty($namespace) ? "namespace $namespace;" : "";
        $classContent = "<?php\n\n$namespaceLine\n\nuse $modelName;\n\nclass $factoryName\n{\n";
        $classContent .= "    public static function create(array \$attributes = []): $modelName\n    {\n";
        $classContent .= "        return new $modelName(array_merge([\n";
        $classContent .= "            // Add default attributes here\n";
        $classContent .= "            'attribute1' => 'value1',\n";
        $classContent .= "            'attribute2' => 'value2',\n";
        $classContent .= "        ], \$attributes));\n";
        $classContent .= "    }\n";
        $classContent .= "}\n";

        $directory = "database/factories";
        $filePath = "{$this->basePath}/$directory/$factoryName.php";

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        if (file_exists($filePath)) {
            $this->message("Error: Factory file already exists.", true, true, 'error');
            return;
        }

        file_put_contents($filePath, $classContent);

        $this->message("Factory file created successfully at $filePath", false, true, 'info');
    }

    private function generateKey()
    {
        // Check if the migrations directory already exists.
        $BoltConfigDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "Configs" . DIRECTORY_SEPARATOR;

        if (!is_dir($BoltConfigDir)) {
            // Create the configs directory
            if (!mkdir($BoltConfigDir, 0755, true)) {
                $this->message("Error: Unable to create the configs directory.", false, true, 'error');
            }
        }

        /**
         * Check if load file already exists.
         */
        $BlotLoadFile = $BoltConfigDir . "load" . '.php';
        if (file_exists($BlotLoadFile)) {
            $m = ucfirst("load");
            $this->message("Config File {$m} already exists.", false, true, 'warning');
        }

        /**
         * Create the load file, if not existing.
         */
        touch($BlotLoadFile);

        /**
         * Customize the content of controller class here.
         * From the sample class.
         */
        $bolt_sample_file = __DIR__ . "/samples/bolt-load-sample.php";

        if (!file_exists($bolt_sample_file))
            $this->message("Error: Bolt Config load Sample file not found in: {$bolt_sample_file}", true, true, 'warning');


        $key = $this->create_random_key();

        $boltcontent = file_get_contents($bolt_sample_file);

        $boltcontent = str_replace("{KEY}", $key, $boltcontent);

        if (file_put_contents($BlotLoadFile, $boltcontent) === false) {
            $this->message("Error: Unable to create the bolt load file.", true, true, 'error');
        }

        $this->message("Config file created successfully, FileName: '$m'!");
    }

    private function create_random_key(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function listAvailableActions()
    {
        $this->message("Available Generate Commands:", false, false, 'info');
        $this->output("  \033[0;37mclass\033[0m: \033[0;36mGenerate a new class\033[0m", 1);
        $this->output("  \033[0;37mfactory\033[0m: \033[0;36mGenerate a new factory class\033[0m", 1);
        $this->output("  \033[0;37mkey\033[0m: \033[0;36mGenerate a new config key\033[0m", 1);
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
