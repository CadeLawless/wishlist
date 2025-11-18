<?php

namespace App\Core;

use App\Core\Database;
use InvalidArgumentException;

class QueryBuilder
{
    private string $table;

    private array $select = ['*'];
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
       SELECT
    --------------------------- */
    public function select(array $columns): self
    {
        $this->select = $columns;
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
    private function buildSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->select)
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
        $sql = $this->buildSql();

        $stmt = $this->prepareAndExecute($sql);
        $result = $stmt->get_result();

        $this->reset();

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    private function reset(): void
    {
        $this->select = ['*'];
        $this->where = [];
        $this->whereGlue = [];
        $this->limit = null;
        $this->offset = null;
        $this->params = [];
        $this->orderBy = [];
        $this->orderByCase = [];
    }
}
