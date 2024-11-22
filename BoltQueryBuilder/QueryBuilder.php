<?php

declare(strict_types=1);

/**
 * ========================================
 * Bolt - Query Builder ===================
 * ========================================
 */

namespace celionatti\Bolt\BoltQueryBuilder;

use PDO;


class QueryBuilder
{
    protected $pdo;
    protected $union = [];
    protected $select = [];
    protected $from;
    protected $join = [];
    protected $where = [];
    protected $orderBy = [];
    protected $groupBy = [];
    protected $having = [];
    protected $limit;
    protected $offset;
    protected $bindings = [];
    protected $type = 'select';
    protected $insert = [];
    protected $update = [];
    protected $delete = false;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function subQuery(callable $callback)
    {
        $subQuery = new self($this->pdo);
        $callback($subQuery);
        return $subQuery->toSql();
    }

    public function union(QueryBuilder $queryBuilder)
    {
        $this->union[] = $queryBuilder->toSql();
        return $this;
    }

    public function select($columns = ['*'])
    {
        $this->type = 'select';
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function table($table)
    {
        $this->from = $table;
        return $this;
    }

    public function from($table)
    {
        $this->from = $table;
        return $this;
    }

    public function join($table, $first, $operator, $second, $type = 'INNER')
    {
        $this->join[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }

    public function where($column, $operator = '=', $value)
    {
        $param = $this->generateParamName($column);
        $this->where[] = "$column $operator :$param";
        $this->bindings[$param] = $value;
        return $this;
    }

    public function whereIn($column, array $values)
    {
        $placeholders = implode(',', array_fill(0, count($values), '?'));
        $this->where[] = "$column IN ($placeholders)";
        $this->bindings = array_merge($this->bindings, $values);
        return $this;
    }

    public function whereNested(callable $callback, $boolean = 'AND')
    {
        $query = new static($this->pdo);
        $callback($query);
        $nestedWhere = '(' . implode(" $boolean ", $query->where) . ')';
        $this->where[] = $nestedWhere;
        $this->bindings = array_merge($this->bindings, $query->bindings);
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = "$column $direction";
        return $this;
    }

    public function groupBy($column)
    {
        $this->groupBy[] = $column;
        return $this;
    }

    public function having($column, $operator = '=', $value)
    {
        $param = $this->generateParamName($column);
        $this->having[] = "$column $operator :$param";
        $this->bindings[$param] = $value;
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function count($column = '*')
    {
        $this->select = ["COUNT($column) AS count"];
        return $this;
    }

    public function first($columns = ['*'])
    {
        $this->select($columns);
        $this->limit(1);
        return $this->execute()[0] ?? null; // return the first result or null if empty
    }

    public function insert($table, array $data)
    {
        $this->type = 'insert';
        $this->from = $table;
        $this->insert = $data;
        return $this;
    }

    public function upsert(array $data, array $updateColumns)
    {
        $this->type = 'insert';
        $this->insert = $data;
        $columns = array_keys($data);
        $params = array_map(fn($column) => ':' . $column, $columns);
    
        $update = implode(', ', array_map(fn($column) => "$column = VALUES($column)", $updateColumns));
    
        $sql = 'INSERT INTO ' . $this->from .
               ' (' . implode(', ', $columns) . ')' .
               ' VALUES (' . implode(', ', $params) . ')' .
               ' ON DUPLICATE KEY UPDATE ' . $update;
    
        foreach ($data as $column => $value) {
            $this->bindings[$column] = $value;
        }
    
        return $sql;
    }

    public function update($table, array $data)
    {
        $this->type = 'update';
        $this->from = $table;
        $this->update = $data;
        return $this;
    }

    public function delete($table)
    {
        $this->type = 'delete';
        $this->from = $table;
        $this->delete = true;
        return $this;
    }

    protected function generateParamName($column)
    {
        return str_replace('.', '_', $column) . '_' . count($this->bindings);
    }

    public function toSql()
    {
        switch ($this->type) {
            case 'insert':
                return $this->buildInsert();
            case 'update':
                return $this->buildUpdate();
            case 'delete':
                return $this->buildDelete();
            case 'select':
            default:
                return $this->buildSelect();
        }
    }

    public function getLastQuery()
    {
        $sql = $this->toSql();
        foreach ($this->bindings as $key => $value) {
            $sql = str_replace(":$key", $this->pdo->quote($value), $sql);
        }
        return $sql;
    }

    public function paginate($perPage = 10, $currentPage = 1)
    {
        $this->limit($perPage)->offset(($currentPage - 1) * $perPage);
        $results = $this->execute();
        $totalQuery = clone $this;
        $totalQuery->select('COUNT(*) as total')->limit(null)->offset(null);
        $total = $totalQuery->execute()[0]['total'] ?? 0;
    
        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => ceil($total / $perPage),
        ];
    }

    protected function buildSelect()
    {
        $sql = 'SELECT ' . implode(', ', $this->select) . ' FROM ' . $this->from;
        if ($this->join) {
            foreach ($this->join as $join) {
                $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
            }
        }
        if ($this->where) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }
        if ($this->groupBy) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }
        if ($this->having) {
            $sql .= ' HAVING ' . implode(' AND ', $this->having);
        }
        if ($this->orderBy) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }
        if ($this->limit) {
            $sql .= ' LIMIT ' . $this->limit;
        }
        if ($this->offset) {
            $sql .= ' OFFSET ' . $this->offset;
        }
        if ($this->union) {
            $sql .= ' UNION ' . implode(' UNION ', $this->union);
        }
        return $sql;
    }

    protected function buildInsert()
    {
        $columns = array_keys($this->insert);
        $params = array_map(function ($column) {
            return ':' . $column;
        }, $columns);

        $sql = 'INSERT INTO ' . $this->from . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $params) . ')';

        foreach ($this->insert as $column => $value) {
            $this->bindings[$column] = $value;
        }

        return $sql;
    }

    protected function buildUpdate()
    {
        $set = [];
        foreach ($this->update as $column => $value) {
            $param = $this->generateParamName($column);
            $set[] = "$column = :$param";
            $this->bindings[$param] = $value;
        }

        $sql = 'UPDATE ' . $this->from . ' SET ' . implode(', ', $set);

        if ($this->where) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        return $sql;
    }

    protected function buildDelete()
    {
        $sql = 'DELETE FROM ' . $this->from;

        if ($this->where) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        return $sql;
    }

    // public function execute($fetchMode = PDO::FETCH_ASSOC, $className = null)
    // {
    //     $stmt = $this->pdo->prepare($this->toSql());
    //     $stmt->execute($this->bindings);

    //     if ($this->type === 'select') {
    //         if ($fetchMode === PDO::FETCH_CLASS && $className !== null) {
    //             return $stmt->fetchAll($fetchMode, $className);
    //         } else {
    //             return $stmt->fetchAll($fetchMode);
    //         }
    //     }

    //     return $stmt->rowCount();
    // }

    public function execute($fetchMode = PDO::FETCH_ASSOC, $className = null)
    {
        try {
            $stmt = $this->pdo->prepare($this->toSql());
            $stmt->execute($this->bindings);
    
            if ($this->type === 'select') {
                if ($fetchMode === PDO::FETCH_CLASS && $className !== null) {
                    return $stmt->fetchAll($fetchMode, $className);
                } else {
                    return $stmt->fetchAll($fetchMode);
                }
            }
    
            return $stmt->rowCount();
        } catch (\PDOException $e) {
            throw new \Exception("Query execution failed: " . $e->getMessage() . "\nSQL: " . $this->getLastQuery());
        }
    }
}
