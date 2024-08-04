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

    public function with(array $relations)
    {
        $this->relations = $relations;
        return $this;
    }

    public function create(array $attributes): ?self
    {
        $attributes = $this->filterAttributes($attributes);
        $attributes = $this->castAttributes($attributes);
        return $this->saveAttributes($attributes);
    }

    public function update($id, array $attributes): ?self
    {
        $attributes = $this->filterAttributes($attributes);
        $attributes = $this->castAttributes($attributes);
        return $this->saveAttributes($attributes, $id);
    }

    private function saveAttributes(array $attributes, $id = null): ?self
    {
        $queryBuilder = new QueryBuilder($this->connection);
        if ($id) {
            $queryBuilder->update($this->table, $attributes)->where($this->primaryKey, '=', $id)->execute();
            return $this->find($id);
        } else {
            $queryBuilder->insert($this->table, $attributes)->execute();
            return $this->find($this->connection->lastInsertId());
        }
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

    public function delete($id): bool
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $queryBuilder->delete($this->table)->where($this->primaryKey, '=', $id)->execute();
        return true;
    }

    public static function all(): array
    {
        $instance = new static();
        $queryBuilder = new QueryBuilder($instance->connection);
        return $queryBuilder->select()->from($instance->table)->execute();
    }

    public static function paginate(int $page = 1, int $itemsPerPage = 15): array
    {
        $instance = new static();
        $queryBuilder = new QueryBuilder($instance->connection);

        $offset = ($page - 1) * $itemsPerPage;

        $totalItemsQuery = (new QueryBuilder($instance->connection))->select("COUNT(*) as total")->from($instance->table)->execute();
        $totalItems = $totalItemsQuery[0]->total;

        $results = $queryBuilder->select()
            ->from($instance->table)
            ->limit($itemsPerPage)
            ->offset($offset)
            ->execute();

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
                return bolt_uuid();
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
        $factoryClass = 'PhpStrike\\database\\factories\\' . static::class . 'Factory';
        if (class_exists($factoryClass)) {
            return new $factoryClass;
        }

        throw new BoltException('Factory class not found for ' . static::class);
    }

    public static function whereStatic($column, $operator = '=', $value): array
    {
        $instance = new static();
        $queryBuilder = new QueryBuilder($instance->connection);
        return $queryBuilder->select()->from($instance->table)->where($column, $operator, $value)->execute();
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    protected function eagerLoadRelations($results)
    {
        foreach ($this->relations as $relation) {
            if (method_exists($this, $relation)) {
                $relatedModel = $this->{$relation}()->getRelatedModel();
                $relatedResults = $this->{$relation}()->getEagerLoadResults(array_column($results, $this->getPrimaryKey()));

                foreach ($results as &$result) {
                    $result->{$relation} = array_filter($relatedResults, function($related) use ($result) {
                        return $related->{$this->{$relation}()->getForeignKey()} == $result->{$this->getPrimaryKey()};
                    });
                }
            }
        }

        return $results;
    }

    /** Relationship Section */

    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?? $this->primaryKey;
        $localKey = $localKey ?? $this->getPrimaryValue();
        return new HasOne($this, $related, $foreignKey, $localKey);
    }

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?? $this->primaryKey;
        $localKey = $localKey ?? $this->getPrimaryValue();
        return new HasMany($this, $related, $foreignKey, $localKey);
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $foreignKey = $foreignKey ?? $this->primaryKey;
        $ownerKey = $ownerKey ?? $this->getPrimaryValue();
        return new BelongsTo($this, $related, $foreignKey, $ownerKey);
    }

    public function belongsToMany($related, $pivotTable = null, $foreignKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null)
    {
        $pivotTable = $pivotTable ?? $this->getPivotTableName($related);
        $foreignPivotKey = $foreignKey ?? $this->primaryKey;
        $relatedPivotKey = $relatedKey ?? $this->getPrimaryValue();
        return new BelongsToMany($this, $related, $pivotTable, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey);
    }

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
