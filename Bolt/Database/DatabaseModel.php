<?php

declare(strict_types=1);

/**
 * ====================================
 * Bolt - Database Model ==============
 * ====================================
 */

namespace Bolt\Bolt\Database;

use Bolt\Bolt\Model;
use Bolt\Bolt\Config;
use Bolt\Bolt\BoltException\BoltException;
use Bolt\Bolt\BoltQueryBuilder\BoltQueryBuilder;

abstract class DatabaseModel extends Model
{
    public string $tableName;
    abstract public static function tableName(): string;
    protected Database $db;
    protected BoltQueryBuilder $queryBuilder;

    public string $order           = 'desc';
    public string $order_column    = 'id';
    public string $primary_key     = 'id';

    public $limit             = 10;
    public $offset             = 0;
    public $errors             = [];
    protected bool $validationPassed = true;

    public $allowedInsertParams = [];
    public $allowedUpdateParams = [];

    public function __construct()
    {
        $this->db = new Database();
    }


    // Initialize and return the query builder
    protected function getQueryBuilder()
    {
        if (!isset($this->queryBuilder)) {
            $this->db = new Database();
            $this->tableName = static::tableName();
            $this->queryBuilder = $this->db->queryBuilder($this->tableName);
        }
        return $this->queryBuilder;
    }

    public function isNew(): bool
    {
        return empty($this->{$this->primary_key});
    }

    public function timeStamps($createdAtField, $updatedAtField = null): void
    {
        $timeZone = Config::get('time_zone');
        date_default_timezone_set($timeZone);

        $now = (new \DateTime("now", new \DateTimeZone($timeZone)))->format('Y-m-d H:i:s');

        if ($updatedAtField !== null) {
            $this->$updatedAtField = $now;
        }

        if ($this->isNew()) {
            $this->$createdAtField = $now;
        }
    }

    // Find all records in the table
    public function findAll()
    {
        return $this->getQueryBuilder()
            ->select()
            ->get();
    }

    // Find a single record by its primary key
    public function findById($id)
    {
        return $this->getQueryBuilder()
            ->select()
            ->where([$this->primary_key => $id])
            ->get();
    }

    // Find a single record by email (assuming there's an 'email' column)
    public function findByEmail($email)
    {
        return $this->getQueryBuilder()
            ->select()
            ->where(['email' => $email])
            ->get()[0] ?? null;
    }

    // Find a single record by custom criteria
    public function findOne(array $criteria)
    {
        return $this->getQueryBuilder()
            ->select()
            ->where($criteria)
            ->limit(1)
            ->get()[0] ?? null;
    }

    public function create(array $data, array $allowedParams = [])
    {
        if ($this->validationPassed) {
            if (empty($allowedParams)) {
                $allowedParams = $this->allowedInsertParams;
            }

            $filteredData = $this->filterDataByAllowedParams($data, $allowedParams);

            return $this->getQueryBuilder()
                ->insert($filteredData)
                ->execute();
        }
    }

    public function insert(array $data, array $allowedParams = [])
    {
        if ($this->validationPassed) {
            if (empty($allowedParams)) {
                $allowedParams = $this->allowedInsertParams;
            }

            $filteredData = $this->filterDataByAllowedParams($data, $allowedParams);

            try {
                $this->db->beginTransaction(); // Start a transaction

                // Optionally, you can call a custom method before saving
                $this->beforeSave();

                $result = $this->getQueryBuilder()
                    ->insert($filteredData)
                    ->execute();

                // Optionally, you can check if the insert was successful
                if ($result) {
                    $this->db->commitTransaction(); // Commit the transaction
                    return $result;
                } else {
                    $this->db->rollbackTransaction(); // Rollback the transaction on failure
                    return false;
                }
            } catch (BoltException $e) {
                $this->db->rollbackTransaction(); // Rollback the transaction on exception
                // echo "Error: " . $e->getMessage();
                throw $e; // Rethrow the exception for handling at a higher level
            }
        }
    }

    protected function filterDataByAllowedParams(array $data, array $allowedParams): array
    {
        return array_filter($data, function ($key) use ($allowedParams) {
            return in_array($key, $allowedParams);
        }, ARRAY_FILTER_USE_KEY);
    }

    // // Update a record by primary key
    // public function updateById($id, array $data)
    // {
    //     return $this->getQueryBuilder()
    //         ->update($data)
    //         ->where([$this->primary_key => $id])
    //         ->execute();
    // }

    public function updateById($id, array $data, array $allowedParams = [])
    {
        if (empty($allowedParams)) {
            $allowedParams = $this->allowedUpdateParams;
        }

        $filteredData = $this->filterDataByAllowedParams($data, $allowedParams);

        return $this->getQueryBuilder()
            ->update($filteredData)
            ->where([$this->primary_key => $id])
            ->execute();
    }

    public function updateBy(array $data, array $conditions, array $allowedParams = [])
    {
        if (empty($allowedParams)) {
            $allowedParams = $this->allowedUpdateParams;
        }

        $filteredData = $this->filterDataByAllowedParams($data, $allowedParams);

        return $this->getQueryBuilder()
            ->update($filteredData)
            ->where($conditions)
            ->execute();
    }

    // Delete records based on conditions
    public function deleteBy(array $conditions)
    {
        return $this->getQueryBuilder()
            ->delete()
            ->where($conditions)
            ->execute();
    }

    // // Update records based on conditions
    // public function updateBy(array $data, array $conditions)
    // {
    //     return $this->getQueryBuilder()
    //         ->update($data)
    //         ->where($conditions)
    //         ->execute();
    // }

    // Delete a record by primary key
    public function deleteById($id)
    {
        return $this->getQueryBuilder()
            ->delete()
            ->where([$this->primary_key => $id])
            ->execute();
    }

    // Find all records with custom conditions
    public function findAllBy(array $conditions)
    {
        return $this->getQueryBuilder()
            ->select()
            ->where($conditions)
            ->get();
    }

    // Find records with custom conditions and order
    public function findAllByWithOrder(array $conditions, $orderByColumn, $orderDirection = 'asc')
    {
        return $this->getQueryBuilder()
            ->select()
            ->where($conditions)
            ->orderBy($orderByColumn, $orderDirection)
            ->get();
    }

    // Find records with custom conditions and limit the results
    public function findAllByWithLimit(array $conditions, $limit)
    {
        return $this->getQueryBuilder()
            ->select()
            ->where($conditions)
            ->limit($limit)
            ->get();
    }

    // Find records with custom conditions and order, with pagination support
    public function findAllByWithPagination(array $conditions, $page, $perPage, $orderByColumn, $orderDirection = 'asc')
    {
        $offset = ($page - 1) * $perPage;
        return $this->getQueryBuilder()
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
        return $this->getQueryBuilder()
            ->count()
            ->where($conditions)
            ->get()[0]->count;
    }

    // Find the first record matching the given conditions
    public function findOneBy(array $conditions)
    {
        return $this->getQueryBuilder()
            ->select()
            ->where($conditions)
            ->limit(1)
            ->get()[0] ?? null;
    }

    // Find the maximum value of a specific column
    public function max($column)
    {
        return $this->getQueryBuilder()
            ->select("MAX($column) as max")
            ->get()[0]->max ?? null;
    }

    // Find the minimum value of a specific column
    public function min($column)
    {
        return $this->getQueryBuilder()
            ->select("MIN($column) as min")
            ->get()[0]->min ?? null;
    }

    // Find the average value of a specific column
    public function avg($column)
    {
        return $this->getQueryBuilder()
            ->select("AVG($column) as avg")
            ->get()[0]->avg ?? null;
    }

    // Find the sum of values in a specific column
    public function sum($column)
    {
        return $this->getQueryBuilder()
            ->select("SUM($column) as sum")
            ->get()[0]->sum ?? null;
    }

    // Execute a custom SQL query and return the result
    public function executeRawQuery(string $sql, array $bindValues = [])
    {
        return $this->getQueryBuilder()
            ->rawQuery($sql, $bindValues)
            ->get();
    }

    public function beforeSave(): void
    {
    }

    public function runValidation($validator): void
    {
        $validates = $validator->runValidation();
        if (!$validates) {
            $this->validationPassed = false;
            $this->errors[$validator->field] = $validator->msg;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setError($name, $value): void
    {
        $this->errors[$name] = $value;
        $this->validationPassed = false;
    }
}
