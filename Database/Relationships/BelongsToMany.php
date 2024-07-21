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
    protected $localKey;
    protected $relatedPivotKey;

    public function __construct($related, DatabaseModel $parent, $pivotTable, $foreignKey, $relatedKey, $localKey, $relatedPivotKey)
    {
        $this->related = new $related();
        $this->parent = $parent;
        $this->pivotTable = $pivotTable;
        $this->foreignKey = $foreignKey;
        $this->relatedKey = $relatedKey;
        $this->localKey = $localKey;
        $this->relatedPivotKey = $relatedPivotKey;
    }

    public function getPivotResults()
    {
        $queryBuilder = new QueryBuilder($this->parent->connection);
        $pivotResults = $queryBuilder->select($this->pivotTable . '.*')
            ->from($this->pivotTable)
            ->where($this->foreignKey, '=', $this->parent->{$this->localKey})
            ->execute();

        return $pivotResults;
    }

    public function get()
    {
        $pivotResults = $this->getPivotResults();
        $relatedIds = array_map(function ($pivot) {
            return $pivot->{$this->relatedPivotKey};
        }, $pivotResults);

        return $this->related->whereIn($this->relatedKey, $relatedIds)->get();
    }
}
