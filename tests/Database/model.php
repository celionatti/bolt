<?php


declare(strict_types=1);

/**
 * ====================================
 * Bolt - Database Model ==============
 * ====================================
 */

namespace Bolt\Bolt\Database;

use Exception;

abstract class DatabaseModel_dd
{
    protected string $tableName;
    protected Database $db;
    protected BoltQueryBuilder $queryBuilder;

    public string $order = 'desc';
    public string $orderColumn = 'id'; // Changed to use camelCase for consistency
    public string $primaryKey = 'id'; // Changed to use camelCase for consistency

    public int $limit = 10;
    public int $offset = 0;
    public array $errors = [];

    public function __construct(Database $database)
    {
        $this->db = $database;
        $this->tableName = static::tableName();
        $this->queryBuilder = $this->db->queryBuilder($this->tableName);
    }

    abstract public static function tableName(): string;

    public function find($columns = "*")
    {
        return $this->queryBuilder
            ->select($columns);
    }

    public function findById($id)
    {
        return $this->queryBuilder
            ->select()
            ->where([$this->primaryKey => $id]); // Using $this->primaryKey to make it dynamic
    }

    public function insert(array $data)
    {
        $this->beforeSave();

        $this->db->beginTransaction();

        try {
            // Implement the logic to insert data into the database table
            // Example: $this->queryBuilder->insert($data);
            // After successful insertion, you can commit the transaction
            $this->db->commitTransaction();
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
            throw $e;
        }
    }

    public function beforeSave(): void
    {
        // Implement any logic you want to execute before saving data
    }
}
