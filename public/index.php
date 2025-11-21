<?php

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Enable error reporting for debugging only in DEVELOPMENT environment
if ($_ENV['APP_ENV'] === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

// Load configuration
\App\Core\Config::load();

// Set timezone
date_default_timezone_set(\App\Core\Config::get('app.timezone', 'America/Chicago'));

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for remember me cookie and auto-login if needed
$authService = new \App\Services\AuthService();
$authService->checkSession();

// Load routes
require_once __DIR__ . '/../routes/web.php';

// Create request object
$request = new \App\Core\Request();

// Dispatch the request
$response = \App\Core\Router::dispatch($request);

// Send the response
$response->send();
