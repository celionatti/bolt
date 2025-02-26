<?php

declare(strict_types=1);

/**
 * ======================================
 * ===============        ===============
 * ===== {{CLASSNAME}} Model
 * ===============        ===============
 * ======================================
 */

namespace {{NAMESPACE}};

use celionatti\Bolt\Model\Model;

class {{CLASSNAME}} extends Model
{
    /**
     * By default the table name is the the classname with s added.
     * But if different you can define it.
     */
    protected $table = "{{TABLENAME}}";
}