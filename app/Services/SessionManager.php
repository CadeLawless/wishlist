<?php

namespace App\Services;

class SessionManager
{
    /**
     * Initialize session if not already started
     */
    public static function startSession(): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }
    }

    /**
     * Set up session for successful registration
     */
    public static function setupRegistrationSession(array $userData): void
    {
        self::startSession();
        
        // Set session variables
        $_SESSION['wishlist_logged_in'] = true;
        $_SESSION['username'] = $userData['username'];
        $_SESSION['account_created'] = true;
        
        // Set remember me cookie
        $cookieTime = 3600 * 24 * 365; // 1 year
        setcookie('wishlist_session_id', session_id(), time() + $cookieTime);
    }

    /**
     * Update dark mode preference in session
     */
    public static function updateDarkMode(bool $isDark): void
    {
        self::startSession();
        $_SESSION['dark'] = $isDark;
    }

    /**
     * Store buyer sort preferences in session
     */
    public static function storeBuyerSortPreferences(string $sortPriority, string $sortPrice): void
    {
        self::startSession();
        $_SESSION['buyer_sort_priority'] = $sortPriority;
        $_SESSION['buyer_sort_price'] = $sortPrice;
    }

    /**
     * Get buyer sort preferences from session
     */
    public static function getBuyerSortPreferences(): array
    {
        self::startSession();
        return [
            'sort_priority' => $_SESSION['buyer_sort_priority'] ?? '',
            'sort_price' => $_SESSION['buyer_sort_price'] ?? ''
        ];
    }

    /**
     * Store wisher sort preferences in session
     */
    public static function storeWisherSortPreferences(string $sortPriority, string $sortPrice): void
    {
        self::startSession();
        $_SESSION['wisher_sort_priority'] = $sortPriority;
        $_SESSION['wisher_sort_price'] = $sortPrice;
    }

    /**
     * Get wisher sort preferences from session
     */
    public static function getWisherSortPreferences(): array
    {
        self::startSession();
        return [
            'sort_priority' => $_SESSION['wisher_sort_priority'] ?? '',
            'sort_price' => $_SESSION['wisher_sort_price'] ?? ''
        ];
    }

    /**
     * Clear all session data
     */
    public static function clearSession(): void
    {
        self::startSession();
        session_destroy();
    }
}
