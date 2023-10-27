<?php

declare(strict_types=1);

/**
 * =======================================
 * Bolt - BoltMigration Class ============
 * =======================================
 */

namespace celionatti\Bolt\Migration;

use celionatti\Bolt\Database\Database;

class BoltMigration extends Database
{
    private $table;
    private $columns = [];
    private $indexes = [];
    private $foreignKeys = [];
    public $dataType = "mysql";

    /**
     * Create a new table with the specified name.
     *
     * @param string $tableName The name of the table to create.
     * @return $this
     */
    public function createTable($tableName)
    {
        $this->table = $tableName;
        return $this;
    }

    /**
     * Define an 'id' column with optional BIGINT as Type default is INT.
     *
     * @param boolean $bigint
     * @return $this
     */
    public function id($bigint = false)
    {
        $this->columns[] = [
            'name' => 'id',
            'type' => $bigint ? 'INT' : 'BIGINT',
            'constraints' => 'AUTO_INCREMENT PRIMARY KEY',
        ];
        return $this;
    }

    /**
     * Define a VARCHAR column with the specified name and optional length.
     *
     * @param string $columnName The name of the VARCHAR column.
     * @param integer $length (Optional) The length of the VARCHAR column (default: 255).
     * @return $this
     */
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

    /**
     * Define an INT column with the specified name.
     *
     * @param string $columnName The name of the INT column.
     * @return $this
     */
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

    /**
     * Define a BIGINT column with the specified name.
     *
     * @param string $columnName The name of the BIGINT column.
     * @return $this
     */
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

    /**
     * Define a TINYINT column with the specified name.
     *
     * @param string $columnName The name of the TINYINT column.
     * @return $this
     */
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

    /**
     * Add AUTO_INCREMENT constraint to the last added column (for MySQL).
     *
     * @return $this
     */
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

    /**
     * Define a TEXT column with the specified name.
     *
     * @param string $columnName The name of the TEXT column.
     * @return $this
     */
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

    /**
     * Define a DATE column with the specified name.
     *
     * @param string $columnName The name of the DATE column.
     * @return $this
     */
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

    /**
     * Define an ENUM column with the specified name and possible values.
     *
     * @param string $columnName The name of the ENUM column.
     * @param array $enumValues An array of possible ENUM values.
     * @return $this
     */
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

    /**
     * Add PRIMARY KEY constraint to the last added column.
     *
     * @return $this
     */
    public function primaryKey()
    {
        // Add primary key constraint to the last added column
        if (!empty($this->columns)) {
            $this->columns[count($this->columns) - 1]['constraints'] .= ' PRIMARY KEY';
        }
        return $this;
    }

    /**
     * Add a UNIQUE constraint to the last added column.
     *
     * @param string $columnName The name of the column to make unique.
     * @return $this
     */
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

    /**
     * Set a default value for the last added column.
     *
     * @param mixed $value The default value to set.
     * @return $this
     */
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

    /**
     * Make the last added column nullable.
     *
     * @return $this
     */
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

    public function timestamp($columnName, $useCurrent = false)
    {
        if ($this->dataType === 'mysql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => $useCurrent ? 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP' : 'TIMESTAMP',
            ];
        } elseif ($this->dataType === 'pgsql') {
            $this->columns[] = [
                'name' => $columnName,
                'type' => $useCurrent ? 'TIMESTAMPTZ DEFAULT NOW()' : 'TIMESTAMPTZ',
            ];
        } else {
            $this->consoleLog("Unsupported database type: {$this->dataType}", true, true, 'error');
            return $this;
        }

        return $this;
    }

    /**
     * Add 'created_at' and 'updated_at' TIMESTAMP columns.
     *
     * @return $this
     */
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

    /**
     * Define an index on a column with an optional name and uniqueness.
     *
     * @param string $columnName The name of the indexed column.
     * @param string $indexName The name of the indexed column.
     * @param boolean $unique (Optional) Set to true to create a unique index, false for non-unique (default: false).
     * @return $this
     */
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

    /**
     * Define a foreign key relationship on the specified column.
     *
     * @param string $columnName The name of the column with the foreign key.
     * @param string $referencedTable The name of the referenced table.
     * @param string $referencedColumn (Optional) The name of the referenced column (default: 'id').
     * @param string $onDelete (Optional) The ON DELETE action (default: 'CASCADE').
     * @param string $onUpdate (Optional) The ON UPDATE action (default: 'CASCADE').
     * @return $this
     */
    public function foreignKey($columnName, $referencedTable, $referencedColumn = 'id', $onDelete = 'CASCADE', $onUpdate = 'CASCADE')
    {
        $constraintName = "{$this->table}_{$columnName}_fk";

        // Store foreign key definitions
        $this->foreignKeys[] = [
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

    private function addForeignKeys()
    {
        foreach ($this->foreignKeys as $key) {
            // Create foreign key constraints
            $sql = "ALTER TABLE {$this->table} ADD CONSTRAINT {$key['name']} FOREIGN KEY ({$key['column']}) " .
                "REFERENCES {$key['references']} ({$key['refColumn']}) ON DELETE {$key['onDelete']} ON UPDATE {$key['onUpdate']}";
            $this->executeSql($sql);
        }
    }

    /**
     * Execute an SQL query and return the statement.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params (Optional) An array of parameters for prepared statements.
     * @return \PDOStatement|bool The PDO statement or false if there's an error.
     */
    private function executeSql($sql, $params = [])
    {
        // Replace 'yourQueryExecutionMethod' with the actual method you use in your application
        $statement = $this->query($sql, $params);

        if ($statement === false) {
            $this->error = $this->getError(); // Adjust this to get the last error from your database
        }

        return $statement;
    }

    /**
     * Drop foreign key constraints from the table.
     *
     * @param string $tableName The name of the table from which to drop foreign key constraints.
     */
    public function dropForeignKey($tableName)
    {
        $sql = "SELECT
                table_name,
                constraint_name
            FROM
                information_schema.key_column_usage
            WHERE
                referenced_table_name = :table_name";

        $params = [':table_name' => $tableName];

        $foreignKeys = $this->query($sql, $params);

        if ($foreignKeys === false) {
            $this->consoleLog("Error fetching foreign key constraints: {$this->error}", true, true, 'error');
            return;
        }

        foreach ($foreignKeys as $foreignKey) {
            $constraintName = $foreignKey['constraint_name'];
            $sql = "ALTER TABLE {$tableName} DROP FOREIGN KEY {$constraintName}";
            if ($this->query($sql)) {
                $this->consoleLog("Foreign key constraint '{$constraintName}' dropped from table '{$tableName}'");
            } else {
                $this->consoleLog("Error dropping foreign key constraint: {$this->error}", true, true, 'error');
            }
        }
    }

    /**
     * Drop the specified table if it exists.
     *
     * @param string $tableName The name of the table to drop.
     */
    public function dropTable($tableName)
    {
        $sql = "DROP TABLE IF EXISTS {$tableName}";
        if ($this->query($sql)) {
            $this->consoleLog("Table '{$tableName}' dropped successfully");
        } else {
            $this->consoleLog("Error dropping table: {$this->error}", true, true, 'error');
        }
    }

    /**
     * Rename a table from the old name to the new name.
     *
     * @param string $oldTableName The current name of the table.
     * @param string $newTableName The desired new name for the table.
     */
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

    /**
     * Add a new column to the table.
     *
     * @param string $columnName The name of the new column.
     * @param string $type The data type of the new column.
     * @param string $after (Optional) The name of a column to place the new column after (default: null).
     */
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

    /**
     * Modify the data type of an existing column.
     *
     * @param string $columnName The name of the column to modify.
     * @param string $newType The new data type for the column.
     */
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


    /**
     * Drop an existing column from the table.
     *
     * @param string $columnName The name of the column to drop.
     */
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

    /**
     * Create a database view with the specified name and SQL query.
     *
     * @param string $viewName The name of the view.
     * @param string $sqlQuery The SQL query that defines the view.
     */
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

    /**
     * Generate and execute SQL code to create the table with defined columns and indexes.
     *
     * @param boolean $addForeignKeys Create the table with foreign key constraints
     * @return void
     */
    public function build($addForeignKeys = false)
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

            // Add foreign keys if specified
            if ($addForeignKeys) {
                $this->addForeignKeys();
            }
        } else {
            $this->consoleLog("Error creating table: {$this->error}", true, true, 'error');
        }
    }

    /**
     * Log a message to the console with optional timestamp and color-coding.
     *
     * @param string $message The message to log.
     * @param bool $die (Optional) Set to true to terminate the script after logging (default: false).
     * @param bool $timestamp (Optional) Set to true to include a timestamp in the log (default: true).
     * @param string $level (Optional) The message level: 'info', 'warning', or 'error' (default: 'info').
     */
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
