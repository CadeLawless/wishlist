<?php

namespace App\Core;

class Config
{
    private static array $config = [];

    public static function load(): void
    {
        // Load .env file first
        $envPath = __DIR__ . '/../../.env';
        
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    [$key, $value] = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);
                    // Remove quotes if present
                    if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || 
                        (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                        $value = substr($value, 1, -1);
                    }
                    $_ENV[$key] = $value;
                }
            }
        }

        // Load config files after .env is loaded
        self::$config['app'] = require __DIR__ . '/../../config/app.php';
        self::$config['database'] = require __DIR__ . '/../../config/database.php';
    }

    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }

        return $value;
    }
}

function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $default;
}
