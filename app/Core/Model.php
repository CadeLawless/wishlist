<?php

namespace App\Core;

use App\Core\Database;
use App\Core\QueryBuilder;

/**
 * Abstract base model class providing CRUD operations for all models
 * 
 * Implements Active Record pattern with common database operations.
 * All model classes should extend this class and define their table name.
 */
abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    protected QueryBuilder $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder = new QueryBuilder(static::$table);
    }

    public static function all(): array
    {
        $stmt = Database::query("SELECT * FROM " . static::$table);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::query("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?", [$id]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public static function where(string $column, string $operator, mixed $value): array
    {
        $stmt = Database::query("SELECT * FROM " . static::$table . " WHERE {$column} {$operator} ?", [$value]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO " . static::$table . " ({$columns}) VALUES ({$placeholders})";
        Database::query($sql, array_values($data));
        return Database::lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
        }
        $set = implode(', ', $set);
        $sql = "UPDATE " . static::$table . " SET {$set} WHERE " . static::$primaryKey . " = ?";
        $params = array_values($data);
        $params[] = $id;
        
        
        $stmt = Database::query($sql, $params);
        $affected = $stmt->affected_rows > 0;
        
        return $affected;
    }

    public static function delete(int $id): bool
    {
        $sql = "DELETE FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?";
        $stmt = Database::query($sql, [$id]);
        return $stmt->affected_rows > 0;
    }
}