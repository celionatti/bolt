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
    protected $relationships = [];

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

    public function char(string $name, int $length = 255): self
    {
        $this->columns[] = "`$name` CHAR($length)";
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

    public function enum(string $name, array $allowedValues): self
    {
        $allowedValuesString = implode("','", $allowedValues);
        $this->columns[] = "`$name` ENUM('$allowedValuesString')";
        return $this;
    }

    // public function nullable(): self
    // {
    //     $lastColumn = array_pop($this->columns);
    //     $this->columns[] = "$lastColumn NULL";
    //     return $this;
    // }
    public function nullable(string $columnName = null): self
    {
        if ($columnName) {
            $index = array_search("`$columnName`", $this->columns);
            if ($index !== false) {
                $this->columns[$index] .= " NULL";
            }
        } else {
            $lastColumn = array_pop($this->columns);
            $this->columns[] = "$lastColumn NULL";
        }
        return $this;
    }

    public function default($value): self
    {
        $lastColumn = array_pop($this->columns);
        $defaultValue = is_string($value) ? "'$value'" : $value;
        $this->columns[] = "$lastColumn DEFAULT $defaultValue";
        return $this;
    }

    public function foreignId(string $name): self
    {
        $this->columns[] = "`$name` BIGINT UNSIGNED";
        $this->foreignKeys[] = ['column' => $name];
        return $this;
    }

    public function references(string $references): self
    {
        $lastKey = array_key_last($this->foreignKeys);
        $this->foreignKeys[$lastKey]['references'] = $references;
        return $this;
    }

    public function on(string $on): self
    {
        $lastKey = array_key_last($this->foreignKeys);
        $this->foreignKeys[$lastKey]['on'] = $on;
        return $this;
    }

    public function onDelete(string $action): self
    {
        $lastKey = array_key_last($this->foreignKeys);
        $this->foreignKeys[$lastKey]['onDelete'] = $action;
        return $this;
    }

    public function onUpdate(string $action): self
    {
        $lastKey = array_key_last($this->foreignKeys);
        $this->foreignKeys[$lastKey]['onUpdate'] = $action;
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

    public function dropTable(): string
    {
        return "DROP TABLE `{$this->table}`;";
    }

    public function renameColumn(string $oldName, string $newName): self
    {
        $this->columns[] = "RENAME COLUMN `$oldName` TO `$newName`";
        return $this;
    }

    public function renameTable(string $newName): string
    {
        return "RENAME TABLE `{$this->table}` TO `$newName`;";
    }

    public function dropForeignKey(string $keyName): self
    {
        $this->foreignKeys[] = "DROP FOREIGN KEY `$keyName`";
        return $this;
    }

    public function dropIndex(string $indexName): self
    {
        $this->indexes[] = "DROP INDEX `$indexName`";
        return $this;
    }

    public function dropUnique(string $uniqueName): self
    {
        $this->uniqueIndexes[] = "DROP INDEX `$uniqueName`";
        return $this;
    }

    public function json(string $name): self
    {
        $this->columns[] = "`$name` JSON";
        return $this;
    }

    public function geometry(string $name): self
    {
        $this->columns[] = "`$name` GEOMETRY";
        return $this;
    }

    public function point(string $name): self
    {
        $this->columns[] = "`$name` POINT";
        return $this;
    }

    public function multiPolygon(string $name): self
    {
        $this->columns[] = "`$name` MULTIPOLYGON";
        return $this;
    }

    public function jsonb(string $name): self
    {
        $this->columns[] = "`$name` JSONB";
        return $this;
    }

    public function set(string $name, array $allowedValues): self
    {
        $allowedValuesString = implode("','", $allowedValues);
        $this->columns[] = "`$name` SET('$allowedValuesString')";
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

    public function addRelationship(string $relation, string $relatedClass, string $foreignKey, string $localKey = 'id'): self
    {
        $this->relationships[] = [
            'relation' => $relation,
            'relatedClass' => $relatedClass,
            'foreignKey' => $foreignKey,
            'localKey' => $localKey,
        ];
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

        $foreignKeys = [];
        foreach ($this->foreignKeys as $foreignKey) {
            $foreignKeySql = "FOREIGN KEY (`{$foreignKey['column']}`) REFERENCES `{$foreignKey['on']}`(`{$foreignKey['references']}`)";
            if (isset($foreignKey['onDelete'])) {
                $foreignKeySql .= " ON DELETE {$foreignKey['onDelete']}";
            }
            if (isset($foreignKey['onUpdate'])) {
                $foreignKeySql .= " ON UPDATE {$foreignKey['onUpdate']}";
            }
            $foreignKeys[] = $foreignKeySql;
        }
        return implode(', ', $foreignKeys);
    }

    public function getRelationships(): array
    {
        return $this->relationships;
    }
}
