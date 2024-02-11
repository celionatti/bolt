<?php

declare(strict_types=1);

/**
 * ========================================
 * Bolt - Bolt Query Builder ==============
 * ========================================
 */

namespace celionatti\Bolt\BoltQueryBuilder;

use PDO;
use PDOException;
use celionatti\Bolt\Database\DatabaseException;


class BoltQueryBuilder
{
    private $connection;
    private string $table;
    private $query;
    private $bindValues = [];
    private $joinClauses = [];
    private $currentStep = 'initial';

    public function __construct($connection, string $table)
    {
        if (empty($table)) {
            throw new \InvalidArgumentException('Table name must not be empty.');
        }

        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * Select columns for the query.
     *
     * @param string|array $columns The columns to select.
     * @return $this
     * @throws \Exception If called in an invalid method order.
     * @throws \InvalidArgumentException If $columns is invalid.
     */
    public function select($columns = '*')
    {
        $this->currentStep = "initial";
        if ($this->currentStep !== 'initial' && $this->currentStep !== 'raw') {
            throw new \Exception('Invalid method order. SELECT should come first.');
        }

        if (!is_array($columns) && !is_string($columns)) {
            throw new \InvalidArgumentException('Invalid argument for SELECT method. Columns must be an array or a comma-separated string.');
        }

        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $this->query = "SELECT $columns FROM $this->table";
        $this->currentStep = 'select';

        return $this;
    }

    public function insert(array $data)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Invalid argument for INSERT method. Data array must not be empty.');
        }

        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. INSERT should come before other query building methods.');
        }

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $this->query = "INSERT INTO $this->table ($columns) VALUES ($values)";
        $this->bindValues = $data;
        $this->currentStep = 'insert';

        return $this;
    }

    public function update(array $data)
    {
        $this->currentStep = 'initial';
        
        if (empty($data)) {
            throw new \InvalidArgumentException('Invalid argument for UPDATE method. Data array must not be empty.');
        }
        
        if ($this->currentStep !== 'initial' && $this->currentStep !== 'where' && $this->currentStep !== 'select' && $this->currentStep !== 'raw') {
            throw new \Exception('Invalid method order. UPDATE should come before other query building methods.');
        }

        $set = [];
        foreach ($data as $column => $value) {
            if (!is_string($column) || empty($column)) {
                throw new \InvalidArgumentException('Invalid argument for UPDATE method. Column names must be non-empty strings.');
            }

            $set[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query = "UPDATE $this->table SET " . implode(', ', $set);
        $this->currentStep = 'update';

        return $this;
    }

    public function delete()
    {
        if (!in_array($this->currentStep, ['select', 'initial', 'limit', 'raw', 'where'])) {
            throw new \Exception('Invalid method order. DELETE should come before other query building methods.');
        }

        $this->query = "DELETE FROM $this->table";
        $this->currentStep = 'delete';

        return $this;
    }

    public function where(array $conditions)
    {
        if (!in_array($this->currentStep, ['select', 'update', 'delete', 'raw', 'where', 'join', 'count'])) {
            throw new \Exception('Invalid method order. WHERE should come after SELECT, UPDATE, DELETE, or a previous WHERE.');
        }

        if (empty($conditions)) {
            throw new \InvalidArgumentException('Invalid argument for WHERE method. Conditions array must not be empty.');
        }

        $where = [];
        foreach ($conditions as $column => $value) {
            if (!is_string($column) || empty($column)) {
                throw new \InvalidArgumentException('Invalid argument for WHERE method. Column names must be non-empty strings.');
            }

            $where[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query .= " WHERE " . implode(' AND ', $where);
        $this->currentStep = 'where';

        return $this;
    }

    public function join(string $table, string $onClause, string $type = 'INNER')
    {
        $this->validateJoinMethod();

        $this->validateJoinArguments($table, $onClause, $type);

        $this->joinClauses[] = "$type JOIN $table ON $onClause";

        $this->currentStep = 'join';

        return $this;
    }

    private function validateJoinMethod()
    {
        $allowedPreviousSteps = ['initial', 'select', 'count', 'raw'];

        if (!in_array($this->currentStep, $allowedPreviousSteps)) {
            throw new \Exception('Invalid method order. JOIN should come after SELECT, COUNT, or a previous JOIN.');
        }
    }

    private function validateJoinArguments(string $table, string $onClause, string $type)
    {
        if (empty($table) || empty($onClause) || !in_array($type, ['INNER', 'LEFT', 'RIGHT', 'OUTER'])) {
            throw new \InvalidArgumentException('Invalid arguments for JOIN method.');
        }
    }

    public function leftJoin($table, $onClause)
    {
        return $this->join($table, $onClause, 'LEFT');
    }

    public function rightJoin($table, $onClause)
    {
        return $this->join($table, $onClause, 'RIGHT');
    }

    public function outerJoin($table, $onClause)
    {
        return $this->join($table, $onClause, 'OUTER');
    }

    public function count()
    {
        $allowedPreviousSteps = ['initial', 'select', 'count', 'raw', 'where'];

        if (!in_array($this->currentStep, $allowedPreviousSteps)) {
            throw new \Exception('Invalid method order. COUNT should come before other query building methods.');
        }

        $this->query = "SELECT COUNT(*) AS count FROM $this->table";
        $this->currentStep = 'count';

        return $this;
    }


    public function orderBy($column, $direction = 'ASC')
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where' && $this->currentStep !== 'raw') {
            throw new \Exception('Invalid method order. ORDER BY should come after SELECT, WHERE, or a previous ORDER BY.');
        }

        if (!is_string($column) || empty($column)) {
            throw new \InvalidArgumentException('Invalid argument for ORDER BY method. Column name must be a non-empty string.');
        }

        $this->query .= " ORDER BY $column $direction";
        $this->currentStep = 'order';

        return $this;
    }

    public function groupBy($column)
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where' && $this->currentStep !== 'order' && $this->currentStep !== 'raw') {
            throw new \Exception('Invalid method order. GROUP BY should come after SELECT, WHERE, ORDER BY, or a previous GROUP BY.');
        }

        if (!is_string($column) || empty($column)) {
            throw new \InvalidArgumentException('Invalid argument for GROUP BY method. Column name must be a non-empty string.');
        }

        $this->query .= "GROUP BY $column";
        $this->currentStep = 'group';

        return $this;
    }

    public function limit($limit)
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where' && $this->currentStep !== 'order' && $this->currentStep !== 'group' && $this->currentStep !== 'raw') {
            throw new \Exception('Invalid method order. LIMIT should come after SELECT, WHERE, ORDER BY, GROUP BY, or a previous LIMIT.');
        }

        if (!is_numeric($limit) || $limit < 1) {
            throw new \InvalidArgumentException('Invalid argument for LIMIT method. Limit must be a positive numeric value.');
        }

        $this->query .= " LIMIT $limit";
        $this->currentStep = 'limit';

        return $this;
    }

    public function offset($offset)
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where' && $this->currentStep !== 'order' && $this->currentStep !== 'group' && $this->currentStep !== 'limit' && $this->currentStep !== 'raw') {
            throw new \Exception('Invalid method order. OFFSET should come after SELECT, WHERE, ORDER BY, GROUP BY, LIMIT, or a previous OFFSET.');
        }

        if (!is_numeric($offset) || $offset < 0) {
            throw new \InvalidArgumentException('Invalid argument for OFFSET method. Offset must be a non-negative numeric value.');
        }

        $this->query .= " OFFSET $offset";
        $this->currentStep = 'offset';

        return $this;
    }

    public function execute()
    {
        try {
            $stm = $this->executeQuery();

            return $stm->rowCount();
        } catch (PDOException $e) {
            // Handle database error, e.g., log or throw an exception
            throw new DatabaseException($e->getMessage());
        }
    }

    public function get($data_type = 'object')
    {
        try {
            $stm = $this->executeQuery();

            if ($data_type === 'object') {
                return $stm->fetchAll(PDO::FETCH_OBJ);
            } elseif ($data_type === 'assoc') {
                return $stm->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return $stm->fetchAll(PDO::FETCH_CLASS);
            }
        } catch (PDOException $e) {
            // Handle database error, e.g., log or throw an exception
            throw new DatabaseException($e->getMessage());
        }
    }

    private function executeQuery()
    {
        try {
            // Combine the main query and join clauses
            $fullQuery = $this->query;

            if (!empty($this->joinClauses)) {
                $fullQuery .= ' ' . implode(' ', $this->joinClauses);
            }

            // Prepare the combined query
            $stm = $this->connection->prepare($fullQuery);

            // Bind values
            foreach ($this->bindValues as $param => $value) {
                $stm->bindValue($param, $value);
            }

            // Execute the query
            $stm->execute();

            return $stm;
        } catch (PDOException $e) {
            // Handle database error, e.g., log or throw an exception
            throw new DatabaseException($e->getMessage());
        }
    }

    public function distinct($columns = '*')
    {
        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. DISTINCT should come before other query building methods.');
        }

        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $columns = implode(', ', $columns);
        $this->query = "SELECT DISTINCT $columns FROM $this->table";
        $this->currentStep = 'distinct';

        return $this;
    }

    public function truncate()
    {
        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. TRUNCATE should come before other query building methods.');
        }

        $this->query = "TRUNCATE TABLE $this->table";
        $this->currentStep = 'truncate';

        return $this;
    }

    public function union(BoltQueryBuilder ...$queries)
    {
        if ($this->currentStep !== 'initial') {
            throw new \Exception('Invalid method order. UNION should come before other query building methods.');
        }

        // Store the current query and reset it
        $currentQuery = $this->query;
        $this->query = '';

        $queryStrings = [$currentQuery];
        foreach ($queries as $query) {
            $queryStrings[] = $query->query; // Assuming your query property is called "query"
        }

        $this->query = implode(' UNION ', $queryStrings);
        $this->currentStep = 'union';

        return $this;
    }


    public function rawQuery(string $sql, array $bindValues = [])
    {
        if ($this->currentStep !== 'initial' && $this->currentStep !== 'raw') {
            throw new \Exception('Invalid method order. Raw query should come before other query building methods.');
        }

        $this->query = $sql;
        $this->bindValues = array_merge($this->bindValues, $bindValues);
        // $this->bindValues = $bindValues;
        $this->currentStep = 'raw';

        return $this;
    }

    public function alias(string $alias)
    {
        if ($this->currentStep === 'initial') {
            throw new \Exception('Invalid method order. Alias should come after other query building methods.');
        }

        $this->query .= " AS $alias";

        return $this;
    }

    public function subquery(BoltQueryBuilder $subquery, string $alias)
    {
        if ($this->currentStep === 'initial') {
            throw new \Exception('Invalid method order. Subquery should come after other query building methods.');
        }

        $this->query .= " ($subquery) AS $alias";

        return $this;
    }

    public function between(string $column, $value1, $value2)
    {
        if ($this->currentStep !== 'select' && $this->currentStep !== 'where') {
            throw new \Exception('Invalid method order. BETWEEN should come after SELECT, WHERE, or a previous BETWEEN.');
        }

        $this->query .= " AND $column BETWEEN :value1 AND :value2";
        $this->bindValues[':value1'] = $value1;
        $this->bindValues[':value2'] = $value2;

        $this->currentStep = 'between';

        return $this;
    }

    public function having(array $conditions)
    {
        if ($this->currentStep !== 'group') {
            throw new \Exception('Invalid method order. HAVING should come after GROUP BY.');
        }

        $having = [];
        foreach ($conditions as $column => $value) {
            $having[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query .= " HAVING " . implode(' AND ', $having);

        return $this;
    }
}
