<?php

declare(strict_types=1);

/**
 * ========================================
 * Bolt - Database ========================
 * ========================================
 */

namespace celionatti\Bolt\Database;

use PDO;
use PDOException;
use celionatti\Bolt\BoltQueryBuilder\QueryBuilder;
use celionatti\Bolt\Database\Exception\DatabaseException;


class Database
{
    private PDO $connection;

    public function __construct(array $config = null)
    {
        $config = $config ?? $this->loadDefaultConfig();
        $this->connect($config);
    }

    private function loadDefaultConfig(): array
    {
        return [
            "drivers" => DB_DRIVERS ?? bolt_env("DB_DRIVERS"),
            "host" => DB_HOST ?? bolt_env("DB_HOST"),
            "dbname" => DB_NAME ?? bolt_env("DB_DATABASE"),
            "username" => DB_USERNAME ?? bolt_env("DB_USERNAME"),
            "password" => DB_PASSWORD ?? bolt_env("DB_PASSWORD")
        ];
    }

    private function connect(array $config): void
    {
        $np_vars = [
            'DB_DRIVERS' => $config["drivers"] ?? bolt_env("DB_DRIVERS"),
            'DB_HOST' => $config["host"] ?? bolt_env("DB_HOST"),
            'DB_NAME' => $config["dbname"] ?? bolt_env("DB_NAME"),
            'DB_USER' => $config["username"] ?? bolt_env("DB_USERNAME"),
            'DB_PASSWORD' => $config["password"] ?? bolt_env("DB_PASSWORD"),
        ];

        try {
            if ($np_vars['DB_DRIVERS'] === 'mysql') {
                $dsn = "mysql:host={$np_vars['DB_HOST']};dbname={$np_vars['DB_NAME']};charset=utf8mb4";
                $this->connection = new PDO($dsn, $np_vars['DB_USER'], $np_vars['DB_PASSWORD'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
                ]);
            } elseif ($np_vars['DB_DRIVERS'] === 'sqlite') {
                $dsn = "sqlite:{$np_vars['DB_NAME']}";
                $this->connection = new PDO($dsn, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
                ]);
            }
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(), $e->getCode(), "info");
        }
    }

    public function reconnect(): void
    {
        $config = $this->loadDefaultConfig();
        $this->connect($config);
    }

    private function logQuery(string $query, array $params = []): void
    {
        file_put_contents('query.log', date('Y-m-d H:i:s') . " - " . $query . " - " . json_encode($params) . PHP_EOL, FILE_APPEND);
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    public function prepare($query)
    {
        return $this->connection->prepare($query);
    }

    public function queryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->connection);
    }

    public function query(string $query, array $params = [], string $data_type = 'object')
    {
        try {
            $this->logQuery($query, $params);
            $stmt = $this->connection->prepare($query);
            foreach ($params as $paramName => $paramValue) {
                $stmt->bindValue(":{$paramName}", $paramValue);
            }
            $result = $stmt->execute();

            if ($result) {
                $rows = match ($data_type) {
                    'object' => $stmt->fetchAll(PDO::FETCH_OBJ),
                    'assoc' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                    default => $stmt->fetchAll(PDO::FETCH_CLASS),
                };
            }

            $resultData = [
                'query' => $query,
                'params' => $params,
                'result' => $rows ?? [],
                'count' => $stmt->rowCount(),
                'query_id' => $this->connection->lastInsertId(),
            ];
        } catch (PDOException $e) {
            throw new DatabaseException("Database Query Error: {$e->getMessage()}", $e->getCode(), "info");
        }

        return $resultData;
    }
}
