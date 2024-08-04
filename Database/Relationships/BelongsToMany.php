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
    protected $parent;
    protected $related;
    protected $pivotTable;
    protected $foreignPivotKey;
    protected $relatedPivotKey;
    protected $parentKey;
    protected $relatedKey;

    public function __construct(DatabaseModel $parent, $related, $pivotTable, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey)
    {
        $this->parent = $parent;
        $this->related = new $related();
        $this->pivotTable = $pivotTable;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
    }

    public function get()
    {
        $queryBuilder = new QueryBuilder($this->parent->getConnection());
        $results = $queryBuilder->select()
            ->from($this->pivotTable)
            ->where($this->foreignPivotKey, '=', $this->parent->{$this->parentKey})
            ->execute();

        $relatedIds = array_column($results, $this->relatedPivotKey);

        if (empty($relatedIds)) {
            return [];
        }

        return $this->related->whereIn($this->relatedKey, $relatedIds)->get();
    }

    public function attach($relatedId)
    {
        $queryBuilder = new QueryBuilder($this->parent->getConnection());
        $data = [
            $this->foreignPivotKey => $this->parent->getPrimaryValue(),
            $this->relatedKey => $relatedId
        ];
        $queryBuilder->insert($this->pivotTable, $data)->execute();
    }

    public function getEagerLoadResults(array $keys)
    {
        $queryBuilder = new QueryBuilder($this->parent->getConnection());
        $pivotResults = $queryBuilder->select()
            ->from($this->pivotTable)
            ->whereIn($this->foreignPivotKey, $keys)
            ->execute();

        $relatedIds = array_column($pivotResults, $this->relatedPivotKey);

        if (empty($relatedIds)) {
            return [];
        }

        $relatedResults = $this->related->whereIn($this->relatedKey, $relatedIds)->get();

        return [
            'pivot' => $pivotResults,
            'related' => $relatedResults,
        ];
    }

    public function getRelatedModel()
    {
        return $this->related;
    }

    public function getForeignKey()
    {
        return $this->foreignPivotKey;
    }
    // protected $parent;
    // protected $related;
    // protected $pivotTable;
    // protected $foreignKey;
    // protected $relatedKey;

    // public function __construct(DatabaseModel $parent, $related, $pivotTable, $foreignKey, $relatedKey)
    // {
    //     $this->parent = $parent;
    //     $this->related = new $related();
    //     $this->pivotTable = $pivotTable;
    //     $this->foreignKey = $foreignKey;
    //     $this->relatedKey = $relatedKey;
    // }

    // public function get()
    // {
    //     $queryBuilder = new QueryBuilder($this->parent->getConnection());
    //     $results = $queryBuilder->select()
    //         ->from($this->pivotTable)
    //         ->where($this->foreignKey, '=', $this->parent->{$this->parent->getPrimaryValue()})
    //         ->execute();

    //     $relatedIds = array_column($results, $this->relatedKey);
    //     return $this->related->whereIn($this->related->getPrimaryKey(), $relatedIds)->get();
    // }

    // public function attach($relatedId)
    // {
    //     $queryBuilder = new QueryBuilder($this->parent->getConnection());
    //     $data = [
    //         $this->foreignKey => $this->parent->getPrimaryValue(),
    //         $this->relatedKey => $relatedId
    //     ];
    //     $queryBuilder->insert($this->pivotTable, $data)->execute();
    // }
}
