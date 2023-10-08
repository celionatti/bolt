<?php

declare(strict_types=1);

/**
 * ========================================
 * Bolt - Database ========================
 * ========================================
 */

namespace Bolt\Bolt\Database;

use Bolt\Bolt\Config;
use Bolt\Bolt\QueryBuilder\BoltQueryBuilder;
use PDO;
use PDOException;


class Database
{
    public static $query_id = '';
    public int $affected_rows = 0;
    public int $insert_id = 0;
    public $error = '';
    public bool $has_error = false;

    private $connection;
    public $transactionLevel = 0;
    public $missing_tables = [];

    private static $instance;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $config = Config::get("database");
        // Replace $np_vars with your actual configuration
        $np_vars = [
            'DB_DRIVER'     => $config["driver"] ?? 'mysql',
            'DB_HOST'       => $config["host"] ?? 'localhost',
            'DB_NAME'       => $config["dbname"] ?? 'bolt',
            'DB_USER'       => $config["username"] ?? 'root',
            'DB_PASSWORD'   => $config["password"] ?? '',
        ];

        $string = "{$np_vars['DB_DRIVER']}:host={$np_vars['DB_HOST']};dbname={$np_vars['DB_NAME']}";

        try {
            $con = new PDO($string, $np_vars['DB_USER'], $np_vars['DB_PASSWORD']);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $con->setAttribute(PDO::ATTR_PERSISTENT, true);
            $con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            $this->handleDatabaseError($e->getMessage());
        }

        $this->connection = $con;
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function beginTransaction()
    {
        if ($this->transactionLevel === 0) {
            try {
                $this->connection->beginTransaction();
            } catch (PDOException $e) {
                $this->handleDatabaseError($e->getMessage());
            }
        }
        $this->transactionLevel++;
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

    public function query(string $query, array $data = [], string $data_type = 'object')
    {
        $this->error = '';
        $this->has_error = false;

        try {
            // Start a transaction
            $this->beginTransaction();

            $stm = $this->connection->prepare($query);

            $result = $stm->execute($data);
            $this->affected_rows = $stm->rowCount();
            $this->insert_id = $this->connection->lastInsertId();

            if ($result) {
                if ($data_type == 'object') {
                    $rows = $stm->fetchAll(PDO::FETCH_OBJ);
                } elseif ($data_type == 'assoc') {
                    $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $rows = $stm->fetchAll(PDO::FETCH_CLASS);
                }
            }
            // Commit the transaction if the query was successful
            $this->commitTransaction();
        } catch (PDOException $e) {
            // Rollback the transaction on error
            $this->rollbackTransaction();
            $this->error = $e->getMessage();
            $this->has_error = true;
        }

        $arr = [];
        $arr['query'] = $query;
        $arr['data'] = $data;
        $arr['result'] = $rows ?? [];
        $arr['query_id'] = self::$query_id;
        self::$query_id = '';

        if (is_array($arr) && count($arr) > 0) {
            return $arr;
        }

        return false;
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
