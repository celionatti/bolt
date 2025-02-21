<?php

declare(strict_types=1);

namespace celionatti\Bolt\Database\Model;

use celionatti\Bolt\Database\Database;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\BoltQueryBuilder\QueryBuilder;
use celionatti\Bolt\Database\Exception\DatabaseException;

abstract class DatabaseModel
{
    protected $connection;
    protected $queryBuilder;
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
    protected $with = [];
    protected $softDelete = false;
    protected $timestamps = true;
    protected $dateFormat = 'Y-m-d H:i:s';

    public function __construct()
    {
        $this->connection = Database::getInstance()->getConnection();
        $this->queryBuilder = new QueryBuilder($this->connection);
        $this->setTable();
    }

    private function setTable()
    {
        if (!$this->table) {
            $className = (new \ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's';
        }
    }

    public function newQuery(): QueryBuilder
    {
        $query = $this->queryBuilder->table($this->table);

        if ($this->softDelete) {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function transaction(callable $callback)
    {
        try {
            $this->connection->beginTransaction();
            $result = $callback($this);
            $this->connection->commit();
            return $result;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new DatabaseException("Transaction failed: " . $e->getMessage());
        }
    }

    // Enhanced Create Method with Timestamps
    public function create(array $attributes): ?self
    {
        $attributes = $this->filterAttributes($attributes);
        $attributes = $this->castAttributes($attributes);

        if ($this->timestamps) {
            $now = date($this->dateFormat);
            $attributes['created_at'] = $now;
            $attributes['updated_at'] = $now;
        }

        try {
            $this->queryBuilder->insert($this->table, $attributes)->execute();
            return $this->findById($this->connection->lastInsertId());
        } catch (\Exception $e) {
            throw new DatabaseException("Failed to create record: {$e->getMessage()}", $e->getCode(), "info");
        }
    }

    // Enhanced Update Method with Timestamps
    public function update(array $attributes, $id = null, $key = null): ?self
    {
        $attributes = $this->filterAttributes($attributes);
        $attributes = $this->castAttributes($attributes);

        if ($this->timestamps) {
            $attributes['updated_at'] = date($this->dateFormat);
        }

        try {
            $id = $id ?? $this->getPrimaryValue();
            $key = $key ?? $this->primaryKey;

            $this->queryBuilder
                ->update($this->table, $attributes)
                ->where($key, '=', $id)
                ->execute();

            return $this->find($id);
        } catch (\Exception $e) {
            throw new DatabaseException("Failed to update record: {$e->getMessage()}", $e->getCode(), "info");
        }
    }

    // Soft Delete Implementation
    public function softDelete($id = null): bool
    {
        if (!$this->softDelete) {
            throw new DatabaseException("Soft deletes are not enabled for this model.");
        }

        $id = $id ?? $this->getPrimaryValue();
        return (bool) $this->update(['deleted_at' => date($this->dateFormat)], $id);
    }

    // Force Delete Implementation
    public function forceDelete($id = null): bool
    {
        $id = $id ?? $this->getPrimaryValue();
        return (bool) $this->queryBuilder
            ->delete($this->table)
            ->where($this->primaryKey, '=', $id)
            ->execute();
    }

    // Restore Soft Deleted Record
    public function restore($id = null): bool
    {
        if (!$this->softDelete) {
            throw new DatabaseException("Soft deletes are not enabled for this model.");
        }

        $id = $id ?? $this->getPrimaryValue();
        return (bool) $this->update(['deleted_at' => null], $id);
    }

    // Enhanced Query Methods
    public function whereNull($column): self
    {
        $this->queryBuilder->where($column, 'IS', 'NULL');
        return $this;
    }

    public function whereNotNull($column): self
    {
        $this->queryBuilder->where($column, 'IS NOT', 'NULL');
        return $this;
    }

    public function whereBetween($column, $start, $end): self
    {
        $this->queryBuilder->where($column, '>=', $start)->where($column, '<=', $end);
        return $this;
    }

    public function whereNotBetween($column, $start, $end): self
    {
        $this->queryBuilder->where($column, '<', $start)->orWhere($column, '>', $end);
        return $this;
    }

    public function whereDate($column, $operator, $value): self
    {
        $this->queryBuilder->where("DATE($column)", $operator, $value);
        return $this;
    }

    // Advanced Aggregates
    public function count($column = '*'): int
    {
        $result = $this->queryBuilder
            ->select("COUNT($column) as count")
            ->from($this->table)
            ->execute();
        return (int) $result[0]['count'];
    }

    public function max($column): mixed
    {
        $result = $this->queryBuilder
            ->select("MAX($column) as max")
            ->from($this->table)
            ->execute();
        return $result[0]['max'];
    }

    public function min($column): mixed
    {
        $result = $this->queryBuilder
            ->select("MIN($column) as min")
            ->from($this->table)
            ->execute();
        return $result[0]['min'];
    }

    public function avg($column): float
    {
        $result = $this->queryBuilder
            ->select("AVG($column) as avg")
            ->from($this->table)
            ->execute();
        return (float) $result[0]['avg'];
    }

    public function sum($column): float
    {
        $result = $this->queryBuilder
            ->select("SUM($column) as sum")
            ->from($this->table)
            ->execute();
        return (float) $result[0]['sum'];
    }

    // Enhanced Relationship Loading
    public function load($relations): self
    {
        if (is_string($relations)) {
            $relations = [$relations];
        }

        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $this->loadRelation($relation);
            }
        }

        return $this;
    }

    protected function loadRelation($relation)
    {
        $relationMethod = $this->$relation();
        $this->attributes[$relation] = $relationMethod->get();
    }

    // Advanced Query Scopes
    public function scope($name, ...$arguments)
    {
        $methodName = 'scope' . ucfirst($name);
        if (method_exists($this, $methodName)) {
            return $this->$methodName($this->queryBuilder, ...$arguments);
        }
        throw new DatabaseException("Scope [$name] does not exist.");
    }

    // Enhanced Attribute Handling
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->castAttribute($key, $this->attributes[$key]);
        }

        if (method_exists($this, $key)) {
            return $this->$key()->get();
        }

        return null;
    }

    public function setAttribute($key, $value): void
    {
        if ($this->hasSetMutator($key)) {
            $method = 'set' . ucfirst($key) . 'Attribute';
            $this->$method($value);
            return;
        }

        $this->attributes[$key] = $value;
    }

    protected function hasSetMutator($key): bool
    {
        return method_exists($this, 'set' . ucfirst($key) . 'Attribute');
    }

    // Enhanced Factory Implementation
    public static function factory(int $count = 1)
    {
        $modelClass = (new \ReflectionClass(static::class))->getShortName();
        $factoryClass = "PhpStrike\\database\\factories\\{$modelClass}Factory";

        if (!class_exists($factoryClass)) {
            throw new BoltException('Factory class not found for ' . static::class);
        }

        $factory = new $factoryClass;
        return $count > 1 ? $factory->count($count) : $factory;
    }

    // Implement remaining methods from your original class...
    // (keeping methods like findById, find, get, etc.)
}