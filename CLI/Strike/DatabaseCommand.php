<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Database commands ===========
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CliActions;
use celionatti\Bolt\CLI\CommandInterface;

class DatabaseCommand extends CliActions implements CommandInterface
{
    private const SEED = 'seed';
    private const CREATE_SEED = 'create-seed';
    private const CREATE_FACTORY = 'create-factory';

    private const ACTIONS = [
        self::SEED => 'Seed the database with data',
        self::CREATE_SEED => 'Create a new seed file',
        self::CREATE_FACTORY => 'Create a new factory file'
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

        if ($action === null) {
            $this->listAvailableActions();
            return;
        }

        $this->callAction($action, array_slice($args["args"], 1));
    }

    private function callAction($action, $params)
    {
        // Check for the action type.
        switch ($action) {
            case self::SEED:
                $this->seedDatabase();
                break;
            case self::CREATE_SEED:
                $this->createSeedFile($params);
                break;
            case self::CREATE_FACTORY:
                $this->createFactoryFile($params);
                break;
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
    }

    private function seedDatabase()
    {
        $seederDir = $this->basePath . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "seeds";

        if (!is_dir($seederDir)) {
            $this->message("Error: Seeder directory does not exist.", true, true, "error");
            return;
        }

        $seedFiles = glob($seederDir . DIRECTORY_SEPARATOR . '*.php');

        if (empty($seedFiles)) {
            $this->message("No seed files found in the seeder directory.", false, true, "info");
            return;
        }

        foreach ($seedFiles as $seedFile) {
            require_once $seedFile;
            $className = basename($seedFile, '.php');

            if (!class_exists($className)) {
                $this->message("Error: Seeder class '$className' not found in '$seedFile'.", true, true, "error");
                continue;
            }

            $seeder = new $className();

            if (!method_exists($seeder, 'run')) {
                $this->message("Error: Seeder class '$className' does not have a 'run' method.", true, true, "error");
                continue;
            }

            $this->message("Seeding with '$className'...", false, true, "info");
            $seeder->run();
            $this->message("Seeded '$className' successfully.", false, true, "info");
        }
    }

    private function createSeedFile($params)
    {
        $seederName = $params[0] ?? '';

        if (empty($seederName)) {
            $this->message("Seeder name cannot be empty.", true, true, "error");
            return;
        }

        $seedersDir = $this->basePath . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "seeders" . DIRECTORY_SEPARATOR;

        if (!is_dir($seedersDir)) {
            if (!mkdir($seedersDir, 0755, true)) {
                $this->message("Unable to create the seeders directory.", true, true, "error");
                return;
            }
        }

        $seederFile = $seedersDir . ucfirst($seederName) . 'Seeder.php';

        if (file_exists($seederFile)) {
            $this->message("Seeder file already exists.", true, true, "warning");
            return;
        }

        $sampleFile = __DIR__ . "/samples/database/seeder-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Seeder sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($seederName) . 'Seeder';

        $content = file_get_contents($sampleFile);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($seederFile, $content) === false) {
            $this->message("Unable to create the seeder file.", true, true, "error");
            return;
        }

        $this->message("Seeder file {$seederName} successfully Created", false, true, "created");
    }

    private function createFactoryFile($params)
    {
        $factoryName = $params[0] ?? '';

        if (empty($factoryName)) {
            $this->message("Factory name cannot be empty.", true, true, "error");
            return;
        }

        $factoriesDir = $this->basePath . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR . "factories" . DIRECTORY_SEPARATOR;

        if (!is_dir($factoriesDir)) {
            if (!mkdir($factoriesDir, 0755, true)) {
                $this->message("Unable to create the factories directory.", true, true, "error");
                return;
            }
        }

        $factoryFile = $factoriesDir . ucfirst($factoryName) . 'Factory.php';

        if (file_exists($factoryFile)) {
            $this->message("Factory file already exists.", true, true, "warning");
            return;
        }

        $sampleFile = __DIR__ . "/samples/database/factories-sample.php";

        if (!file_exists($sampleFile)) {
            $this->message("Factory sample file not found.", true, true, "error");
            return;
        }

        $className = ucfirst($factoryName) . 'Factory';

        $content = file_get_contents($sampleFile);
        $content = str_replace("{CLASSNAME}", $className, $content);

        if (file_put_contents($factoryFile, $content) === false) {
            $this->message("Unable to create the factory file.", true, true, "error");
            return;
        }

        $this->message("Factory file {$factoryName} successfully Created", false, true, "created");
    }


    private function listAvailableActions()
    {
        $this->message("Available Database Commands:", false, false, 'info');
        foreach (self::ACTIONS as $action => $description) {
            $this->output("  \033[0;37m{$action}\033[0m: \033[0;36m{$description}\033[0m", 1);
        }
    }
}
