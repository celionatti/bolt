<?php

declare(strict_types=1);

namespace celionatti\Bolt\Database\Model;

use celionatti\Bolt\Database\Database;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\BoltQueryBuilder\QueryBuilder;
use celionatti\Bolt\Database\Exception\DatabaseException;

abstract class caludeDatabaseModel
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
        $this->queryBuilder = new QueryBuilder($this->connection);
        $this->queryBuilder->from($this->table);
        return $this->queryBuilder;
    }

    public function query(): QueryBuilder
    {
        return $this->newQuery();
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function with($relations)
    {
        if (is_string($relations)) {
            $this->with[] = $relations;
        } elseif (is_array($relations)) {
            $this->with = array_merge($this->with, $relations);
        }
        return $this;
    }

    public function create(array $attributes): ?self
    {
        $attributes = $this->filterAttributes($attributes);
        $attributes = $this->castAttributes($attributes);

        try {
            $this->queryBuilder->insert($this->table, $attributes)->execute();
            return $this->find($this->connection->lastInsertId());
        } catch (\Exception $e) {
            throw new DatabaseException("Failed to create record: {$e->getMessage()}", $e->getCode(), "info");
        }
    }

    public function update(array $attributes, $id = null): ?self
    {
        if (!$id && !$this->exists) {
            throw new DatabaseException("Cannot update a non-existent model.");
        }

        $id = $id ?? $this->getPrimaryValue();
        $attributes = $this->filterAttributes($attributes);
        $attributes = $this->castAttributes($attributes);

        try {
            $this->queryBuilder
                ->update($this->table, $attributes)
                ->where($this->primaryKey, '=', $id)
                ->execute();

            return $this->find($id);
        } catch (\Exception $e) {
            throw new DatabaseException("Failed to update record: {$e->getMessage()}", $e->getCode(), "info");
        }
    }

    public function find($id): ?self
    {
        $result = $this->newQuery()
            ->select()
            ->where($this->primaryKey, '=', $id)
            ->execute();

        if (!empty($result)) {
            return $this->hydrate($result[0]);
        }

        return null;
    }

    public function findBy(array $conditions): ?self
    {
        $query = $this->newQuery()->select();

        foreach ($conditions as $column => $value) {
            $query->where($column, '=', $value);
        }

        $result = $query->execute();

        if (!empty($result)) {
            return $this->hydrate($result[0]);
        }

        return null;
    }

    public function where($column, $operator = '=', $value = null): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->queryBuilder->where($column, $operator, $value);
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $this->queryBuilder->whereIn($column, $values);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->queryBuilder->orderBy($column, $direction);
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->queryBuilder->limit($limit);
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->queryBuilder->offset($offset);
        return $this;
    }

    public function get(): array
    {
        $results = $this->queryBuilder->execute();
        return array_map([$this, 'hydrate'], $results);
    }

    public function first(): ?self
    {
        $result = $this->queryBuilder->limit(1)->execute();
        return !empty($result) ? $this->hydrate($result[0]) : null;
    }

    public function delete($id = null): bool
    {
        $id = $id ?? $this->getPrimaryValue();

        if (!$id) {
            throw new DatabaseException("No ID specified for deletion");
        }

        try {
            $this->queryBuilder
                ->delete($this->table)
                ->where($this->primaryKey, '=', $id)
                ->execute();

            $this->exists = false;
            return true;
        } catch (\Exception $e) {
            throw new DatabaseException("Failed to delete record: {$e->getMessage()}");
        }
    }

    public function paginate(int $perPage = 15, int $currentPage = 1): array
    {
        return $this->queryBuilder->paginate($perPage, $currentPage);
    }

    protected function hydrate($data): self
    {
        $model = new static();
        $model->exists = true;
        $model->attributes = $this->castAttributes((array)$data);

        if (!empty($this->with)) {
            foreach ($this->with as $relation) {
                if (method_exists($model, $relation)) {
                    $model->loadRelation($relation);
                }
            }
        }

        return $model;
    }

    protected function loadRelation($relation)
    {
        if (method_exists($this, $relation)) {
            $this->relations[$relation] = $this->$relation()->get();
        }
    }

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

    protected function castAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            $attributes[$key] = $this->castAttribute($key, $value);
        }
        return $attributes;
    }

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
                return is_string($value) ? json_decode($value, true) : (array)$value;
            case 'datetime':
                return $value instanceof \DateTime ? $value : new \DateTime($value);
            case 'json':
                return is_string($value) ? json_decode($value, true) : $value;
            default:
                return $value;
        }
    }

    public function getPrimaryValue()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function toArray(): array
    {
        $attributes = $this->attributes;

        foreach ($this->hidden as $hiddenAttribute) {
            unset($attributes[$hiddenAttribute]);
        }

        if (!empty($this->relations)) {
            foreach ($this->relations as $key => $value) {
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        if (method_exists($this, $key)) {
            return $this->$key()->get();
        }

        return null;
    }

    public function __set($key, $value)
    {
        if (in_array($key, $this->fillable) && !in_array($key, $this->guarded)) {
            $this->attributes[$key] = $this->castAttribute($key, $value);
        } else {
            throw new DatabaseException("Attribute $key is not fillable or is guarded.");
        }
    }

    public function save(): ?self
    {
        return $this->exists ? $this->update($this->attributes) : $this->create($this->attributes);
    }
}