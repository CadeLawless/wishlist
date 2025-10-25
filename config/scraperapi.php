<?php

/**
 * ScraperAPI Configuration
 * 
 * To use ScraperAPI for Amazon and other blocked sites:
 * 1. Sign up at https://www.scraperapi.com/
 * 2. Get your free API key (2,000 free requests)
 * 3. Set the API key in .env file
 */

use Dotenv\Dotenv;

// Load .env file using phpdotenv
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

return [
    'api_key' => $_ENV['SCRAPERAPI_KEY'] ?? '', // Set in .env file
    'enabled' => !empty($_ENV['SCRAPERAPI_KEY'] ?? ''),
    'free_tier_limit' => 5000, // Free requests per month
];
