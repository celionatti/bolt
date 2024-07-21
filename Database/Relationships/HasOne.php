<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - HasOne ======================
 * ====================================
 */

namespace celionatti\Bolt\Database\Relationships;

use celionatti\Bolt\Database\Model\DatabaseModel;

class HasOne
{
    protected $related;
    protected $parent;
    protected $foreignKey;
    protected $localKey;

    public function __construct($related, DatabaseModel $parent, $foreignKey, $localKey)
    {
        $this->related = new $related();
        $this->parent = $parent;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function get()
    {
        return $this->related->where($this->foreignKey, $this->parent->{$this->localKey})->first();
    }
}