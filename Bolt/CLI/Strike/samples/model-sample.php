<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============        ===============
 * ===== {CLASSNAME} Model
 * ===============        ===============
 * ======================================
 */

namespace Bolt\models;

use Bolt\Bolt\Database\DatabaseModel;

class {CLASSNAME} extends DatabaseModel
{
    /**
     * here is how to define const
     */
    const GREETING = "Bolt {CLASSNAME}";

    /**
     * Also for defining variables
     */
    public string $name = "Bolt";
    public string $version;

    public static function tableName():string
    {
        return "{TABLENAME}";
    }

    public function others()
    {
        
    }
}