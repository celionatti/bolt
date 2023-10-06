<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - View commands =============
 * ====================================
 */

namespace Bolt\Bolt\CLI\Strike;

use Bolt\Bolt\CLI\StrikeCommands;

class ViewCommand extends StrikeCommands
{
    public function execute($args, $options)
    {
        // Logic for creating models, generating migrations, etc.
        $modelName = $args[0] ?? null;
        dd($modelName);
        // ... (implementation specific to the 'model' command)
    }
}