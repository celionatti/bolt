<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - HasMany =====================
 * ====================================
 */

namespace celionatti\Bolt\Database\Relationships;

use celionatti\Bolt\Database\Model\DatabaseModel;

class HasMany
{
    protected $related;
    protected $parent;
    protected $foreignKey;
    protected $localKey;

    /**
     * HasMany constructor.
     *
     * @param string $related
     * @param DatabaseModel $parent
     * @param string $foreignKey
     * @param string $localKey
     */
    public function __construct(string $related, DatabaseModel $parent, string $foreignKey, string $localKey)
    {
        $this->related = new $related();
        $this->parent = $parent;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    /**
     * Get the related model instances.
     *
     * @return array
     */
    public function get(): array
    {
        return $this->related->where($this->foreignKey, '=', $this->parent->{$this->localKey})->get();
    }
}
