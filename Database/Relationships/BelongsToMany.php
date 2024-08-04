<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - BelongsToMany ===============
 * ====================================
 */

namespace celionatti\Bolt\Database\Relationships;

use celionatti\Bolt\Database\Model\DatabaseModel;
use celionatti\Bolt\BoltQueryBuilder\QueryBuilder;

class BelongsToMany
{
    protected $related;
    protected $parent;
    protected $pivotTable;
    protected $foreignKey;
    protected $relatedKey;

    public function __construct(DatabaseModel $parent, $related, $pivotTable, $foreignKey, $relatedKey)
    {
        $this->related = new $related();
        $this->parent = $parent;
        $this->pivotTable = $pivotTable;
        $this->foreignKey = $foreignKey;
        $this->relatedKey = $relatedKey;
    }

    protected function getPivotResults()
    {
        $queryBuilder = new QueryBuilder($this->parent->getConnection());
        return $queryBuilder->select('*')
            ->from($this->pivotTable)
            ->where($this->foreignKey, '=', $this->parent->{$this->parent->getPrimaryValue()})
            ->execute();
    }

    public function get()
    {
        $pivotResults = $this->getPivotResults();
        $relatedIds = array_column($pivotResults, $this->relatedKey);

        return $this->related->whereIn($this->related->getPrimaryValue(), $relatedIds)->get();
    }
}
