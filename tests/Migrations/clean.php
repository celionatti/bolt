<?php

namespace Bolt\Bolt\Migration;

use Bolt\Bolt\Database\Database;

class BoltMigration_clean extends Database
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

    // Add other column type methods

    public function nullable(): self
    {
        return $this->modifyLastColumn(' NULL');
    }

    public function autoIncrement(): self
    {
        return $this->modifyLastColumn(' AUTO_INCREMENT');
    }

    // Rest of your methods

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

    // Rest of your methods

    // The rest of your class remains the same
}
