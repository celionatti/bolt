<?php

declare(strict_types=1);

/**
 * =======================================
 * Bolt - BoltMigration Class ============
 * =======================================
 */

namespace Bolt\Bolt\Migration;

use Bolt\Bolt\Database\Database;

class Migration extends Database
{
    public $columns = [];
    public $data = [];
    public $currentTable;

    public function createTable(string $table): self
    {
        if (!empty($this->columns)) {
            $query = "CREATE TABLE IF NOT EXISTS $table (";
            $query .= implode(",", $this->columns) . ',';
            $query = trim($query, ",");
            $query .= ") ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4";

            $this->query($query);

            $this->columns = [];
            $this->consoleLog("Table $table created successfully!");
        } else {
            $this->consoleLog("Column data not found! Could not create table: $table", true, true, 'error');
        }

        $this->currentTable = $table;
        return $this;
    }

    public function addColumn(string $column): self
    {
        $this->columns[] = $column;
        return $this;
    }

    public function addData(string $data): self
    {
        $this->data[] = $data;
        return $this;
    }

    public function addBigIntColumn(string $columnName): self
    {
        return $this->addColumn("$columnName BIGINT");
    }

    public function addIntColumn(string $columnName): self
    {
        return $this->addColumn("$columnName INT");
    }

    public function addVarcharColumn(string $columnName, int $length): self
    {
        return $this->addColumn("$columnName VARCHAR($length)");
    }

    // Add other column type methods

    public function addPrimaryKey(string $columnName): self
    {
        $query = "ALTER TABLE $this->currentTable ADD PRIMARY KEY ($columnName)";
        $this->query($query);
        return $this;
    }

    public function addUniqueIndex(string $columnName): self
    {
        $query = "CREATE UNIQUE INDEX idx_unique_$columnName ON $this->currentTable ($columnName)";
        $this->query($query);
        return $this;
    }

    public function nullable(): self
    {
        // Set the nullable attribute for the last added column
        if (!empty($this->columns)) {
            $lastColumnIndex = count($this->columns) - 1;
            $this->columns[$lastColumnIndex] .= ' NULL';
        }
        return $this;
    }

    public function autoIncrement(): self
    {
        // Set the auto-increment attribute for the last added column
        if (!empty($this->columns)) {
            $lastColumnIndex = count($this->columns) - 1;
            $this->columns[$lastColumnIndex] .= ' AUTO_INCREMENT';
        }
        return $this;
    }

    public function addTimestamp(string $columnName = 'created_at', bool $useCurrent = false): self
    {
        $timestampColumn = "$columnName TIMESTAMP";

        if ($useCurrent) {
            $timestampColumn .= " DEFAULT CURRENT_TIMESTAMP";
        }

        $this->addColumn($timestampColumn);
        return $this;
    }

    public function updateTimestamp(string $columnName = 'updated_at', bool $useCurrent = false): self
    {
        $timestampColumn = "$columnName TIMESTAMP";

        if ($useCurrent) {
            $timestampColumn .= " ON UPDATE CURRENT_TIMESTAMP";
        }

        $this->addColumn($timestampColumn);
        return $this;
    }

    public function dropTable(string $table): self
    {
        $query = "DROP TABLE IF EXISTS $table";
        $this->query($query);

        $this->consoleLog("Table $table deleted successfully!");
        return $this;
    }

    public function renameTable(string $oldTableName, string $newTableName): self
    {
        $query = "RENAME TABLE $oldTableName TO $newTableName";
        $this->query($query);

        $this->consoleLog("Table $oldTableName renamed to $newTableName successfully!");
        return $this;
    }

    public function addForeignKey(string $columnName, string $foreignTable, string $foreignColumn): self
    {
        $query = "ALTER TABLE $this->currentTable ADD CONSTRAINT fk_$columnName FOREIGN KEY ($columnName) REFERENCES $foreignTable($foreignColumn)";
        $this->query($query);

        $this->consoleLog("Foreign key added successfully on column $columnName in table $this->currentTable!");
        return $this;
    }

    public function dropColumn(string $columnName): self
    {
        $query = "ALTER TABLE $this->currentTable DROP COLUMN $columnName";
        $this->query($query);

        $this->consoleLog("Column $columnName dropped successfully from table $this->currentTable!");
        return $this;
    }

    public function dropPrimaryKey(): self
    {
        $query = "ALTER TABLE $this->currentTable DROP PRIMARY KEY";
        $this->query($query);

        $this->consoleLog("Primary key dropped successfully from table $this->currentTable!");
        return $this;
    }

    public function insert(): self
    {
        if (!empty($this->data) && is_array($this->data)) {
            $keys = array_keys($this->data[0]);
            $columns_string = implode(",", $keys);
            $values_string = ':' . implode(",:", $keys);

            foreach ($this->data as $row) {
                $query = "INSERT INTO $this->currentTable ($columns_string) VALUES ($values_string)";
                $this->query($query, $row);
            }

            $this->consoleLog("Data inserted successfully in table: $this->currentTable");
        } else {
            $this->consoleLog("Row data not found! No data inserted in table: $this->currentTable", true, true, 'error');
        }

        return $this;
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
        }

        $output .= "\033[0m"; // Reset color

        echo $output;

        if ($die) {
            exit(1);
        }
    }
}
