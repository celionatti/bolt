<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Database Model ==============
 * ====================================
 */

namespace Bolt\Bolt\Database;

use Exception;

abstract class DatabaseModel extends Database
{
    public string $tableName;
    abstract public static function tableName(): string;
    protected $db;
    protected $queryBuilder;

    public $order             = 'desc';
    public $order_column     = 'id';
    public $primary_key     = 'id';

    public $limit             = 10;
    public $offset             = 0;
    public $errors             = [];

    public function __construct()
    {
        $this->db = $this->getInstance();
        $this->tableName = static::tableName();
        $this->queryBuilder = $this->db->queryBuilder($this->tableName);
    }

    public function find($column = "*")
    {
        return $this->queryBuilder
            ->select($column);
    }

    public function findById($id)
    {
        return $this->queryBuilder
            ->select()
            ->where(['id' => $id]);
    }

    public function insert()
    {
        $this->beforeSave();
        $this->db->beginTransaction();

        try {
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
            throw $e;
        }
    }

    public function beforeSave(): void
    {
    }
}
