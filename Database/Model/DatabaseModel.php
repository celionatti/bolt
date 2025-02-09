<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Database Model ==============
 * ====================================
 */

namespace celionatti\Bolt\Database\Model;

use celionatti\Bolt\Database\Database;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\BoltQueryBuilder\QueryBuilder;
use celionatti\Bolt\Database\Relationships\HasOne;
use celionatti\Bolt\Database\Relationships\HasMany;
use celionatti\Bolt\Database\Relationships\BelongsTo;
use celionatti\Bolt\Database\Exception\DatabaseException;
use celionatti\Bolt\Database\Relationships\BelongsToMany;

abstract class DatabaseModel
{
    protected $connection;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $guarded = [];
    protected $hidden = [];
    protected $casts = [];
    protected $attributes = [];
    protected $rules = [];
    protected $exists = false;
    protected $relations = [];

    public function __construct()
    {
        $this->connection = Database::getInstance()->getConnection();
        $this->setTable();
    }

    private function database()
    {
        return Database::getInstance();
    }

    private function setTable()
    {
        if (!$this->table) {
            $className = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's';
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function query(string $query, array $params = [], string $data_type = 'object')
    {
        return $this->database()->query($query, $params, $data_type);
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getPrimaryValue()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    // public function with(array $relations)
    // {
    //     $this->relations = $relations;
    //     return $this;
    // }
    public function with($relations)
    {
        if (is_string($relations)) {
            $relations = [$relations];
        }

        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $this->relations[] = $relation;
            }
        }

        return $this;
    }


    public function create(array $attributes): ?self
    {
        $attributes = $this->filterAttributes($attributes);
        $attributes = $this->castAttributes($attributes);

        try {
            return $this->saveAttributes($attributes);
        } catch (\Exception $e) {
            // Handle the exception or log it.
            throw new DatabaseException("Failed to create record: {$e->getMessage()}", $e->getCode(), "info");
        }
    }

    public function update(array $attributes, $id, $key = null): ?self
    {
        $attributes = $this->filterAttributes($attributes);
        $attributes = $this->castAttributes($attributes);
        try {
            return $this->saveAttributes($attributes, $id, $key ?? $this->primaryKey);
        } catch (\Exception $e) {
            // Handle the exception or log it.
            throw new DatabaseException("Failed to update record: {$e->getMessage()}", $e->getCode(), "info");
        }
    }

    private function saveAttributes(array $attributes, $id = null, $key = null): ?self
    {
        $queryBuilder = new QueryBuilder($this->connection);
        if ($id) {
            $queryBuilder->update($this->table, $attributes)->where($key ?? $this->primaryKey, '=', $id)->execute();
            return $this->find($id);
        } else {
            $queryBuilder->insert($this->table, $attributes)->execute();
            return $this->findById($this->connection->lastInsertId());
        }
    }

    public function findById($id): ?self
    {
        return $this->findBy(['id' => $id]);
    }

    public function find($id): ?self
    {
        return $this->findBy([$this->primaryKey => $id]);
    }

    public function findBy(array $conditions): ?self
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $queryBuilder->select()->from($this->table);

        foreach ($conditions as $column => $value) {
            $queryBuilder->where($column, '=', $value);
        }

        $result = $queryBuilder->execute();
        if ($result) {
            $this->attributes = (array)$result[0];
            $this->attributes = $this->castAttributes($this->attributes);
            $this->exists = true;
            return $this;
        }
        return null;
    }

    public function get(): array
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $results = $queryBuilder->select()->from($this->table)->execute();

        if (!empty($this->relations)) {
            $results = $this->eagerLoadRelations($results);
        }

        return $results;
    }

    public function first(): ?self
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $result = $queryBuilder->select()->from($this->table)->limit(1)->execute();
        if ($result) {
            $this->attributes = (array)$result[0];
            $this->attributes = $this->castAttributes($this->attributes);
            $this->exists = true;
            return $this;
        }
        return null;
    }

    public function delete($id, $key = null): bool
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $queryBuilder->delete($this->table)->where($key ?? $this->primaryKey, '=', $id)->execute();
        return true;
    }

    public static function allBy($column, $value): array
    {
        $instance = new static();
        $queryBuilder = new QueryBuilder($instance->connection);
        return $queryBuilder->select()->from($instance->table)->where($column, '=', $value)->execute();
    }

    public static function all(): array
    {
        $instance = new static();
        $queryBuilder = new QueryBuilder($instance->connection);
        return $queryBuilder->select()->from($instance->table)->execute();
    }

    public static function paginate(int $page = 1, int $itemsPerPage = 15, array $conditions = [], $order = []): array
    {
        $instance = new static();
        $queryBuilder = new QueryBuilder($instance->connection);

        $offset = ($page - 1) * $itemsPerPage;

        // Apply conditions if provided
        $queryBuilder->select()->from($instance->table);

        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                $queryBuilder->where($column, '=', $value);
            }
        }

        $queryBuilder->limit($itemsPerPage)->offset($offset);

        if (!empty($order)) {
            foreach ($order as $column => $value) {
                $queryBuilder->orderBy($column, $value);
            }
        }

        $results = $queryBuilder->execute();

        // Calculate total items considering the conditions
        $countQuery = (new QueryBuilder($instance->connection))->select("COUNT(*) as total")->from($instance->table);

        if (!empty($conditions)) {
            foreach ($conditions as $column => $value) {
                $countQuery->where($column, '=', $value);
            }
        }

        $totalItemsQuery = $countQuery->execute();
        $totalItems = $totalItemsQuery[0]['total'];

        $totalPages = ceil($totalItems / $itemsPerPage);

        return [
            'data' => $results,
            'pagination' => [
                'total_items' => $totalItems,
                'current_page' => $page,
                'items_per_page' => $itemsPerPage,
                'total_pages' => $totalPages,
            ],
        ];
    }

    public function rawPaginate(string $query, array $params = [], int $page = 1, int $itemsPerPage = 15): array
    {
        try {
            // Calculate the offset
            $offset = ($page - 1) * $itemsPerPage;

            // Modify the original query to add LIMIT and OFFSET
            $paginatedQuery = $query . " LIMIT $itemsPerPage OFFSET $offset";

            // Execute the paginated query
            $results = $this->query($paginatedQuery, $params);

            // Create a query to count total rows
            $countQuery = "SELECT COUNT(*) as total FROM ($query) as count_table";
            $totalResult = $this->query($countQuery, $params);

            $totalItems = $totalResult[0]->total;
            $totalPages = ceil($totalItems / $itemsPerPage);

            return [
                'data' => $results,
                'pagination' => [
                    'total_items' => (int)$totalItems,
                    'current_page' => $page,
                    'items_per_page' => $itemsPerPage,
                    'total_pages' => $totalPages,
                ],
            ];
        } catch (\Exception $e) {
            throw new DatabaseException(
                "Failed to execute raw pagination query: {$e->getMessage()}",
                $e->getCode(),
                "error"
            );
        }
    }

    // Static version of the method
    public static function rawPaginateStatic(string $query, array $params = [], int $page = 1, int $itemsPerPage = 15): array
    {
        $instance = new static();
        return $instance->rawPaginate($query, $params, $page, $itemsPerPage);
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $queryBuilder = new QueryBuilder($this->connection);

        $queryBuilder->select()
            ->from($this->table)
            ->join($table, $first, $operator, $second, $type);

        $results = $queryBuilder->execute();

        if ($results) {
            $this->attributes = (array) $results[0];
            $this->attributes = $this->castAttributes($this->attributes);
            $this->exists = true;
            return $this;
        }

        throw new DatabaseException("No matching records found for join operation.", 404, "error");
    }

    // public function paginate(int $perPage, int $page = 1): array
    // {
    //     $queryBuilder = new QueryBuilder($this->connection);
    //     $queryBuilder->select()->from($this->table)->limit($perPage)->offset(($page - 1) * $perPage);
    //     $results = $queryBuilder->execute();
    //     return $results;
    // }

    private function filterAttributes(array $attributes): array
    {
        if (!empty($this->fillable)) {
            $attributes = array_intersect_key($attributes, array_flip($this->fillable));
        }
        if (!empty($this->guarded)) {
            $attributes = array_diff_key($attributes, array_flip($this->guarded));
        }
        return $attributes;
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        if (in_array($key, $this->fillable) && !in_array($key, $this->guarded)) {
            $this->attributes[$key] = $this->castAttribute($key, $value);
        } else {
            throw new DatabaseException("Attribute $key is not fillable or is guarded.");
        }
    }

    private function castAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            $attributes[$key] = $this->castAttribute($key, $value);
        }
        return $attributes;
    }

    private function castAttribute(string $key, $value)
    {
        if (!isset($this->casts[$key])) {
            return $value;
        }

        switch ($this->casts[$key]) {
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'string':
                return (string)$value;
            case 'bool':
                return (bool)$value;
            case 'array':
                return (array)$value;
            case 'datetime':
                return new \DateTime($value);
            case 'hash':
                return password_hash($value, PASSWORD_DEFAULT, ['cost' => 12]);
            case 'uuid':
                return bv_uuid();
            default:
                return $value;
        }
    }

    public function toArray(): array
    {
        $attributes = $this->attributes;

        foreach ($this->hidden as $hiddenAttribute) {
            unset($attributes[$hiddenAttribute]);
        }

        return $attributes;
    }

    public function save(): ?self
    {
        $this->attributes = $this->castAttributes($this->attributes);

        if ($this->exists) {
            return $this->update($this->attributes[$this->primaryKey], $this->attributes);
        } else {
            return $this->create($this->attributes);
        }
    }

    public function where($column, $operator = '=', $value): ?self
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $result = $queryBuilder->select()->from($this->table)->where($column, $operator, $value)->execute();

        if ($result) {
            $this->attributes = (array)$result[0];
            $this->attributes = $this->castAttributes($this->attributes);
            $this->exists = true;
            return $this;
        }

        throw new DatabaseException("Record not found", 404, "error");
    }

    public function whereIn($column, $value): ?self
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $result = $queryBuilder->select()->from($this->table)->whereIn($column, $value)->execute();

        if ($result) {
            $this->attributes = (array)$result[0];
            $this->attributes = $this->castAttributes($this->attributes);
            $this->exists = true;
            return $this;
        }

        throw new DatabaseException("Record not found", 404, "error");
    }

    // public static function factory()
    // {
    //     $factoryClass = 'PhpStrike\\database\\factories\\' . get_called_class() . 'Factory';
    //     if (class_exists($factoryClass)) {
    //         return new $factoryClass();
    //     }

    //     throw new BoltException('Factory class not found for ' . get_called_class());
    // }
    public static function factory()
    {
        $modelClass = (new \ReflectionClass(static::class))->getShortName();
        $factoryClass = "PhpStrike\\database\\factories\\{$modelClass}Factory";

        if (class_exists($factoryClass)) {
            return new $factoryClass;
        }

        throw new \Exception('Factory class not found for ' . static::class);
    }

    public static function whereStatic($column, $operator = '=', $value): array|int
    {
        $instance = new static();
        $queryBuilder = new QueryBuilder($instance->connection);
        return $queryBuilder->select()->from($instance->table)->where($column, $operator, $value)->execute();
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    protected function eagerLoadRelation($relation, $results)
    {
        $relationInstance = $this->$relation();
        $relatedModel = $relationInstance->getRelatedModel();
        $relatedPrimaryKey = $relatedModel->getPrimaryKey();
        $foreignKey = $relationInstance->getForeignKey();

        $keys = array_column($results, $this->getPrimaryKey());
        $relatedResults = $relatedModel->whereIn($foreignKey, $keys)->get();

        $mappedResults = [];
        foreach ($relatedResults as $relatedResult) {
            $mappedResults[$relatedResult->$foreignKey][] = $relatedResult;
        }

        foreach ($results as &$result) {
            $result->$relation = $mappedResults[$result->{$this->getPrimaryKey()}] ?? [];
        }

        return $results;
    }

    protected function eagerLoadRelations($results)
    {
        foreach ($this->relations as $relation) {
            $results = $this->eagerLoadRelation($relation, $results);
        }

        return $results;
    }


    public function pluck($column)
    {
        return array_map(function ($item) use ($column) {
            return $item->{$column};
        }, $this->items);
    }

    /** Relationship Section */

    // public function hasOne($related, $foreignKey = null, $localKey = null)
    // {
    //     $foreignKey = $foreignKey ?? strtolower(class_basename($this)) . '_id';
    //     $localKey = $localKey ?? $this->primaryKey;
    //     return new HasOne($this, $related, $foreignKey, $localKey);
    // }

    // public function hasMany($related, $foreignKey = null, $localKey = null)
    // {
    //     // If the foreign key is not provided, assume it's the related model's table name with '_id' suffix.
    //     $foreignKey = $foreignKey ?? strtolower(class_basename($this)) . '_id';

    //     // If the local key is not provided, use the primary key of the current model.
    //     $localKey = $localKey ?? $this->primaryKey;

    //     // Return a new HasMany relationship instance.
    //     return new HasMany($this, $related, $foreignKey, $localKey);
    // }

    // public function belongsTo($related, $foreignKey = null, $ownerKey = null)
    // {
    //     $foreignKey = $foreignKey ?? strtolower(class_basename($this)) . '_id';
    //     $ownerKey = $ownerKey ?? $this->primaryKey;
    //     return new BelongsTo($this, $related, $foreignKey, $ownerKey);
    // }

    // public function belongsToMany($related, $pivotTable = null, $foreignKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null)
    // {
    //     $pivotTable = $pivotTable ?? $this->getPivotTableName($related);
    //     $foreignPivotKey = $foreignKey ?? strtolower(class_basename($this)) . '_id';
    //     $relatedPivotKey = $relatedKey ?? $this->primaryKey;
    //     return new BelongsToMany($this, $related, $pivotTable, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey);
    // }

    protected function getPivotTableName($related)
    {
        $tables = [
            $this->getTable(),
            (new $related())->getTable(),
        ];

        sort($tables);

        return strtolower(implode('_', $tables));
    }
}
