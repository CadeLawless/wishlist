<?php

namespace App\Core;

use mysqli;
use App\Core\Exceptions\DatabaseException;

/**
 * Database connection and query management
 * 
 * Provides singleton database connection and prepared statement execution.
 * Handles connection management, query execution, and error handling.
 */
class Database
{
    private static ?mysqli $connection = null;

    /**
     * Get database connection (singleton pattern)
     * 
     * Creates and returns a single database connection instance.
     * Connection is reused across all database operations.
     * 
     * @return mysqli Database connection instance
     */
    public static function connect(): mysqli
    {
        if (self::$connection === null) {
            $config = Config::get('database.connections.mysql');
            try {
                self::$connection = new mysqli(
                    $config['host'],
                    $config['username'],
                    $config['password'],
                    $config['database']
                );
                
                if (self::$connection->connect_error) {
                    throw new DatabaseException("Database connection failed: " . self::$connection->connect_error);
                }
                
                self::$connection->set_charset($config['charset']);
            } catch (\Exception $e) {
                throw new DatabaseException("Database connection failed: " . $e->getMessage(), 0, $e);
            }
        }
        return self::$connection;
    }

    /**
     * Execute a prepared SQL query with modern PHP 8.1+ features
     * 
     * Executes SQL queries with parameter binding for security.
     * Uses PHP 8.1+ execute() with parameters for cleaner code.
     * 
     * @param string $sql SQL query with placeholders (?)
     * @param array $params Parameters to bind to the query
     * @return \mysqli_stmt Prepared statement ready for execution
     */
    public static function query(string $sql, array $params = []): \mysqli_stmt
    {
        $stmt = self::connect()->prepare($sql);
        if (!$stmt) {
            throw new DatabaseException("Prepare failed: " . self::connect()->error);
        }
        
        $stmt->execute($params);
        return $stmt;
    }

    public static function lastInsertId(): int
    {
        return self::connect()->insert_id;
    }

    // Legacy compatibility methods
    public static function select(string $sql, array $params = []): \mysqli_stmt
    {
        return self::query($sql, $params);
    }

    public static function write(string $sql, array $params = []): bool
    {
        $stmt = self::query($sql, $params);
        return $stmt !== false;
    }

    public static function insert_id(): int
    {
        return self::lastInsertId();
    }

    public static function beginTransaction(): void
    {
        self::connect()->begin_transaction();
    }

    public static function commit(): void
    {
        self::connect()->commit();
    }

    public static function rollback(): void
    {
        self::connect()->rollback();
    }
}