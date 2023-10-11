<?php

declare(strict_types=1);

/**
 * ========================================
 * Bolt - Database ========================
 * ========================================
 */

namespace Bolt\Bolt\Database;

use PDO;
use PDOException;
use Bolt\Bolt\Config;
use Bolt\Bolt\BoltQueryBuilder\BoltQueryBuilder;


class Database
{
    public static $query_id = '';
    public int $affected_rows = 0;
    public mixed $insert_id = 0;
    public $error = '';
    public bool $has_error = false;

    private $connection;
    public $transactionLevel = 0;
    public $missing_tables = [];

    private static $instances = [];

    public $databaseType;

    public function __construct()
    {
        $database = [
            "drivers" => "mysql",
            "host" => "127.0.0.1",
            "dbname" => "",
            "username" => "",
            "password" => ""
        ];

        $config = Config::get(BOLT_DATABASE, $database);
        $this->connect($config);
    }

    private function connect($config)
    {
        // Replace $np_vars with your actual configuration
        $np_vars = [
            'DB_DRIVERS'     => $config["drivers"],
            'DB_HOST'       => $config["host"],
            'DB_NAME'       => $config["dbname"],
            'DB_USER'       => $config["username"],
            'DB_PASSWORD'   => $config["password"],
        ];

        $string = "{$np_vars['DB_DRIVERS']}:host={$np_vars['DB_HOST']};dbname={$np_vars['DB_NAME']}";

        try {
            $this->connection = new PDO($string, $np_vars['DB_USER'], $np_vars['DB_PASSWORD']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->connection->setAttribute(PDO::ATTR_PERSISTENT, true);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->handleDatabaseError($e->getMessage());
        }

        return $this->connection;
    }

    public static function getInstance(string $connectionName = 'database')
    {
        if (!isset(self::$instances[$connectionName])) {
            self::$instances[$connectionName] = new self($connectionName);
        }

        return self::$instances[$connectionName];
    }

    public function secureQuery(string $query, array $data = [], string $data_type = 'object')
    {
        // Implement input validation and proper escaping to prevent SQL injection attacks
        // ...

        return $this->query($query, $data, $data_type);
    }

    public function beginTransaction()
    {
        try {
            if ($this->transactionLevel === 0) {
                $this->connection->beginTransaction();
            }
            $this->transactionLevel++;
        } catch (PDOException $e) {
            $this->handleDatabaseError($e->getMessage());
        }
    }

    public function createSavepoint(string $savepoint)
    {
        try {
            $this->connection->exec("SAVEPOINT $savepoint");
        } catch (PDOException $e) {
            $this->handleDatabaseError($e->getMessage());
        }
    }

    public function rollbackToSavepoint(string $savepoint)
    {
        try {
            $this->connection->exec("ROLLBACK TO SAVEPOINT $savepoint");
        } catch (PDOException $e) {
            $this->handleDatabaseError($e->getMessage());
        }
    }

    public function commitTransaction()
    {
        if ($this->transactionLevel === 1) {
            try {
                $this->connection->commit();
            } catch (PDOException $e) {
                $this->handleDatabaseError($e->getMessage());
            }
        }
        $this->transactionLevel = max(0, $this->transactionLevel - 1);
    }

    public function rollbackTransaction()
    {
        if ($this->transactionLevel === 1) {
            try {
                $this->connection->rollBack();
            } catch (PDOException $e) {
                $this->handleDatabaseError($e->getMessage());
            }
        }
        $this->transactionLevel = max(0, $this->transactionLevel - 1);
    }

    public function setDatabaseType(string $databaseType)
    {
        // Validate and set the database type
        if (in_array($databaseType, ['mysql', 'pgsql'])) {
            $this->databaseType = $databaseType;
        } else {
            $this->handleDatabaseError("Unsupported database type: {$databaseType}");
        }
    }

    public function getDatabaseType()
    {
        return $this->databaseType;
    }

    public function queryBuilder($table)
    {
        return new BoltQueryBuilder($this->connection, $table);
    }

    private function handleDatabaseError($errorMessage)
    {
        $this->error = $errorMessage;
        $this->has_error = true;

        // Example: Log error to a file
        error_log("Database Error: $errorMessage");

        // You can also throw an exception if desired
        bolt_die("Database Error", $errorMessage);
    }

    public function get_row(string $query, array $data = [], string $data_type = 'object')
    {
        $result = $this->query($query, $data, $data_type);
        if (is_array($result) && count($result) > 0) {
            return $result[0];
        }

        return false;
    }

    // public function query(string $query, array $data = [], string $data_type = 'object')
    // {
    //     $this->error = '';
    //     $this->has_error = false;

    //     try {
    //         $stm = $this->connection->prepare($query);

    //         $result = $stm->execute($data);
    //         $this->affected_rows = $stm->rowCount();
    //         $this->insert_id = $this->connection->lastInsertId();

    //         if ($result) {
    //             if ($data_type == 'object') {
    //                 $rows = $stm->fetchAll(PDO::FETCH_OBJ);
    //             } elseif ($data_type == 'assoc') {
    //                 $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
    //             } else {
    //                 $rows = $stm->fetchAll(PDO::FETCH_CLASS);
    //             }
    //         }
    //     } catch (PDOException $e) {
    //         $this->error = $e->getMessage();
    //         $this->has_error = true;
    //     }

    //     $arr = [];
    //     $arr['query'] = $query;
    //     $arr['data'] = $data;
    //     $arr['result'] = $rows ?? [];
    //     $arr['query_id'] = self::$query_id;
    //     self::$query_id;

    //     if (is_array($arr) && count($arr) > 0) {
    //         return $arr;
    //     }

    //     return false;
    // }

    public function query(string $query, array $params = [], string $data_type = 'object')
    {
        $this->error = '';
        $this->has_error = false;

        try {
            $stmt = $this->connection->prepare($query);

            // Bind named parameters if provided
            foreach ($params as $paramName => $paramValue) {
                $stmt->bindValue(":" . $paramName, $paramValue);
            }

            $result = $stmt->execute();

            $this->affected_rows = $stmt->rowCount();
            $this->insert_id = $this->connection->lastInsertId();

            if ($result) {
                if ($data_type == 'object') {
                    $rows = $stmt->fetchAll(PDO::FETCH_OBJ);
                } elseif ($data_type == 'assoc') {
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $rows = $stmt->fetchAll(PDO::FETCH_CLASS);
                }
            }
        } catch (PDOException $e) {
            // Log the error
            error_log("Database Query Error: " . $e->getMessage());

            // Handle the error based on your application's needs
            // For example, you can throw a custom exception or return an error response
            $this->error = $e->getMessage();
            $this->has_error = true;
        }

        $resultData = [
            'query' => $query,
            'params' => $params,
            'result' => $rows ?? [],
            'query_id' => self::$query_id,
        ];
        self::$query_id = '';

        return $resultData;
    }




    public function table_exists(string|array $tables): bool
    {
        if (!is_array($tables)) {
            $tables = [$tables];
        }

        $this->error = '';
        $this->has_error = false;

        try {
            $existingTables = [];

            // Fetch existing table names from the database
            $stmt = $this->connection->prepare('SHOW TABLES');
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($result !== false) {
                $existingTables = $result;
            }

            // Check if all specified tables exist
            foreach ($tables as $table) {
                if (!in_array($table, $existingTables)) {
                    $this->missing_tables[] = $table;
                }
            }

            return empty($this->missing_tables);
        } catch (PDOException $e) {
            $this->handleDatabaseError($e->getMessage());
            return false;
        }
    }
}
