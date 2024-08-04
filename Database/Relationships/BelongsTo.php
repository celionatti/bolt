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

    /**
     * BelongsTo constructor.
     *
     * @param string $related
     * @param DatabaseModel $child
     * @param string $foreignKey
     * @param string $ownerKey
     */
    public function __construct(string $related, DatabaseModel $child, string $foreignKey, string $ownerKey)
    {
        $this->related = new $related();
        $this->child = $child;
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    /**
     * Get the related model instance.
     *
     * @return DatabaseModel|null
     */
    public function get(): ?DatabaseModel
    {
        return $this->related->where($this->ownerKey, '=', $this->child->{$this->foreignKey})->first();
    }
}
