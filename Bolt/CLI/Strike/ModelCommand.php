<?php

declare(strict_types=1);

/**
 * ====================================
 * Strike - Model commands ============
 * ====================================
 */

namespace Bolt\Bolt\CLI\Strike;

use Bolt\Bolt\CLI\StrikeCommands;

class ModelCommand extends StrikeCommands
{
    public function execute($args, $options)
    {
        // Logic for creating models, generating migrations, etc.
        $modelName = $args[0] ?? null;
        dd($modelName);
        // ... (implementation specific to the 'model' command)
    }
}