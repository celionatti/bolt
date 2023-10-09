<?php

declare(strict_types=1);

/**
 * =======================================
 * Bolt - BoltMigration Class ============
 * =======================================
 */

namespace Bolt\Bolt\Migration;

use Bolt\Bolt\Database\Database;

class BoltMigration_working extends Database
{
    private $table;
    private $columns = [];

    public function createTable($tableName)
    {
        $this->table = $tableName;
        return $this;
    }

    public function id()
    {
        $this->columns[] = [
            'name' => 'id',
            'type' => 'INT',
            'constraints' => 'AUTO_INCREMENT PRIMARY KEY',
        ];
        return $this;
    }

    public function varchar($columnName, $length = 255)
    {
        $this->columns[] = [
            'name' => $columnName,
            'type' => "VARCHAR($length)",
        ];
        return $this;
    }

    public function int($columnName)
    {
        $this->columns[] = [
            'name' => $columnName,
            'type' => 'INT',
        ];
        return $this;
    }

    public function bigint($columnName)
    {
        $this->columns[] = [
            'name' => $columnName,
            'type' => 'BIGINT',
        ];
        return $this;
    }

    public function primaryKey()
    {
        // Add primary key constraint to the last added column
        if (!empty($this->columns)) {
            $this->columns[count($this->columns) - 1]['constraints'] .= ' PRIMARY KEY';
        }
        return $this;
    }


    public function uniquekey($columnName)
    {
        // Add a unique constraint to the last added column
        if (!empty($this->columns)) {
            $this->columns[count($this->columns) - 1]['constraints'] .= " UNIQUE KEY ($columnName)";
        }
        return $this;
    }

    public function defaultValue($value)
    {
        // Set a default value for the last added column
        if (!empty($this->columns)) {
            $this->columns[count($this->columns) - 1]['constraints'] .= " DEFAULT '$value'";
        }
        return $this;
    }

    public function nullable()
    {
        // Make the last added column nullable
        if (!empty($this->columns)) {
            $this->columns[count($this->columns) - 1]['constraints'] .= ' NULL';
        }
        return $this;
    }

    public function timestamps()
    {
        $this->columns[] = [
            'name' => 'created_at',
            'type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        ];

        $this->columns[] = [
            'name' => 'updated_at',
            'type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ];

        return $this;
    }

    public function build()
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (";

        foreach ($this->columns as $column) {
            $sql .= "{$column['name']} {$column['type']}";

            if (isset($column['constraints'])) {
                $sql .= " {$column['constraints']}";
            }

            $sql .= ', ';
        }

        $sql = rtrim($sql, ', ');
        $sql .= ') ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4';

        // Execute the SQL query on the database
        if ($this->query($sql)) {
            $this->consoleLog("Table '{$this->table}' created successfully");
        } else {
            $this->consoleLog("Error creating table: {$this->error}");
        }
    }

    public function consoleLog(string $message, bool $die = false, bool $timestamp = true, string $level = 'info'): void
    {
        $output = '';

        if ($timestamp) {
            $output .= "[" . date("Y-m-d H:i:s") . "] - ";
        }

        $output .= ucfirst($message) . PHP_EOL;

        switch ($level) {
            case 'info':
                $output = "\033[0;32m" . $output; // Green color for info
                break;
            case 'warning':
                $output = "\033[0;33m" . $output; // Yellow color for warning
                break;
            case 'error':
                $output = "\033[0;31m" . $output; // Red color for error
                break;
            default:
                break;
        }

        $output .= "\033[0m"; // Reset color

        echo $output;

        if ($die) {
            die();
        }
    }

    private function dd($data)
    {
        var_dump($data);
        die;
    }
}
