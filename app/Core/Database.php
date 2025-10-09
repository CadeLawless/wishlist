<?php

namespace App\Core;

use mysqli;

class Database
{
    private static ?mysqli $connection = null;

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
                    die("Database connection failed: " . self::$connection->connect_error);
                }
                
                self::$connection->set_charset($config['charset']);
            } catch (\Exception $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    public static function query(string $sql, array $params = []): \mysqli_stmt
    {
        $stmt = self::connect()->prepare($sql);
        if (!$stmt) {
            die("Prepare failed: " . self::connect()->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Assume all strings for now
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
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
}