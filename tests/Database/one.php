<?php

namespace App\Core;

use PDO;
use PDOException;

class Database_d
{
    private PDO $pdo;

    public function __construct(string $host, string $username, string $password, string $database)
    {
        $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    // ... Previous methods ...

    public function select($table, $columns = ['*'])
    {
        return new QueryBuilder($this->pdo, 'select', $table, $columns);
    }

    public function insert($table)
    {
        return new QueryBuilder($this->pdo, 'insert', $table);
    }

    public function update($table)
    {
        return new QueryBuilder($this->pdo, 'update', $table);
    }

    public function delete($table)
    {
        return new QueryBuilder($this->pdo, 'delete', $table);
    }
}

class QueryBuilder
{
    private PDO $pdo;
    private string $type;
    private string $table;
    private array $columns;
    private array $values = [];
    private string $where = '';
    private string $orderBy = '';
    private string $limit = '';

    public function __construct(PDO $pdo, string $type, string $table, $columns = ['*'])
    {
        $this->pdo = $pdo;
        $this->type = $type;
        $this->table = $table;
        $this->columns = $columns;
    }

    public function where($condition, $value = null)
    {
        if (empty($this->where)) {
            $this->where .= ' WHERE ' . $condition;
        } else {
            $this->where .= ' AND ' . $condition;
        }

        if ($value !== null) {
            $this->values[] = $value;
        }

        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy = " ORDER BY $column $direction";
        return $this;
    }

    public function limit($count)
    {
        $this->limit = " LIMIT $count";
        return $this;
    }

    public function execute()
    {
        $sql = $this->buildQuery();
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->values);

        return $stmt;
    }

    private function buildQuery()
    {
        switch ($this->type) {
            case 'select':
                $columns = implode(', ', $this->columns);
                return "SELECT $columns FROM {$this->table}{$this->where}{$this->orderBy}{$this->limit}";
            case 'insert':
                $columns = implode(', ', array_keys($this->values));
                $placeholders = implode(', ', array_fill(0, count($this->values), '?'));
                return "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
            case 'update':
                $set = implode(', ', array_map(fn($col) => "$col = ?", array_keys($this->values)));
                return "UPDATE {$this->table} SET $set{$this->where}";
            case 'delete':
                return "DELETE FROM {$this->table}{$this->where}";
            default:
                return '';
        }
    }
}
