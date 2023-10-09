<?php

namespace Bolt\Bolt\Migration;

use Bolt\Bolt\Database\Database;

class BoltMigration_com extends Database
{
    private $columns = [];
    private $data = [];
    private $currentTable;

    public function createTable(string $table): self
    {
        if (!empty($this->columns)) {
            $query = "CREATE TABLE IF NOT EXISTS $table (";
            $query .= implode(",", $this->columns) . ',';
            $query = trim($query, ",");
            $query .= ") ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4";

            $this->query($query);

            $this->clearColumns();
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

    public function int(string $columnName)
    {
        return $this->addColumn("$columnName INT");
    }

    public function varchar(string $columnName, int $length)
    {
        return $this->addColumn("$columnName VARCHAR($length)");
    }

    public function bigint(string $columnName)
    {
        return $this->addColumn("$columnName BIGINT");
    }

    public function enum(string $columnName, array $enumValues)
    {
        $enumValuesStr = implode(',', array_map(function ($value) {
            return "'" . addslashes($value) . "'";
        }, $enumValues));

        return $this->addColumn("$columnName ENUM($enumValuesStr)");
    }

    public function tinyint(string $columnName)
    {
        return $this->addColumn("$columnName TINYINT");
    }

    public function nullable(): self
    {
        return $this->modifyLastColumn(' NULL');
    }

    public function autoIncrement(): self
    {
        return $this->modifyLastColumn(' AUTO_INCREMENT');
    }

    public function addTimestamp(string $columnName = 'created_at', bool $useCurrent = false): self
    {
        $timestampColumn = "$columnName TIMESTAMP";

        if ($useCurrent) {
            $timestampColumn .= " DEFAULT CURRENT_TIMESTAMP";
        }

        return $this->addColumn($timestampColumn);
    }

    public function updateTimestamp(string $columnName = 'updated_at', bool $useCurrent = false): self
    {
        $timestampColumn = "$columnName TIMESTAMP";

        if ($useCurrent) {
            $timestampColumn .= " ON UPDATE CURRENT_TIMESTAMP";
        }

        return $this->addColumn($timestampColumn);
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

            $this->data = [];
            $this->consoleLog("Data inserted successfully in table: $this->currentTable");
        } else {
            $this->consoleLog("Row data not found! No data inserted in table: $this->currentTable", true, true, 'error');
        }

        return $this;
    }

    private function modifyLastColumn(string $modification): self
    {
        if (!empty($this->columns)) {
            $lastColumnIndex = count($this->columns) - 1;
            $this->columns[$lastColumnIndex] .= $modification;
        }
        return $this;
    }

    private function clearColumns(): void
    {
        $this->columns = [];
    }

    private function consoleLog(string $message, bool $die = false, bool $timestamp = true, string $level = 'info'): void
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
