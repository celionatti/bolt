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
use JsonSerializable;
use celionatti\Bolt\Database\Model\Interfaces\ModelInterface;
use celionatti\Bolt\Database\Database;
use celionatti\Bolt\BoltException\BoltException;
use celionatti\Bolt\Database\Exception\DatabaseException;
use celionatti\Bolt\BoltQueryBuilder\QueryBuilder;
use celionatti\Bolt\Database\Relationships\HasOne;
use celionatti\Bolt\Database\Relationships\HasMany;
use celionatti\Bolt\Database\Relationships\BelongsTo;
use celionatti\Bolt\Database\Relationships\BelongsToMany;

abstract class oneDatabaseModel implements ModelInterface, JsonSerializable
{
    protected QueryBuilder $queryBuilder;
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $guarded = [];
    protected array $hidden = [];
    protected array $casts = [];
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;
    protected array $relations = [];
    protected bool $timestamps = true;
    protected string $createdAt = 'created_at';
    protected string $updatedAt = 'updated_at';

    public function __construct()
    {
        $this->initializeQueryBuilder();
        $this->setTable();
    }

    protected function initializeQueryBuilder(): void
    {
        $connection = Database::getInstance()->getConnection();
        $this->queryBuilder = new QueryBuilder($connection);
    }

    protected function setTable(): void
    {
        if (empty($this->table)) {
            $className = (new ReflectionClass($this))->getShortName();
            $this->table = strtolower($className) . 's';
        }
    }

    public function newQuery(): QueryBuilder
    {
        return clone $this->queryBuilder->from($this->table);
    }

    public function getKey(): mixed
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function setAttribute(string $key, mixed $value): self
    {
        if ($this->isFillable($key)) {
            $this->attributes[$key] = $this->castAttribute($key, $value);
        }
        return $this;
    }

    public function fill(array $attributes): self
    {
        foreach ($this->filterAttributes($attributes) as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    public function save(array $options = []): bool
    {
        $this->validateAttributes();
        $this->updateTimestamps();

        if ($this->exists) {
            $this->performUpdate();
        } else {
            $this->performInsert();
        }

        $this->original = $this->attributes;
        return true;
    }

    protected function performInsert(): void
    {
        $attributes = $this->getDirtyAttributes();
        $this->queryBuilder->insert($this->table, $attributes)->execute();
        $this->exists = true;
        $this->setAttribute($this->primaryKey, $this->queryBuilder->getLastInsertId());
    }

    protected function performUpdate(): void
    {
        $dirty = $this->getDirtyAttributes();
        if (!empty($dirty)) {
            $this->queryBuilder->update($this->table, $dirty)
                ->where($this->primaryKey, $this->getKey())
                ->execute();
        }
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $this->newQuery()
            ->where($this->primaryKey, $this->getKey())
            ->delete()
            ->execute();

        $this->exists = false;
        return true;
    }

    public static function find(mixed $id): ?self
    {
        return (new static())->newQuery()
            ->where((new static())->primaryKey, $id)
            ->first();
    }

    public static function all(): array
    {
        return (new static())->newQuery()->get();
    }

    public static function where(string $column, string $operator, mixed $value): QueryBuilder
    {
        return (new static())->newQuery()->where($column, $operator, $value);
    }

    public static function paginate(int $perPage = 15, int $page = 1): array
    {
        return (new static())->newQuery()->paginate($perPage, $page);
    }

    public function load(array $relations): self
    {
        foreach ($relations as $relation) {
            $this->loadRelation($relation);
        }
        return $this;
    }

    protected function loadRelation(string $relation): void
    {
        if (method_exists($this, $relation)) {
            $relation = $this->$relation();
            $relation->match(
                [$this],
                $relation->getResults()
            );
        }
    }

    public function hasOne(string $related, string $foreignKey = null, string $localKey = null): HasOne
    {
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->primaryKey;
        return new HasOne($this, $related, $foreignKey, $localKey);
    }

    public function hasMany(string $related, string $foreignKey = null, string $localKey = null): HasMany
    {
        $foreignKey = $foreignKey ?? $this->getForeignKey();
        $localKey = $localKey ?? $this->primaryKey;
        return new HasMany($this, $related, $foreignKey, $localKey);
    }

    public function belongsTo(string $related, string $foreignKey = null, string $ownerKey = null): BelongsTo
    {
        $foreignKey = $foreignKey ?? $related::getForeignKey();
        $ownerKey = $ownerKey ?? (new $related())->primaryKey;
        return new BelongsTo($this, $related, $foreignKey, $ownerKey);
    }

    public function belongsToMany(
        string $related,
        string $pivotTable = null,
        string $foreignPivotKey = null,
        string $relatedPivotKey = null,
        string $parentKey = null,
        string $relatedKey = null
    ): BelongsToMany {
        $pivotTable = $pivotTable ?? $this->getPivotTableName($related);
        $foreignPivotKey = $foreignPivotKey ?? $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?? (new $related())->getForeignKey();
        return new BelongsToMany(
            $this,
            $related,
            $pivotTable,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?? $this->primaryKey,
            $relatedKey ?? (new $related())->primaryKey
        );
    }

    protected function getDirtyAttributes(): array
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    protected function updateTimestamps(): void
    {
        if ($this->timestamps) {
            $now = new DateTime();
            $this->setAttribute($this->updatedAt, $now);

            if (!$this->exists) {
                $this->setAttribute($this->createdAt, $now);
            }
        }
    }

    protected function validateAttributes(): void
    {
        foreach ($this->attributes as $key => $value) {
            if (!$this->isFillable($key)) {
                throw new DatabaseException("Attribute {$key} is not fillable");
            }
        }
    }

    protected function isFillable(string $key): bool
    {
        if (in_array($key, $this->guarded)) {
            return false;
        }

        return empty($this->fillable) || in_array($key, $this->fillable);
    }

    protected function filterAttributes(array $attributes): array
    {
        return array_filter($attributes, function ($key) {
            return $this->isFillable($key);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function castAttribute(string $key, mixed $value): mixed
    {
        $castType = $this->casts[$key] ?? null;

        if (is_null($castType)) {
            return $value;
        }

        switch ($castType) {
            case 'int':
            case 'integer':
                return (int)$value;
            case 'float':
            case 'double':
                return (float)$value;
            case 'string':
                return (string)$value;
            case 'bool':
            case 'boolean':
                return (bool)$value;
            case 'array':
            case 'json':
                return json_decode($value, true);
            case 'datetime':
                return new DateTime($value);
            case 'timestamp':
                return strtotime($value);
            case 'hash':
                return password_hash($value, PASSWORD_DEFAULT);
            default:
                if (class_exists($castType)) {
                    return new $castType($value);
                }
                return $value;
        }
    }

    public function toArray(): array
    {
        $attributes = $this->attributes;
        foreach ($this->hidden as $hidden) {
            unset($attributes[$hidden]);
        }

        array_walk_recursive($attributes, function (&$value) {
            if ($value instanceof DateTime) {
                $value = $value->format(DateTime::ATOM);
            }
        });

        return $attributes;
    }

    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->getAttribute($name);
        }

        if (method_exists($this, $name)) {
            $relation = $this->$name();
            return $relation->getResults();
        }

        throw new DatabaseException("Property {$name} does not exist");
    }

    public function __set(string $name, mixed $value): void
    {
        $this->setAttribute($name, $value);
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]) || method_exists($this, $name);
    }

    public function __call(string $method, array $parameters): mixed
    {
        return $this->newQuery()->$method(...$parameters);
    }

    public static function __callStatic(string $method, array $parameters): mixed
    {
        return (new static())->$method(...$parameters);
    }
}
