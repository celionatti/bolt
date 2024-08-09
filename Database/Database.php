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
    private static ?Database $instance = null;
    private PDO $connection;
    private array $config;

    private function __construct(array $config = null)
    {
        $this->config = $config ?? $this->loadDefaultConfig();
        $this->connect($this->config);
    }

    public static function getInstance(array $config = null): Database
    {
        if (self::$instance === null) {
            self::$instance = new self($config);
        }
        return self::$instance;
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
            $dsn = match ($np_vars['DB_DRIVERS']) {
                'mysql' => "mysql:host={$np_vars['DB_HOST']};dbname={$np_vars['DB_NAME']};charset=utf8mb4",
                'sqlite' => "sqlite:{$np_vars['DB_NAME']}",
                default => throw new DatabaseException("Unsupported database driver: {$np_vars['DB_DRIVERS']}", 500, "error"),
            };

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            ];

            if ($np_vars['DB_DRIVERS'] === 'mysql') {
                $options[PDO::ATTR_EMULATE_PREPARES] = false;
                $options[PDO::ATTR_PERSISTENT] = true;
            }

            $this->connection = new PDO($dsn, $np_vars['DB_USER'], $np_vars['DB_PASSWORD'], $options);
        } catch (PDOException $e) {
            throw new DatabaseException($e->getMessage(), $e->getCode(), "error");
        }
    }

    public function reconnect(): void
    {
        $this->connect($this->config);
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

    public function prepare(string $query)
    {
        return $this->connection->prepare($query);
    }

    public function queryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->connection);
    }

    public function query(string $query, array $params = [], string $data_type = 'object'): array
    {
        try {
            $this->logQuery($query, $params);
            $stmt = $this->connection->prepare($query);
            foreach ($params as $paramName => $paramValue) {
                $stmt->bindValue(":{$paramName}", $paramValue);
            }
            $stmt->execute();

            $rows = match ($data_type) {
                'object' => $stmt->fetchAll(PDO::FETCH_OBJ),
                'assoc' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                default => $stmt->fetchAll(PDO::FETCH_CLASS),
            };

            $resultData = [
                'query' => $query,
                'params' => $params,
                'result' => $rows ?? [],
                'count' => $stmt->rowCount(),
                'query_id' => $this->connection->lastInsertId(),
            ];

            return $resultData;
        } catch (PDOException $e) {
            throw new DatabaseException("Database Query Error: {$e->getMessage()}", $e->getCode(), "error");
        }
    }

    public function fetchAll(string $query, array $params = [], string $data_type = 'object'): array
    {
        return $this->query($query, $params, $data_type)['result'];
    }

    public function fetchOne(string $query, array $params = [], string $data_type = 'object')
    {
        $result = $this->fetchAll($query, $params, $data_type);
        return $result[0] ?? null;
    }
}
