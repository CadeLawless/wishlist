<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Wish List',
    'env' => $_ENV['APP_ENV'] ?? 'local',
    'debug' => $_ENV['APP_DEBUG'] ?? true,
    'url' => $_ENV['APP_URL'] ?? 'http://localhost/wishlist',
    'timezone' => $_ENV['TIMEZONE'] ?? 'America/Chicago',
    'email' => [
        'from' => $_ENV['MAIL_FROM'] ?? 'support@cadelawless.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'Wish List Support',
        'smtp_host' => $_ENV['MAIL_HOST'] ?? 'smtp.ionos.com',
        'smtp_username' => $_ENV['MAIL_USERNAME'] ?? 'support@cadelawless.com',
        'smtp_password' => $_ENV['MAIL_PASSWORD'] ?? 'REDACTED',
        'smtp_port' => $_ENV['MAIL_PORT'] ?? 587,
        'smtp_encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
    ],
];

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
