<?php

class Database
{
    // ... (existing class properties and methods)

    private static $instances = [];

    public static function getInstance(string $connectionName = 'default')
    {
        if (!isset(self::$instances[$connectionName])) {
            self::$instances[$connectionName] = new self($connectionName);
        }

        return self::$instances[$connectionName];
    }

    public function __construct(string $connectionName = 'default')
    {
        $this->connect($connectionName);
    }

    private function connect(string $connectionName)
    {
        $config = Config::get("database", $connectionName); // Use a specific connection configuration

        // ... (Rest of the connection logic remains the same)

        $this->connection = $con;
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

    // Add support for nested transactions and savepoints
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

    // ... (Other methods remain the same)

    // Add support for different database types (MySQL, PostgreSQL, etc.)
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
}
