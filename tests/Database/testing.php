<?php

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

    // Find all records in the table
    public function findAll()
    {
        return $this->queryBuilder
            ->select()
            ->get();
    }

    // Find a single record by its primary key
    public function findById($id)
    {
        return $this->queryBuilder
            ->select()
            ->where([$this->primary_key => $id])
            ->get();
    }

    // Find a single record by email (assuming there's an 'email' column)
    public function findByEmail($email)
    {
        return $this->queryBuilder
            ->select()
            ->where(['email' => $email])
            ->get();
    }

    // Find a single record by custom criteria
    public function findOne(array $criteria)
    {
        return $this->queryBuilder
            ->select()
            ->where($criteria)
            ->get();
    }

    // Create a new record
    public function create(array $data)
    {
        return $this->queryBuilder
            ->insert($data)
            ->executeQuery();
    }

    // Update a record by primary key
    public function updateById($id, array $data)
    {
        return $this->queryBuilder
            ->update($data)
            ->where([$this->primary_key => $id])
            ->executeQuery();
    }

    // Delete a record by primary key
    public function deleteById($id)
    {
        return $this->queryBuilder
            ->delete()
            ->where([$this->primary_key => $id])
            ->executeQuery();
    }

    // Add other custom methods as needed

    public function beforeSave(): void
    {
        // Implement any custom logic before saving a record
    }
}
