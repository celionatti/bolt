<?php

declare(strict_types=1);

namespace Bolt\Bolt\Migration;

use Bolt\Bolt\Database\Database;

class AdvancedBoltMigration extends Database
{
    private $table;
    private $columns = [];
    private $indexes = [];
    private $dbType;

    public function __construct($dbType)
    {
        parent::__construct();
        $this->dbType = $dbType;
    }

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

    // Add methods for other data types (e.g., text, date, enum)

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
        if ($this->dbType === 'mysql') {
            $sql = "ALTER TABLE {$oldTableName} RENAME TO {$newTableName}";
        } elseif ($this->dbType === 'pgsql') {
            $sql = "ALTER TABLE {$oldTableName} RENAME TO {$newTableName}";
        } else {
            $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
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
        if ($this->dbType === 'mysql') {
            $sql = "ALTER TABLE {$this->table} ADD COLUMN {$columnName} {$type}";
        } elseif ($this->dbType === 'pgsql') {
            $sql = "ALTER TABLE {$this->table} ADD COLUMN {$columnName} {$type}";
        } else {
            $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
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
        if ($this->dbType === 'mysql') {
            $sql = "ALTER TABLE {$this->table} MODIFY COLUMN {$columnName} {$newType}";
        } elseif ($this->dbType === 'pgsql') {
            $sql = "ALTER TABLE {$this->table} ALTER COLUMN {$columnName} TYPE {$newType}";
        } else {
            $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
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
        if ($this->dbType === 'mysql') {
            $sql = "ALTER TABLE {$this->table} DROP COLUMN {$columnName}";
        } elseif ($this->dbType === 'pgsql') {
            $sql = "ALTER TABLE {$this->table} DROP COLUMN {$columnName}";
        } else {
            $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
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
        if ($this->dbType === 'mysql') {
            $sql = "CREATE VIEW {$viewName} AS {$sqlQuery}";
        } elseif ($this->dbType === 'pgsql') {
            $sql = "CREATE VIEW {$viewName} AS {$sqlQuery}";
        } else {
            $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
            return;
        }

        if ($this->query($sql)) {
            $this->consoleLog("View '{$viewName}' created successfully");
        } else {
            $this->consoleLog("Error creating view: {$this->error}", true, true, 'error');
        }
    }

    // Additional advanced methods and features can be added here

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

        if ($this->dbType === 'mysql') {
            $sql .= ') ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4';
        } elseif ($this->dbType === 'pgsql') {
            $sql .= ')';
        } else {
            $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
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
        // ... (unchanged)
    }

    private function dd($data)
    {
        // ... (unchanged)
    }

    // ...

    public function text($columnName)
    {
        if ($this->dbType === 'mysql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'TEXT',
            ];
        } elseif ($this->dbType === 'pgsql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'TEXT',
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    public function date($columnName)
    {
        if ($this->dbType === 'mysql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'DATE',
            ];
        } elseif ($this->dbType === 'pgsql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => 'DATE',
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    public function enum($columnName, array $enumValues)
    {
        $enumValues = array_map([$this, 'quoteValue'], $enumValues);

        if ($this->dbType === 'mysql') {
            $enumValuesStr = implode(',', $enumValues);
            $this->columns[] = [
                'name' => $columnName,
                'type' => "ENUM({$enumValuesStr})",
            ];
        } elseif ($this->dbType === 'pgsql') {
            $enumValuesStr = implode(',', $enumValues);
            $this->columns[] = [
                'name' => $columnName,
                'type' => "TEXT CHECK ({$columnName} IN ({$enumValuesStr}))",
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    private function quoteValue($value)
    {
        return "'" . addslashes($value) . "'";
    }

    // ...


    // ...

public function varchar($columnName, $length = 255)
{
    if ($this->dbType === 'mysql') {
        $this->columns[] = [
            'name' => $columnName,
            'type' => "VARCHAR($length)",
        ];
    } elseif ($this->dbType === 'pgsql') {
        $this->columns[] = [
            'name' => $columnName,
            'type' => "VARCHAR($length)",
        ];
    } else {
        $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
        return $this;
    }

    return $this;
}

public function bigint($columnName)
{
    if ($this->dbType === 'mysql') {
        $this->columns[] = [
            'name' => $columnName,
            'type' => 'BIGINT',
        ];
    } elseif ($this->dbType === 'pgsql') {
        $this->columns[] = [
            'name' => $columnName,
            'type' => 'BIGINT',
        ];
    } else {
        $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
        return $this;
    }

    return $this;
}

public function int($columnName)
{
    if ($this->dbType === 'mysql') {
        $this->columns[] = [
            'name' => $columnName,
            'type' => 'INT',
        ];
    } elseif ($this->dbType === 'pgsql') {
        $this->columns[] = [
            'name' => $columnName,
            'type' => 'INT',
        ];
    } else {
        $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
        return $this;
    }

    return $this;
}

public function tinyint($columnName)
{
    if ($this->dbType === 'mysql') {
        $this->columns[] = [
            'name' => $columnName,
            'type' => 'TINYINT',
        ];
    } elseif ($this->dbType === 'pgsql') {
        $this->columns[] = [
            'name' => $columnName,
            'type' => 'SMALLINT',
        ];
    } else {
        $this->consoleLog("Unsupported database type: {$this->dbType}", true, true, 'error');
        return $this;
    }

    return $this;
}

public function autoIncrement()
{
    if ($this->dbType === 'mysql') {
        // Add an AUTO_INCREMENT column to the last added column
        if (!empty($this->columns)) {
            $this->columns[count($this->columns) - 1]['constraints'] .= ' AUTO_INCREMENT';
        }
    } else {
        $this->consoleLog("AUTO_INCREMENT is not supported in {$this->dbType}", true, true, 'error');
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

// ...


}
