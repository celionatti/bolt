<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - HasOne ======================
 * ====================================
 */

namespace celionatti\Bolt\Database\Relationships;

use celionatti\Bolt\Database\Model\DatabaseModel;
use celionatti\Bolt\BoltQueryBuilder\QueryBuilder;

class HasOne
{
    protected $parent;
    protected $related;
    protected $foreignKey;
    protected $localKey;

    public function __construct(DatabaseModel $parent, $related, $foreignKey, $localKey)
    {
        $this->parent = $parent;
        $this->related = new $related();
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function get()
    {
        $queryBuilder = new QueryBuilder($this->parent->getConnection());
        return $queryBuilder->select()
            ->from($this->related->getTable())
            ->where($this->foreignKey, '=', $this->parent->{$this->localKey})
            ->execute()[0];
    }

    public function getEagerLoadResults(array $keys)
    {
        $queryBuilder = new QueryBuilder($this->parent->getConnection());
        return $queryBuilder->select()
            ->from($this->related->getTable())
            ->whereIn($this->foreignKey, $keys)
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