<?php

declare(strict_types=1);

/**
 * =======================================
 * Bolt - BoltMigration Class ============
 * =======================================
 */

namespace Bolt\Bolt\Migration;

use Bolt\Bolt\Database\Database;

class BoltMigration extends Database
{
    private $table;
    private $columns = [];
    private $indexes = [];
    public $dataType = "mysql";

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
        if ($this->dataType === 'mysql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => "VARCHAR($length)",
            ];
        } elseif ($this->dataType === 'pgsql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => "VARCHAR($length)",
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    public function int($columnName)
    {
        if ($this->dataType === 'mysql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'INT',
            ];
        } elseif ($this->dataType === 'pgsql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'INT',
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    public function bigint($columnName)
    {
        if ($this->dataType === 'mysql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'BIGINT',
            ];
        } elseif ($this->dataType === 'pgsql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'BIGINT',
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    public function tinyint($columnName)
    {
        if ($this->dataType === 'mysql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'TINYINT',
            ];
        } elseif ($this->dataType === 'pgsql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'SMALLINT',
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    public function autoIncrement()
    {
        if ($this->dataType === 'mysql') {
            // Add an AUTO_INCREMENT column to the last added column
            if (!empty($this->columns)) {
                $this->columns[count($this->columns) - 1]['constraints'] .= ' AUTO_INCREMENT';
            }
        } else {
            $this->consoleLog("AUTO_INCREMENT is not supported in {$this->dataType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    public function text($columnName)
    {
        if ($this->dataType === 'mysql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'TEXT',
            ];
        } elseif ($this->dataType === 'pgsql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'TEXT',
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    public function date($columnName)
    {
        if ($this->dataType === 'mysql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'DATE',
            ];
        } elseif ($this->dataType === 'pgsql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'DATE',
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    public function enum($columnName, array $enumValues)
    {
        $enumValues = array_map([$this, 'quoteValue'], $enumValues);

        if ($this->dataType === 'mysql') {
            $enumValuesStr = implode(',', $enumValues);
            $this->columns[] = [
                'name' => $columnName,
                'type' => "ENUM({$enumValuesStr})",
            ];
        } elseif ($this->dataType === 'pgsql') {
            $enumValuesStr = implode(',', $enumValues);
            $this->columns[] = [
                'name' => $columnName,
                'type' => "TEXT CHECK ({$columnName} IN ({$enumValuesStr}))",
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return $this;
        }

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


    public function uniqueKey($columnName)
    {
        // Add a unique constraint to the last added column
        if (!empty($this->columns)) {
            // Initialize 'constraints' if not already defined
            if (!isset($this->columns[count($this->columns) - 1]['constraints'])) {
                $this->columns[count($this->columns) - 1]['constraints'] = '';
            }
            $this->columns[count($this->columns) - 1]['constraints'] .= " UNIQUE";
        }
        return $this;
    }

    public function defaultValue($value)
    {
        // Set a default value for the last added column
        if (!empty($this->columns)) {
            // Initialize 'constraints' if not already defined
            if (!isset($this->columns[count($this->columns) - 1]['constraints'])) {
                $this->columns[count($this->columns) - 1]['constraints'] = '';
            }
            $this->columns[count($this->columns) - 1]['constraints'] .= " DEFAULT '{$value}'";
        }
        return $this;
    }

    public function nullable()
    {
        // Make the last added column nullable
        if (!empty($this->columns)) {
            // Initialize 'constraints' if not already defined
            if (!isset($this->columns[count($this->columns) - 1]['constraints'])) {
                $this->columns[count($this->columns) - 1]['constraints'] = '';
            }
            $this->columns[count($this->columns) - 1]['constraints'] .= ' NULL';
        }
        return $this;
    }


    public function timestamps()
    {
        if ($this->dataType === 'mysql') {
            $this->columns[] = [
                'name' => 'created_at',
                'type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            ];

            $this->columns[] = [
                'name' => 'updated_at',
                'type' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ];
        } elseif ($this->dataType === 'pgsql') {
            $this->columns[] = [
                'name' => 'created_at',
                'type' => 'TIMESTAMP DEFAULT NOW()',
            ];

            $this->columns[] = [
                'name' => 'updated_at',
                'type' => 'TIMESTAMP DEFAULT NOW()',
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    public function index($columnName, $indexName = null, $unique = false)
    {
        if ($indexName === null) {
            $indexName = "{$this->table}_{$columnName}_index";
        }

        $type = $unique ? 'UNIQUE' : 'INDEX';

        $this->indexes[] = [
            'name' => $indexName,
            'type' => $type,
            'column' => $columnName,
        ];
        return $this;
    }

    public function foreignKey($columnName, $referencedTable, $referencedColumn = 'id', $onDelete = 'CASCADE', $onUpdate = 'CASCADE')
    {
        $constraintName = "{$this->table}_{$columnName}_fk";

        $this->columns[] = [
            'name' => $columnName,
            'type' => 'INT',
        ];

        $this->indexes[] = [
            'name' => $constraintName,
            'type' => 'FOREIGN KEY',
            'column' => $columnName,
            'references' => $referencedTable,
            'refColumn' => $referencedColumn,
            'onDelete' => $onDelete,
            'onUpdate' => $onUpdate,
        ];
        return $this;
    }

    public function dropTable($tableName)
    {
        $sql = "DROP TABLE IF EXISTS {$tableName}";
        if ($this->query($sql)) {
            $this->consoleLog("Table '{$tableName}' dropped successfully");
        } else {
            $this->consoleLog("Error dropping table: {$this->error}", true, true, 'error');
        }
    }

    public function renameTable($oldTableName, $newTableName)
    {
        if ($this->dataType === 'mysql') {
            $sql = "ALTER TABLE {$oldTableName} RENAME TO {$newTableName}";
        } elseif ($this->dataType === 'pgsql') {
            $sql = "ALTER TABLE {$oldTableName} RENAME TO {$newTableName}";
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return;
        }

        if ($this->query($sql)) {
            $this->consoleLog("Table '{$oldTableName}' renamed to '{$newTableName}' successfully");
        } else {
            $this->consoleLog("Error renaming table: {$this->error}", true, true, 'error');
        }
    }

    public function addColumn($columnName, $type, $after = null)
    {
        if ($this->dataType === 'mysql') {
            $sql = "ALTER TABLE {$this->table} ADD COLUMN {$columnName} {$type}";
        } elseif ($this->dataType === 'pgsql') {
            $sql = "ALTER TABLE {$this->table} ADD COLUMN {$columnName} {$type}";
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return;
        }

        if ($after !== null) {
            $sql .= " AFTER {$after}";
        }

        if ($this->query($sql)) {
            $this->consoleLog("Column '{$columnName}' added to '{$this->table}' successfully");
        } else {
            $this->consoleLog("Error adding column: {$this->error}", true, true, 'error');
        }
    }

    public function modifyColumn($columnName, $newType)
    {
        if ($this->dataType === 'mysql') {
            $sql = "ALTER TABLE {$this->table} MODIFY COLUMN {$columnName} {$newType}";
        } elseif ($this->dataType === 'pgsql') {
            $sql = "ALTER TABLE {$this->table} ALTER COLUMN {$columnName} TYPE {$newType}";
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return;
        }

        if ($this->query($sql)) {
            $this->consoleLog("Column '{$columnName}' modified successfully");
        } else {
            $this->consoleLog("Error modifying column: {$this->error}", true, true, 'error');
        }
    }

    public function dropColumn($columnName)
    {
        if ($this->dataType === 'mysql') {
            $sql = "ALTER TABLE {$this->table} DROP COLUMN {$columnName}";
        } elseif ($this->dataType === 'pgsql') {
            $sql = "ALTER TABLE {$this->table} DROP COLUMN {$columnName}";
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return;
        }

        if ($this->query($sql)) {
            $this->consoleLog("Column '{$columnName}' dropped successfully");
        } else {
            $this->consoleLog("Error dropping column: {$this->error}", true, true, 'error');
        }
    }

    public function createView($viewName, $sqlQuery)
    {
        if ($this->dataType === 'mysql') {
            $sql = "CREATE VIEW {$viewName} AS {$sqlQuery}";
        } elseif ($this->dataType === 'pgsql') {
            $sql = "CREATE VIEW {$viewName} AS {$sqlQuery}";
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return;
        }

        if ($this->query($sql)) {
            $this->consoleLog("View '{$viewName}' created successfully");
        } else {
            $this->consoleLog("Error creating view: {$this->error}", true, true, 'error');
        }
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

        foreach ($this->indexes as $index) {
            $sql .= "{$index['type']} {$index['name']} ({$index['column']}), ";
        }

        $sql = rtrim($sql, ', ');

        if ($this->dataType === 'mysql') {
            $sql .= ') ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4';
        } elseif ($this->dataType === 'pgsql') {
            $sql .= ')';
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return;
        }

        // Execute the SQL query on the database
        if ($this->query($sql)) {
            $this->consoleLog("Table '{$this->table}' created successfully");
        } else {
            $this->consoleLog("Error creating table: {$this->error}", true, true, 'error');
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

    private function quoteValue($value)
    {
        return "'" . addslashes($value) . "'";
    }
}
