<?php

declare(strict_types=1);

/**
 * =======================================
 * Bolt - Schema Class ===================
 * =======================================
 */

namespace celionatti\Bolt\Illuminate\Schema;

class Blueprint
{
    protected $table;
    protected $columns = [];
    protected $indexes = [];
    protected $uniqueIndexes = [];
    protected $foreignKeys = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(): self
    {
        $this->columns[] = "`id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    public function string(string $name, int $length = 255): self
    {
        $this->columns[] = "`$name` VARCHAR($length)";
        return $this;
    }

    public function timestamp(string $name): self
    {
        $this->columns[] = "`$name` TIMESTAMP";
        return $this;
    }

    public function unique(string $name): self
    {
        $this->uniqueIndexes[] = "`$name`";
        return $this;
    }

    public function nullable(): self
    {
        $lastColumn = array_pop($this->columns);
        $this->columns[] = "$lastColumn NULL";
        return $this;
    }

    public function foreignId(string $name, string $references = '', string $on = ''): self
    {
        $this->columns[] = "`$name` INT UNSIGNED";
        if ($references && $on) {
            $this->foreignKeys[] = "FOREIGN KEY (`$name`) REFERENCES `$on`(`$references`)";
        }
        return $this;
    }

    public function text(string $name): self
    {
        $this->columns[] = "`$name` TEXT";
        return $this;
    }

    public function longText(string $name): self
    {
        $this->columns[] = "`$name` LONGTEXT";
        return $this;
    }

    public function integer(string $name): self
    {
        $this->columns[] = "`$name` INT";
        return $this;
    }

    public function bigInteger(string $name): self
    {
        $this->columns[] = "`$name` BIGINT";
        return $this;
    }

    public function boolean(string $name): self
    {
        $this->columns[] = "`$name` TINYINT(1)";
        return $this;
    }

    public function decimal(string $name, int $precision = 8, int $scale = 2): self
    {
        $this->columns[] = "`$name` DECIMAL($precision, $scale)";
        return $this;
    }

    public function date(string $name): self
    {
        $this->columns[] = "`$name` DATE";
        return $this;
    }

    public function dateTime(string $name): self
    {
        $this->columns[] = "`$name` DATETIME";
        return $this;
    }

    public function time(string $name): self
    {
        $this->columns[] = "`$name` TIME";
        return $this;
    }

    public function tinyInteger(string $name): self
    {
        $this->columns[] = "`$name` TINYINT";
        return $this;
    }

    public function smallInteger(string $name): self
    {
        $this->columns[] = "`$name` SMALLINT";
        return $this;
    }

    public function mediumInteger(string $name): self
    {
        $this->columns[] = "`$name` MEDIUMINT";
        return $this;
    }

    public function index(string $name): self
    {
        $this->indexes[] = "`$name`";
        return $this;
    }

    public function rememberToken(): self
    {
        $this->columns[] = "`remember_token` VARCHAR(100) NULL";
        return $this;
    }

    public function timestamps(): self
    {
        $this->columns[] = "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    public function softDeletes(): self
    {
        $this->columns[] = "`deleted_at` TIMESTAMP NULL";
        return $this;
    }

    public function dropColumn(string $name): self
    {
        $this->columns[] = "DROP COLUMN `$name`";
        return $this;
    }

    public function modifyColumn(string $name, string $type, int $length = null): self
    {
        $column = "`$name` $type";
        if ($length !== null) {
            $column .= "($length)";
        }
        $this->columns[] = "MODIFY $column";
        return $this;
    }

    public function toSql(): string
    {
        $columns = implode(', ', $this->columns);
        $indexes = $this->buildIndexes();
        $uniqueIndexes = $this->buildUniqueIndexes();
        $foreignKeys = $this->buildForeignKeys();

        $sqlParts = array_filter([$columns, $indexes, $uniqueIndexes, $foreignKeys]);
        $sql = implode(', ', $sqlParts);

        return "CREATE TABLE `{$this->table}` ($sql);";
    }

    protected function buildIndexes(): string
    {
        if (empty($this->indexes)) {
            return '';
        }

        $indexes = array_map(fn($index) => "INDEX ($index)", $this->indexes);
        return implode(', ', $indexes);
    }

    protected function buildUniqueIndexes(): string
    {
        if (empty($this->uniqueIndexes)) {
            return '';
        }

        $uniqueIndexes = array_map(fn($uniqueIndex) => "UNIQUE INDEX ($uniqueIndex)", $this->uniqueIndexes);
        return implode(', ', $uniqueIndexes);
    }

    protected function buildForeignKeys(): string
    {
        if (empty($this->foreignKeys)) {
            return '';
        }

        return implode(', ', $this->foreignKeys);
    }
}
