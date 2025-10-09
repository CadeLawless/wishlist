<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Wish List',
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => $_ENV['APP_DEBUG'] ?? true,
    'url' => $_ENV['APP_URL'] ?? 'http://localhost/wishlist',
    'timezone' => $_ENV['TIMEZONE'] ?? 'America/Chicago',
];

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
