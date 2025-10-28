<?php

namespace App\Core;

use Dotenv\Dotenv;

class Config
{
    private static array $config = [];

    public static function load(): void
    {
        // Load .env file using vlucas/phpdotenv package
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->safeLoad();

        // Load config files after .env is loaded
        self::$config['app'] = require __DIR__ . '/../../config/app.php';
        self::$config['database'] = require __DIR__ . '/../../config/database.php';
    }

    public static function get(string $key, $default = null): mixed
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

function env(string $key, $default = null): mixed
{
    return $_ENV[$key] ?? $default;
}
