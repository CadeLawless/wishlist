<?php

namespace App\Core;

class Config
{
    private static array $config = [];

    public static function load(): void
    {
        // Load .env file
        if (file_exists('.env')) {
            $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    [$key, $value] = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }

        // Load config files
        self::$config['app'] = require 'config/app.php';
        self::$config['database'] = require 'config/database.php';
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
