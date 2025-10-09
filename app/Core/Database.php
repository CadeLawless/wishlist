<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;
    private string $table = '';
    private array $wheres = [];
    private array $joins = [];
    private string $orderBy = '';
    private int $limit = 0;
    private int $offset = 0;

    private function __construct()
    {
        $config = Config::get('database.connections.mysql');
        
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        
        try {
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function table(string $table): Database
    {
        $this->reset();
        $this->table = $table;
        return $this;
    }

    public function select(array $columns = ['*']): Database
    {
        return $this;
    }

    public function where(string $column, $operator, $value = null): Database
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second): Database
    {
        $this->joins[] = "JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): Database
    {
        $this->joins[] = "LEFT JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): Database
    {
        $this->orderBy = "ORDER BY {$column} {$direction}";
        return $this;
    }

    public function limit(int $limit, int $offset = 0): Database
    {
        $this->limit = $limit;
        $this->offset = $offset;
        return $this;
    }

    public function get(): array
    {
        $sql = $this->buildSelectQuery();
        $params = $this->getWhereParams();
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function insert(array $data): bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($data);
    }

    public function update(array $data): bool
    {
        $setClause = [];
        foreach (array_keys($data) as $column) {
            $setClause[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setClause);
        
        $sql = "UPDATE {$this->table} SET {$setClause}";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
        }
        
        $params = array_merge($data, $this->getWhereParams());
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
        }
        
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($this->getWhereParams());
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
        }
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($this->getWhereParams());
        
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    private function buildSelectQuery(): string
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWhereClause();
        }
        
        if ($this->orderBy) {
            $sql .= ' ' . $this->orderBy;
        }
        
        if ($this->limit > 0) {
            $sql .= " LIMIT {$this->limit}";
            if ($this->offset > 0) {
                $sql .= " OFFSET {$this->offset}";
            }
        }
        
        return $sql;
    }

    private function buildWhereClause(): string
    {
        $clauses = [];
        foreach ($this->wheres as $where) {
            $clauses[] = "{$where['column']} {$where['operator']} :{$where['column']}";
        }
        return implode(' AND ', $clauses);
    }

    private function getWhereParams(): array
    {
        $params = [];
        foreach ($this->wheres as $where) {
            $params[$where['column']] = $where['value'];
        }
        return $params;
    }

    private function reset(): void
    {
        $this->table = '';
        $this->wheres = [];
        $this->joins = [];
        $this->orderBy = '';
        $this->limit = 0;
        $this->offset = 0;
    }

    // Legacy compatibility methods
    public function query(string $sql): \PDOStatement
    {
        return $this->connection->query($sql);
    }

    public function select_legacy(string $sql, array $values = []): \PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($values);
        return $stmt;
    }

    public function write_legacy(string $sql, array $values = []): bool
    {
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($values);
    }

    public function insert_id(): string
    {
        return $this->lastInsertId();
    }
}
