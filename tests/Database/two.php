<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
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

    public function query(string $sql, array $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception("Database query error: " . $e->getMessage());
        }
    }

    public function fetchAll(string $sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetch(string $sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function execute(string $sql, array $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollback()
    {
        $this->pdo->rollBack();
    }
}
