<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - BelongsTo ===================
 * ====================================
 */

namespace celionatti\Bolt\Database\Relationships;

use celionatti\Bolt\Database\Model\DatabaseModel;
use celionatti\Bolt\BoltQueryBuilder\QueryBuilder;

class BelongsTo
{
    protected $parent;
    protected $related;
    protected $foreignKey;
    protected $ownerKey;

    public function __construct(DatabaseModel $parent, $related, $foreignKey, $ownerKey)
    {
        $this->parent = $parent;
        $this->related = new $related();
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    public function get()
    {
        $queryBuilder = new QueryBuilder($this->parent->getConnection());
        return $queryBuilder->select()
            ->from($this->related->getTable())
            ->where($this->ownerKey, '=', $this->parent->{$this->foreignKey})
            ->execute()[0];
    }

    public function getEagerLoadResults(array $keys)
    {
        $queryBuilder = new QueryBuilder($this->parent->getConnection());
        return $queryBuilder->select()
            ->from($this->related->getTable())
            ->whereIn($this->ownerKey, $keys)
            ->execute();
    }

    public function getRelatedModel()
    {
        return $this->related;
    }

    public function getForeignKey()
    {
        return $this->foreignKey;
    }
}
