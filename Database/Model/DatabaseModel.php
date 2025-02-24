<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Database Model ==============
 * ====================================
 */

namespace celionatti\Bolt\Database\Model;

use PDO;
use Exception;
use DateTime;
use ReflectionClass;
use celionatti\Bolt\Database\Database;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Database\Exception\DatabaseException;
use celionatti\Bolt\BoltQueryBuilder\QueryBuilder;

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
    protected $exists = false;
    protected $relations = [];

    public function __construct()
    {
        $this->connection = Database::getInstance()->getConnection();
        $this->setTable();
    }

    /**
     * Set the table name based on the model name if not already provided.
     */
    protected function setTable(): void
    {
        if (!$this->table) {
            $className = (new ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's';
        }
    }

    /**
     * Get a new QueryBuilder instance pre-configured with the model's table.
     */
    protected function newQuery(): QueryBuilder
    {
        return (new QueryBuilder($this->connection))
            ->select()
            ->from($this->table);
    }

    /**
     * Fill the model with attributes.
     */
    public function fill(array $attributes): self
    {
        $this->attributes = $this->castAttributes($attributes);
        return $this;
    }

    /**
     * Find a record by its primary key.
     */
    public function find($id): ?self
    {
        $result = $this->newQuery()
            ->where($this->primaryKey, '=', $id)
            ->limit(1)
            ->execute();

        if ($result) {
            $this->fill($result[0]);
            $this->exists = true;
            return $this;
        }
        return null;
    }

    /**
     * Find a record by its primary key or throw an exception.
     */
    public function findOrFail($id): self
    {
        $instance = $this->find($id);
        if (!$instance) {
            throw new DatabaseException("Record not found", 404, "error");
        }
        return $instance;
    }

    /**
     * Find a record using an array of conditions.
     */
    public function findBy(array $conditions): ?self
    {
        $query = $this->newQuery();
        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }
        $result = $query->limit(1)->execute();
        if ($result) {
            $this->fill($result[0]);
            $this->exists = true;
            return $this;
        }
        return null;
    }

    /**
     * Find all records matching the given conditions.
     */
    public function findAllBy(array $conditions): array
    {
        $query = $this->newQuery();
        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }
        return $query->execute();
    }

    /**
     * Get all records (with optional eager loaded relations).
     */
    public function get(): array
    {
        $results = $this->newQuery()->execute();
        if (!empty($this->relations)) {
            $results = $this->eagerLoadRelations($results);
        }
        return $results;
    }

    /**
     * Get the first record.
     */
    public function first(): ?self
    {
        $result = $this->newQuery()->limit(1)->execute();
        if ($result) {
            $this->fill($result[0]);
            $this->exists = true;
            return $this;
        }
        return null;
    }

    /**
     * Get the first record or throw an exception.
     */
    public function firstOrFail(): self
    {
        $instance = $this->first();
        if (!$instance) {
            throw new DatabaseException("Record not found", 404, "error");
        }
        return $instance;
    }

    /**
     * Create a new record.
     */
    public function create(array $attributes): ?self
    {
        $attributes = $this->filterAttributes($attributes);
        $attributes = $this->castAttributes($attributes);

        $query = new QueryBuilder($this->connection);
        $query->insert($this->table, $attributes)->execute();

        $id = $this->connection->lastInsertId();
        return $this->find($id);
    }

    /**
     * Update an existing record.
     */
    public function update(array $attributes, $id = null): ?self
    {
        $attributes = $this->filterAttributes($attributes);
        $attributes = $this->castAttributes($attributes);
        $id = $id ?? $this->getPrimaryValue();

        if (!$id) {
            throw new DatabaseException("No primary key value found for update", 400, "error");
        }

        $query = new QueryBuilder($this->connection);
        $query->update($this->table, $attributes)
              ->where($this->primaryKey, '=', $id)
              ->execute();

        return $this->find($id);
    }

    /**
     * Delete a record.
     */
    public function delete($id = null): bool
    {
        $id = $id ?? $this->getPrimaryValue();
        if (!$id) {
            throw new DatabaseException("No primary key value found for delete", 400, "error");
        }

        $query = new QueryBuilder($this->connection);
        $query->delete($this->table)
              ->where($this->primaryKey, '=', $id)
              ->execute();

        return true;
    }

    /**
     * Update an existing record or create a new one if not exists.
     */
    public function updateOrCreate(array $conditions, array $attributes): self
    {
        $instance = $this->findBy($conditions);
        if ($instance) {
            return $instance->update($attributes);
        }
        $data = array_merge($conditions, $attributes);
        return $this->create($data);
    }

    /**
     * Paginate results.
     */
    public static function paginate(int $page = 1, int $itemsPerPage = 15, array $conditions = [], array $order = []): array
    {
        $instance = new static();
        $query = $instance->newQuery();

        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }
        if (!empty($order)) {
            foreach ($order as $column => $direction) {
                $query->orderBy($column, $direction);
            }
        }
        $offset = ($page - 1) * $itemsPerPage;
        $query->limit($itemsPerPage)->offset($offset);
        $results = $query->execute();

        // Count total items
        $countQuery = (new QueryBuilder($instance->connection))
            ->select("COUNT(*) as total")
            ->from($instance->table);
        foreach ($conditions as $column => $value) {
            $countQuery->where($column, '=', $value);
        }
        $totalResult = $countQuery->execute();
        $totalItems = $totalResult[0]['total'] ?? 0;

        return [
            'data' => $results,
            'pagination' => [
                'total_items'   => (int)$totalItems,
                'current_page'  => $page,
                'items_per_page'=> $itemsPerPage,
                'total_pages'   => (int)ceil($totalItems / $itemsPerPage)
            ]
        ];
    }

    /**
     * Execute a raw pagination query.
     */
    public function rawPaginate(string $query, array $params = [], int $page = 1, int $itemsPerPage = 15): array
    {
        try {
            $offset = ($page - 1) * $itemsPerPage;
            $paginatedQuery = $query . " LIMIT $itemsPerPage OFFSET $offset";
            $results = $this->query($paginatedQuery, $params, "assoc");

            $countQuery = "SELECT COUNT(*) as total FROM ($query) as count_table";
            $totalResult = $this->query($countQuery, $params, "assoc");

            $totalItems = $totalResult['result'][0]['total'] ?? 0;
            return [
                'data' => $results['result'],
                'pagination' => [
                    'total_items'   => (int)$totalItems,
                    'current_page'  => $page,
                    'items_per_page'=> $itemsPerPage,
                    'total_pages'   => (int)ceil($totalItems / $itemsPerPage)
                ]
            ];
        } catch (\Exception $e) {
            throw new DatabaseException(
                "Failed to execute raw pagination query: {$e->getMessage()}",
                $e->getCode(),
                "error"
            );
        }
    }

    /**
     * Static version of rawPaginate.
     */
    public static function rawPaginateStatic(string $query, array $params = [], int $page = 1, int $itemsPerPage = 15): array
    {
        $instance = new static();
        return $instance->rawPaginate($query, $params, $page, $itemsPerPage);
    }

    /**
     * Delegate dynamic calls to the QueryBuilder instance.
     */
    public function __call($method, $parameters)
    {
        $query = $this->newQuery();
        if (method_exists($query, $method)) {
            return $query->$method(...$parameters);
        }
        throw new DatabaseException("Method {$method} not found.", 404, "error");
    }

    /**
     * Delegate static calls to the QueryBuilder instance.
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static();
        $query = $instance->newQuery();
        if (method_exists($query, $method)) {
            return $query->$method(...$parameters);
        }
        throw new DatabaseException("Static method {$method} not found.", 404, "error");
    }

    /**
     * Get the primary key value.
     */
    public function getPrimaryValue()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    /**
     * Filter attributes based on fillable and guarded.
     */
    protected function filterAttributes(array $attributes): array
    {
        if (!empty($this->fillable)) {
            $attributes = array_intersect_key($attributes, array_flip($this->fillable));
        }
        if (!empty($this->guarded)) {
            $attributes = array_diff_key($attributes, array_flip($this->guarded));
        }
        return $attributes;
    }

    /**
     * Cast attributes based on defined casts.
     */
    protected function castAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            $attributes[$key] = $this->castAttribute($key, $value);
        }
        return $attributes;
    }

    /**
     * Cast an individual attribute.
     */
    protected function castAttribute(string $key, $value)
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

    /**
     * Convert the model attributes to an array (excluding hidden attributes).
     */
    public function toArray(): array
    {
        $attributes = $this->attributes;
        foreach ($this->hidden as $hiddenAttribute) {
            unset($attributes[$hiddenAttribute]);
        }
        return $attributes;
    }

    /**
     * Eager load a single relation.
     */
    protected function eagerLoadRelation($relation, $results)
    {
        $relationInstance = $this->$relation();
        $relatedModel = $relationInstance->getRelatedModel();
        $foreignKey = $relationInstance->getForeignKey();

        $keys = array_column($results, $this->primaryKey);
        $relatedResults = $relatedModel->whereIn($foreignKey, $keys)->get();

        $mappedResults = [];
        foreach ($relatedResults as $relatedResult) {
            $mappedResults[$relatedResult->$foreignKey][] = $relatedResult;
        }

        foreach ($results as &$result) {
            $result->$relation = $mappedResults[$result->{$this->primaryKey}] ?? [];
        }
        return $results;
    }

    /**
     * Eager load multiple relations.
     */
    protected function eagerLoadRelations($results)
    {
        foreach ($this->relations as $relation) {
            $results = $this->eagerLoadRelation($relation, $results);
        }
        return $results;
    }

    /**
     * Execute a raw query (delegated to the Database class).
     */
    public function query(string $query, array $params = [], string $data_type = 'object')
    {
        return Database::getInstance()->query($query, $params, $data_type);
    }

    /**
     * Static factory method to obtain a model factory.
     */
    public static function factory()
    {
        $modelClass = (new \ReflectionClass(static::class))->getShortName();
        $factoryClass = "PhpStrike\\database\\factories\\{$modelClass}Factory";

        if (class_exists($factoryClass)) {
            return new $factoryClass;
        }

        throw new \Exception('Factory class not found for ' . static::class);
    }

    /**
     * Get the pivot table name for many-to-many relationships.
     */
    protected function getPivotTableName($related)
    {
        $tables = [
            $this->table,
            (new $related())->getTable(),
        ];
        sort($tables);
        return strtolower(implode('_', $tables));
    }
}
