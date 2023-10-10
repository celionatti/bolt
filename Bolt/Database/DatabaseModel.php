<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Database Model ==============
 * ====================================
 */

namespace Bolt\Bolt\Database;

use Bolt\Bolt\QueryBuilder\BoltQueryBuilder;

abstract class DatabaseModel
{
    public string $tableName;
    protected Database $db;
    protected BoltQueryBuilder $queryBuilder;

    public string $order           = 'desc';
    public string $order_column    = 'id';
    public string $primary_key     = 'id';

    public $limit             = 10;
    public $offset             = 0;
    public $errors             = [];

    public function __construct()
    {
        $this->db = new Database();
        $this->tableName = static::tableName();
        $this->queryBuilder = $this->db->queryBuilder($this->tableName);
    }

    abstract public static function tableName(): string;

    public function find($column = "*")
    {
        return $this->queryBuilder
            ->select($column);
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
            ->execute();
    }

    // Create a new record
    public function insert(array $data)
    {
        try {
            $this->db->beginTransaction(); // Start a transaction

            // Optionally, you can call a custom method before saving
            $this->beforeSave();

            $result = $this->queryBuilder
                ->insert($data)
                ->executeQuery();

            // Optionally, you can check if the insert was successful
            if ($result) {
                $this->db->commitTransaction(); // Commit the transaction
                return $result;
            } else {
                $this->db->rollbackTransaction(); // Rollback the transaction on failure
                return false;
            }
        } catch (\Exception $e) {
            $this->db->rollbackTransaction(); // Rollback the transaction on exception
            throw $e; // Rethrow the exception for handling at a higher level
        }
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

    // Find all records with custom conditions
    public function findAllBy(array $conditions)
    {
        return $this->queryBuilder
            ->select()
            ->where($conditions)
            ->get();
    }

    // Find records with custom conditions and order
    public function findAllByWithOrder(array $conditions, $orderByColumn, $orderDirection = 'asc')
    {
        return $this->queryBuilder
            ->select()
            ->where($conditions)
            ->orderBy($orderByColumn, $orderDirection)
            ->get();
    }

    // Find records with custom conditions and limit the results
    public function findAllByWithLimit(array $conditions, $limit)
    {
        return $this->queryBuilder
            ->select()
            ->where($conditions)
            ->limit($limit)
            ->get();
    }

    // Find records with custom conditions and order, with pagination support
    public function findAllByWithPagination(array $conditions, $page, $perPage, $orderByColumn, $orderDirection = 'asc')
    {
        $offset = ($page - 1) * $perPage;
        return $this->queryBuilder
            ->select()
            ->where($conditions)
            ->orderBy($orderByColumn, $orderDirection)
            ->limit($perPage)
            ->offset($offset)
            ->get();
    }

    // Count records with custom conditions
    public function countBy(array $conditions)
    {
        return $this->queryBuilder
            ->count()
            ->where($conditions)
            ->get()[0]->count;
    }

    // Find the first record matching the given conditions
    public function findOneBy(array $conditions)
    {
        return $this->queryBuilder
            ->select()
            ->where($conditions)
            ->limit(1)
            ->get()[0] ?? null;
    }

    // Find the maximum value of a specific column
    public function max($column)
    {
        return $this->queryBuilder
            ->select("MAX($column) as max")
            ->get()[0]->max ?? null;
    }

    // Find the minimum value of a specific column
    public function min($column)
    {
        return $this->queryBuilder
            ->select("MIN($column) as min")
            ->get()[0]->min ?? null;
    }

    // Find the average value of a specific column
    public function avg($column)
    {
        return $this->queryBuilder
            ->select("AVG($column) as avg")
            ->get()[0]->avg ?? null;
    }

    // Find the sum of values in a specific column
    public function sum($column)
    {
        return $this->queryBuilder
            ->select("SUM($column) as sum")
            ->get()[0]->sum ?? null;
    }

    // Delete records based on conditions
    public function deleteBy(array $conditions)
    {
        return $this->queryBuilder
            ->delete()
            ->where($conditions)
            ->execute();
    }

    // Update records based on conditions
    public function updateBy(array $data, array $conditions)
    {
        return $this->queryBuilder
            ->update($data)
            ->where($conditions)
            ->execute();
    }

    // Execute a custom SQL query and return the result
    public function executeRawQuery(string $sql, array $bindValues = [])
    {
        return $this->queryBuilder
            ->rawQuery($sql, $bindValues)
            ->get();
    }

    public function beforeSave(): void
    {
    }
}
