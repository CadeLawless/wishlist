<?php

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration
\App\Core\Config::load();

// Set timezone
date_default_timezone_set(\App\Core\Config::get('app.timezone', 'America/Chicago'));

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load routes
require_once __DIR__ . '/../routes/web.php';

// Create request object
$request = new \App\Core\Request();

// Dispatch the request
$response = \App\Core\Router::dispatch($request);

// Send the response
$response->send();
