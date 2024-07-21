<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - BelongsTo ===================
 * ====================================
 */

namespace celionatti\Bolt\Database\Relationships;

use celionatti\Bolt\Database\Model\DatabaseModel;

class BelongsTo
{
    protected $related;
    protected $child;
    protected $foreignKey;
    protected $ownerKey;

    public function __construct($related, DatabaseModel $child, $foreignKey, $ownerKey)
    {
        $this->related = new $related();
        $this->child = $child;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    public function get()
    {
        return $this->related->where($this->ownerKey, $this->child->{$this->foreignKey})->first();
    }
}
