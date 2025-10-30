<?php

namespace Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use App\Core\Database;
use App\Core\Config;

abstract class TestCase extends PHPUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test database connection
        $this->setupTestDatabase();
        
        // Clear any existing data
        $this->clearTestData();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->clearTestData();
        
        parent::tearDown();
    }

    /**
     * Set up test database connection
     */
    protected function setupTestDatabase(): void
    {
        // Use in-memory SQLite for testing
        $config = [
            'host' => ':memory:',
            'dbname' => 'test_wishlist',
            'username' => '',
            'password' => '',
            'charset' => 'utf8mb4'
        ];
        
        // Initialize database connection
        Database::connect($config);
        
        // Create test tables
        $this->createTestTables();
    }

    /**
     * Create test database tables
     */
    protected function createTestTables(): void
    {
        // Create users table
        $usersTable = "
            CREATE TABLE IF NOT EXISTS wishlist_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                session VARCHAR(255) NULL,
                session_expiration DATETIME NULL,
                verification_token VARCHAR(255) NULL,
                verification_expires_at DATETIME NULL,
                reset_token VARCHAR(255) NULL,
                reset_expires_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";

        // Create wishlists table
        $wishlistsTable = "
            CREATE TABLE IF NOT EXISTS wishlists (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL,
                wishlist_name VARCHAR(100) NOT NULL,
                type ENUM('Birthday', 'Christmas', 'General') NOT NULL,
                background_theme VARCHAR(100) NULL,
                gift_wrap_theme VARCHAR(100) NULL,
                is_public BOOLEAN DEFAULT TRUE,
                is_complete BOOLEAN DEFAULT FALSE,
                secret_key VARCHAR(10) UNIQUE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_username (username),
                INDEX idx_secret_key (secret_key)
            )
        ";

        // Create items table
        $itemsTable = "
            CREATE TABLE IF NOT EXISTS items (
                id INT AUTO_INCREMENT PRIMARY KEY,
                wishlist_id INT NOT NULL,
                item_name VARCHAR(100) NOT NULL,
                price DECIMAL(10,2) NOT NULL,
                quantity INT DEFAULT 1,
                priority INT DEFAULT 1,
                link VARCHAR(500) NULL,
                notes TEXT NULL,
                image VARCHAR(255) NULL,
                copy_id VARCHAR(255) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_wishlist_id (wishlist_id),
                INDEX idx_copy_id (copy_id)
            )
        ";

        // Execute table creation
        Database::query($usersTable);
        Database::query($wishlistsTable);
        Database::query($itemsTable);
    }

    /**
     * Clear all test data
     */
    protected function clearTestData(): void
    {
        Database::query("DELETE FROM items");
        Database::query("DELETE FROM wishlists");
        Database::query("DELETE FROM wishlist_users");
    }

    /**
     * Create a test user
     */
    protected function createTestUser(array $data = []): array
    {
        $defaultData = [
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $userData = array_merge($defaultData, $data);
        
        $sql = "INSERT INTO wishlist_users (username, name, email, password, created_at) VALUES (?, ?, ?, ?, ?)";
        Database::query($sql, [
            $userData['username'],
            $userData['name'],
            $userData['email'],
            $userData['password'],
            $userData['created_at']
        ]);

        $userId = Database::lastInsertId();
        return array_merge($userData, ['id' => $userId]);
    }

    /**
     * Create a test wishlist
     */
    protected function createTestWishlist(array $data = []): array
    {
        $defaultData = [
            'username' => 'testuser',
            'wishlist_name' => 'Test Wishlist',
            'type' => 'General',
            'secret_key' => 'test123456',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $wishlistData = array_merge($defaultData, $data);
        
        $sql = "INSERT INTO wishlists (username, wishlist_name, type, secret_key, created_at) VALUES (?, ?, ?, ?, ?)";
        Database::query($sql, [
            $wishlistData['username'],
            $wishlistData['wishlist_name'],
            $wishlistData['type'],
            $wishlistData['secret_key'],
            $wishlistData['created_at']
        ]);

        $wishlistId = Database::lastInsertId();
        return array_merge($wishlistData, ['id' => $wishlistId]);
    }

    /**
     * Create a test item
     */
    protected function createTestItem(array $data = []): array
    {
        $defaultData = [
            'wishlist_id' => 1,
            'item_name' => 'Test Item',
            'price' => 19.99,
            'quantity' => 1,
            'priority' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $itemData = array_merge($defaultData, $data);
        
        $sql = "INSERT INTO items (wishlist_id, item_name, price, quantity, priority, created_at) VALUES (?, ?, ?, ?, ?, ?)";
        Database::query($sql, [
            $itemData['wishlist_id'],
            $itemData['item_name'],
            $itemData['price'],
            $itemData['quantity'],
            $itemData['priority'],
            $itemData['created_at']
        ]);

        $itemId = Database::lastInsertId();
        return array_merge($itemData, ['id' => $itemId]);
    }

    /**
     * Assert that validation errors contain specific field errors
     */
    protected function assertValidationError(array $errors, string $field, string $expectedMessage = null): void
    {
        $this->assertArrayHasKey($field, $errors, "Expected validation error for field '{$field}'");
        
        if ($expectedMessage) {
            $this->assertContains($expectedMessage, $errors[$field], "Expected error message '{$expectedMessage}' for field '{$field}'");
        }
    }

    /**
     * Assert that no validation errors exist for a field
     */
    protected function assertNoValidationError(array $errors, string $field): void
    {
        $this->assertArrayNotHasKey($field, $errors, "Expected no validation error for field '{$field}'");
    }
}
