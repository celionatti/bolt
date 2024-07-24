<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Seeder commands ===========
 * ====================================
 */

namespace celionatti\Bolt\CLI\Strike;

use celionatti\Bolt\CLI\CliActions;
use celionatti\Bolt\CLI\CommandInterface;

class SeederCommand extends CliActions implements CommandInterface
{
    private const MIGRATE = 'migrate';
    private const ROLLBACK = 'rollback';
    private const SEED = 'seed';
    private const MAKE_MIGRATION = 'make:migration';

    private const ACTIONS = [
        self::MIGRATE => 'Run database migrations',
        self::ROLLBACK => 'Rollback the last database migration',
        self::SEED => 'Seed the database with data',
        self::MAKE_MIGRATION => 'Create a new migration file',
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
            default:
                $this->message("Unknown Command - You can check help or docs to see the list of commands and methods of calling.", true, true, 'warning');
        }
    }

    private function seedDatabase()
    {
        // Logic to seed the database
        $this->message("Seeding the database with data...", false, true, "info");
        // Add your seeding logic here
    }

    private function listAvailableActions()
    {
        $this->message("Available Database Commands:", false, false, 'info');
        foreach (self::ACTIONS as $action => $description) {
            $this->output("  \033[0;37m{$action}\033[0m: \033[0;36m{$description}\033[0m", 1);
        }
    }
}
