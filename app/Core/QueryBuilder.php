<?php

namespace App\Core;

use App\Core\Database;
use InvalidArgumentException;

class QueryBuilder
{
    private string $table;

    private array $columns = ['*'];
    private array $where = [];
    private array $whereGlue = [];

    private array $orderBy = [];
    private array $orderByCase = [];

    private ?int $limit = null;
    private ?int $offset = null;

    private array $params = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    /* ---------------------------
       Utility
    --------------------------- */
    private function addParam(mixed $value): void
    {
        $this->params[] = $value;
    }

    /* ---------------------------
       COLUMNS
    --------------------------- */
    public function columns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    /* ---------------------------
       VALUES
    --------------------------- */
    public function params(array $values): self
    {
        $this->params = $values;
        return $this;
    }

    /* ---------------------------
       WHERE / AND / OR
    --------------------------- */
    public function where(string $column, mixed $value, string $operator = '='): self
    {
        $this->where[] = "$column $operator ?";
        $this->whereGlue[] = 'AND';
        $this->addParam($value);
        return $this;
    }

    public function andWhere(string $column, mixed $value, string $operator = '='): self
    {
        return $this->where($column, $value, $operator);
    }

    public function orWhere(string $column, mixed $value, string $operator = '='): self
    {
        $this->where[] = "$column $operator ?";
        $this->whereGlue[] = 'OR';
        $this->addParam($value);
        return $this;
    }

    /* ---------------------------
       ORDER BY
    --------------------------- */
    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new InvalidArgumentException("Order direction must be ASC or DESC");
        }

        $this->orderBy[] = "$column $direction";
        return $this;
    }

    /* ---------------------------
       UNIVERSAL ORDER BY CASE
    --------------------------- */
    /**
     * Universal CASE:
     * [
     *   ['when' => 'POSITION(? IN username) = 1', 'then' => 1, 'params' => [$search]],
     *   ['when' => 'username = ?', 'then' => 2, 'params' => [$username]],
     * ]
     */
    public function orderByCase(array $cases, string $direction = 'ASC', ?int $elsePriority = null): self
    {
        $direction = strtoupper($direction);

        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new InvalidArgumentException("Order direction must be ASC or DESC");
        }

        $sql = "CASE";

        foreach ($cases as $rule) {
            if (!isset($rule['when'], $rule['then'])) {
                throw new InvalidArgumentException("CASE rule must contain 'when' and 'then'");
            }

            $sql .= " WHEN {$rule['when']} THEN {$rule['then']}";

            if (!empty($rule['params'])) {
                foreach ($rule['params'] as $p) {
                    $this->addParam($p);
                }
            }
        }

        if ($elsePriority !== null) {
            $sql .= " ELSE $elsePriority";
        }

        $sql .= " END $direction";

        $this->orderByCase[] = $sql;

        return $this;
    }

    /* ---------------------------
       LIMIT / OFFSET
    --------------------------- */
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

    /* ---------------------------
       SQL Building
    --------------------------- */
    private function buildSql(string $operation = 'select'): string
    {
        if($operation !== 'select' && $operation !== 'insert' && $operation !== 'update' && $operation !== 'delete') {
            throw new InvalidArgumentException("Unsupported operation: $operation");
        }

        $sql = '';

        if($operation === 'insert') {
            // Build INSERT SQL
            $placeholders = array_map(fn($v) => '?', $this->columns);

            $sql = "INSERT INTO {$this->table} (" . implode(', ', $this->columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            return $sql;
        } else if ($operation === 'update') {
            // Build UPDATE SQL
            $setClauses = array_map(fn($col) => "$col = ?", $this->columns);
            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses);

            if (!empty($this->where)) {
                $sql .= " WHERE ";

                $clauses = [];
                foreach ($this->where as $i => $clause) {
                    if ($i === 0) {
                        $clauses[] = $clause;
                    } else {
                        $clauses[] = $this->whereGlue[$i] . " " . $clause;
                    }
                }

                $sql .= implode(' ', $clauses);
            }
        } else if ($operation === 'delete') {
            // Build DELETE SQL
            $sql = "DELETE FROM {$this->table}";

            if (!empty($this->where)) {
                $sql .= " WHERE ";

                $clauses = [];
                foreach ($this->where as $i => $clause) {
                    if ($i === 0) {
                        $clauses[] = $clause;
                    } else {
                        $clauses[] = $this->whereGlue[$i] . " " . $clause;
                    }
                }

                $sql .= implode(' ', $clauses);
            }
        } else if($operation === 'select') {
            // Build SELECT SQL
            $sql = "SELECT " . implode(', ', $this->columns)
                . " FROM {$this->table}";

            if (!empty($this->where)) {
                $sql .= " WHERE ";

                $clauses = [];
                foreach ($this->where as $i => $clause) {
                    if ($i === 0) {
                        $clauses[] = $clause;
                    } else {
                        $clauses[] = $this->whereGlue[$i] . " " . $clause;
                    }
                }

                $sql .= implode(' ', $clauses);
            }

            $orders = array_merge($this->orderBy, $this->orderByCase);
            if (!empty($orders)) {
                $sql .= " ORDER BY " . implode(', ', $orders);
            }

            if ($this->limit !== null) {
                $sql .= " LIMIT {$this->limit}";
            }

            if ($this->offset !== null) {
                $sql .= " OFFSET {$this->offset}";
            }
        }

        return $sql;
    }

    private function prepareAndExecute(string $sql)
    {
        $conn = Database::connect();
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new \Exception("SQL prepare failed: " . $conn->error);
        }

        if (!empty($this->params)) {
            $stmt->execute($this->params);
        } else {
            $stmt->execute();
        }

        return $stmt;
    }

    /* ---------------------------
       Fetch
    --------------------------- */
    public function first(): ?array
    {
        $this->limit = 1;
        $sql = $this->buildSql();

        $stmt = $this->prepareAndExecute($sql);
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        $this->reset();

        return $row ?: null;
    }

    public function getAll(): array
    {
        $sql = $this->buildSql('select');

        $stmt = $this->prepareAndExecute($sql);
        $result = $stmt->get_result();

        $this->reset();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function insert(): bool
    {
        $sql = $this->buildSql('insert');

        $stmt = $this->prepareAndExecute($sql);

        $this->reset();

        return $stmt !== false;
    }

    public function update(): bool
    {
        $sql = $this->buildSql('update');

        $stmt = $this->prepareAndExecute($sql);

        $this->reset();

        return $stmt !== false;
    }

    public function delete(): bool
    {
        $sql = $this->buildSql('delete');

        $stmt = $this->prepareAndExecute($sql);

        $this->reset();

        return $stmt !== false;
    }

    private function reset(): void
    {
        $this->columns = ['*'];
        $this->where = [];
        $this->whereGlue = [];
        $this->limit = null;
        $this->offset = null;
        $this->params = [];
        $this->orderBy = [];
        $this->orderByCase = [];
    }
}
