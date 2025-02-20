<?php

declare(strict_types=1);

/**
 * ========================================
 * Bolt - Query Builder ===================
 * ========================================
 */

namespace celionatti\Bolt\BoltQueryBuilder;

use PDO;
use PDOException;
use InvalidArgumentException;
use Exception;


class QueryBuilder
{
    protected $pdo;
    protected $union = [];
    protected $select = [];
    protected $from;
    protected $join = [];
    protected $where = [];
    protected $orderBy = [];
    protected $groupBy = [];
    protected $having = [];
    protected $limit;
    protected $offset;
    protected $bindings = [];
    protected $type = 'select';
    protected $insert = [];
    protected $update = [];
    protected $delete = false;
    protected $upsertUpdateColumns = [];
    protected $parameterPrefix = '';

    public function __construct(PDO $pdo, string $parameterPrefix = '')
    {
        $this->pdo = $pdo;
        $this->parameterPrefix = $parameterPrefix;
    }

    public function subQuery(callable $callback, string $alias = null): self
    {
        $subQuery = new self($this->pdo, 'sub_' . uniqid() . '_');
        $callback($subQuery);
        $sql = '(' . $subQuery->toSql() . ')';
        if ($alias) {
            $sql .= ' AS ' . $this->quoteIdentifier($alias);
        }
        $this->mergeBindings($subQuery->getBindings());
        return $sql;
    }

    public function union(QueryBuilder $queryBuilder): self
    {
        $this->union[] = $queryBuilder;
        return $this;
    }

    public function select($columns = ['*']): self
    {
        $this->type = 'select';
        $this->select = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function distinct(): self
    {
        $this->select = array_map(function ($column) {
            return "DISTINCT {$column}";
        }, $this->select);
        return $this;
    }

    public function table(string $table): self
    {
        return $this->from($table);
    }

    public function from(string $table): self
    {
        $this->from = $this->quoteIdentifier($table);
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $table = $this->quoteIdentifier($table);
        $first = $this->quoteIdentifier($first);
        $second = $this->quoteIdentifier($second);
        $this->join[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function where($column, $operator, $value = null, string $boolean = 'AND'): self
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        $this->addWhere($column, $operator, $value, $boolean);
        return $this;
    }

    public function orWhere($column, $operator, $value = null): self
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    public function whereIn(string $column, array $values, string $boolean = 'AND', bool $not = false): self
    {
        $operator = $not ? 'NOT IN' : 'IN';
        $params = [];
        foreach ($values as $value) {
            $param = $this->generateParamName($column);
            $params[] = ":{$param}";
            $this->bindings[$param] = $value;
        }
        $this->where[] = [
            'type' => 'basic',
            'column' => $this->quoteIdentifier($column),
            'operator' => $operator,
            'value' => '(' . implode(', ', $params) . ')',
            'boolean' => $boolean
        ];
        return $this;
    }

    public function whereNested(callable $callback, string $boolean = 'AND'): self
    {
        $subQuery = new self($this->pdo, 'nested_' . count($this->where) . '_');
        $callback($subQuery);
        $this->where[] = [
            'type' => 'nested',
            'query' => $subQuery,
            'boolean' => $boolean
        ];
        $this->mergeBindings($subQuery->getBindings());
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new InvalidArgumentException("Invalid order direction: {$direction}");
        }
        $this->orderBy[] = $this->quoteIdentifier($column) . " {$direction}";
        return $this;
    }

    public function groupBy(string $column): self
    {
        $this->groupBy[] = $this->quoteIdentifier($column);
        return $this;
    }

    public function having(string $column, string $operator, $value): self
    {
        $param = $this->generateParamName($column);
        $this->having[] = [
            'column' => $this->quoteIdentifier($column),
            'operator' => $operator,
            'value' => ":{$param}",
            'boolean' => 'AND'
        ];
        $this->bindings[$param] = $value;
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function count(string $column = '*'): self
    {
        $this->select(["COUNT({$column}) AS count"]);
        return $this;
    }

    public function insert(string $table, array $data): self
    {
        $this->type = 'insert';
        $this->from = $this->quoteIdentifier($table);
        $this->insert = $data;
        return $this;
    }

    public function onDuplicateKeyUpdate(array $columns): self
    {
        $this->upsertUpdateColumns = $columns;
        return $this;
    }

    public function update(string $table, array $data): self
    {
        $this->type = 'update';
        $this->from = $this->quoteIdentifier($table);
        $this->update = $data;
        return $this;
    }

    public function delete(string $table): self
    {
        $this->type = 'delete';
        $this->from = $this->quoteIdentifier($table);
        $this->delete = true;
        return $this;
    }

    public function toSql(): string
    {
        switch ($this->type) {
            case 'insert':
                return $this->buildInsert();
            case 'update':
                return $this->buildUpdate();
            case 'delete':
                return $this->buildDelete();
            default:
                return $this->buildSelect();
        }
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }

    public function execute(string $className = null): array
    {
        try {
            $sql = $this->toSql();
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($this->bindings);

            if ($this->type === 'select') {
                return $className ? $stmt->fetchAll(PDO::FETCH_CLASS, $className) : $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return ['affected_rows' => $stmt->rowCount()];
        } catch (PDOException $e) {
            throw new Exception(
                "Query failed: {$e->getMessage()}\nSQL: {$this->getDebugSql()}\nBindings: " . print_r($this->bindings, true)
            );
        } finally {
            $this->reset();
        }
    }

    public function paginate(int $perPage = 15, int $currentPage = 1): array
    {
        $total = $this->cloneWithoutPaging()->count()->execute()[0]['count'] ?? 0;
        $results = $this->limit($perPage)->offset(($currentPage - 1) * $perPage)->execute();

        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => max(ceil($total / $perPage), 1),
        ];
    }

    protected function buildSelect(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->select) . ' FROM ' . $this->from;

        if (!empty($this->join)) {
            $sql .= ' ' . implode(' ', $this->join);
        }

        $whereClause = $this->buildWheres();
        if ($whereClause) {
            $sql .= ' WHERE ' . $whereClause;
        }

        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }

        if (!empty($this->having)) {
            $sql .= ' HAVING ' . $this->buildHavings();
        }

        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        if (!empty($this->union)) {
            $unionSql = [];
            foreach ($this->union as $union) {
                $unionSql[] = 'UNION (' . $union->toSql() . ')';
                $this->mergeBindings($union->getBindings());
            }
            $sql .= ' ' . implode(' ', $unionSql);
        }

        return $sql;
    }

    protected function buildInsert(): string
    {
        $columns = array_map([$this, 'quoteIdentifier'], array_keys($this->insert));
        $params = array_map(fn($col) => ":{$col}", array_keys($this->insert));

        $sql = 'INSERT INTO ' . $this->from . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $params) . ')';

        if (!empty($this->upsertUpdateColumns)) {
            $updates = array_map(
                fn($col) => $this->quoteIdentifier($col) . ' = VALUES(' . $this->quoteIdentifier($col) . ')',
                $this->upsertUpdateColumns
            );
            $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updates);
        }

        $this->bindings = array_merge($this->bindings, $this->insert);
        return $sql;
    }

    protected function buildUpdate(): string
    {
        $set = [];
        foreach ($this->update as $column => $value) {
            $param = $this->generateParamName($column);
            $set[] = $this->quoteIdentifier($column) . ' = :' . $param;
            $this->bindings[$param] = $value;
        }

        $sql = 'UPDATE ' . $this->from . ' SET ' . implode(', ', $set);

        $whereClause = $this->buildWheres();
        if ($whereClause) {
            $sql .= ' WHERE ' . $whereClause;
        }

        return $sql;
    }

    protected function buildDelete(): string
    {
        $sql = 'DELETE FROM ' . $this->from;

        $whereClause = $this->buildWheres();
        if ($whereClause) {
            $sql .= ' WHERE ' . $whereClause;
        }

        return $sql;
    }

    protected function buildWheres(): string
    {
        $clauses = [];
        foreach ($this->where as $where) {
            if ($where['type'] === 'nested') {
                $clause = '(' . $where['query']->buildWheres() . ')';
                $this->mergeBindings($where['query']->getBindings());
            } else {
                $clause = "{$where['column']} {$where['operator']} {$where['value']}";
            }
            $clauses[] = ($clauses ? $where['boolean'] . ' ' : '') . $clause;
        }
        return implode(' ', $clauses);
    }

    protected function buildHavings(): string
    {
        return implode(' AND ', array_map(
            fn($having) => "{$having['column']} {$having['operator']} {$having['value']}",
            $this->having
        ));
    }

    protected function generateParamName(string $column): string
    {
        $param = $this->parameterPrefix . str_replace(['.', ' '], '_', $column) . '_' . count($this->bindings);
        return preg_replace('/[^a-zA-Z0-9_]/', '', $param);
    }

    protected function quoteIdentifier(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    protected function mergeBindings(array $bindings): void
    {
        $this->bindings = array_merge($this->bindings, $bindings);
    }

    protected function cloneWithoutPaging(): self
    {
        $clone = clone $this;
        $clone->limit = null;
        $clone->offset = null;
        return $clone;
    }

    protected function getDebugSql(): string
    {
        $sql = $this->toSql();
        foreach ($this->bindings as $key => $value) {
            $sql = str_replace(":{$key}", $this->pdo->quote($value), $sql);
        }
        return $sql;
    }

    protected function reset(): void
    {
        $properties = [
            'union', 'select', 'from', 'join', 'where', 'orderBy', 'groupBy',
            'having', 'limit', 'offset', 'bindings', 'insert', 'update',
            'delete', 'upsertUpdateColumns'
        ];
        foreach ($properties as $property) {
            $this->$property = is_array($this->$property) ? [] : null;
        }
        $this->type = 'select';
    }
}
