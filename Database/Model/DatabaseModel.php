<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Database Model ==============
 * ====================================
 */

namespace celionatti\Bolt\Database\Model;

use celionatti\Bolt\Database\Database;
use celionatti\Bolt\Validation\Validator;
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
    protected $validationErrors = [];

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

    public function create(array $attributes)
    {
        if ($this->validate($attributes)) {
            $attributes = $this->filterAttributes($attributes);
            $attributes = $this->castAttributes($attributes);
            return $this->saveAttributes($attributes);
        }

        return false;
    }

    public function update($id, array $attributes)
    {
        if ($this->validate($attributes)) {
            $attributes = $this->filterAttributes($attributes);
            $attributes = $this->castAttributes($attributes);
            return $this->saveAttributes($attributes, $id);
        }

        return false;
    }

    private function saveAttributes(array $attributes, $id = null)
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

    public function find($id)
    {
        return $this->findBy([$this->primaryKey => $id]);
    }

    public function findBy(array $conditions)
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

    public function get()
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $results = $queryBuilder->select()->from($this->table)->execute();
        return $results;
    }

    public function first()
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

    public function delete($id)
    {
        $queryBuilder = new QueryBuilder($this->connection);
        $queryBuilder->delete($this->table)->where($this->primaryKey, '=', $id)->execute();
        return true;
    }

    public static function all()
    {
        $instance = new static();
        $queryBuilder = new QueryBuilder($instance->connection);
        return $queryBuilder->select()->from($instance->table)->execute();
    }

    public static function paginate(int $page = 1, int $itemsPerPage = 15)
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

    private function filterAttributes(array $attributes)
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
            if (array_key_exists($key, $this->casts)) {
                $this->attributes[$key] = $this->castAttributes([$key => $value])[$key];
            } else {
                $this->attributes[$key] = $value;
            }
        } else {
            throw new DatabaseException("Attribute $key is not fillable or is guarded.");
        }
    }

    private function castAttributes(array $attributes)
    {
        foreach ($this->casts as $key => $type) {
            if (isset($attributes[$key])) {
                switch ($type) {
                    case 'int':
                        $attributes[$key] = (int)$attributes[$key];
                        break;
                    case 'float':
                        $attributes[$key] = (float)$attributes[$key];
                        break;
                    case 'string':
                        $attributes[$key] = (string)$attributes[$key];
                        break;
                    case 'bool':
                        $attributes[$key] = (bool)$attributes[$key];
                        break;
                    case 'array':
                        $attributes[$key] = (array)$attributes[$key];
                        break;
                    case 'datetime':
                        $attributes[$key] = new \DateTime($attributes[$key]);
                        break;
                    case 'hash':
                        $attributes[$key] = password_hash($attributes[$key], PASSWORD_DEFAULT, ['cost' => 12]);
                        break;
                    case 'uuid':
                        $attributes[$key] = bolt_uuid();
                        break;
                }
            }
        }
        return $attributes;
    }

    public function toArray()
    {
        $attributes = $this->attributes;

        foreach ($this->hidden as $hiddenAttribute) {
            unset($attributes[$hiddenAttribute]);
        }

        return $attributes;
    }

    public function validate(array $attributes = null)
    {
        $validator = new Validator($attributes ?? $this->attributes, $this->rules);
        if ($validator->fails()) {
            $this->validationErrors = $validator->errors();
            return false;
        }
        return true;
    }

    public function save()
    {
        $this->attributes = $this->castAttributes($this->attributes);

        if ($this->validate()) {
            if ($this->exists) {
                return $this->update($this->attributes[$this->primaryKey], $this->attributes);
            } else {
                return $this->create($this->attributes);
            }
        }

        return false;
    }

    public function where($column, $operator = '=', $value)
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

    public static function factory()
    {
        $factoryClass = 'PhpStrike\\database\\factories\\' . get_called_class() . 'Factory';
        if (class_exists($factoryClass)) {
            return new $factoryClass();
        }

        throw new \Exception('Factory class not found for ' . get_called_class());
    }

    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?? $this->primaryKey;
        $localKey = $localKey ?? $this->primaryKey;

        return new HasOne($this, $related, $foreignKey, $localKey);
    }

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $foreignKey = $foreignKey ?? $this->primaryKey;
        $localKey = $localKey ?? $this->primaryKey;

        return new HasMany($this, $related, $foreignKey, $localKey);
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($related))->getShortName()) . '_id';
        $ownerKey = $ownerKey ?? $this->primaryKey;

        return new BelongsTo($this, $related, $foreignKey, $ownerKey);
    }

    public function belongsToMany($related, $table = null, $foreignKey = null, $relatedKey = null)
    {
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
        $relatedKey = $relatedKey ?? strtolower((new \ReflectionClass($related))->getShortName()) . '_id';

        $table = $table ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_' . strtolower((new \ReflectionClass($related))->getShortName());

        return new BelongsToMany($this, $related, $table, $foreignKey, $relatedKey);
    }

    public function validationErrors()
    {
        return $this->validationErrors;
    }
}
