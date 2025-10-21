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

    /**
     * Set wishlist context for navigation
     */
    public static function setWishlistContext(int $wishlistId, int $pageno = 1): void
    {
        self::startSession();
        $_SESSION['wisher_wishlist_id'] = $wishlistId;
        $_SESSION['home'] = "/wishlist/{$wishlistId}?pageno={$pageno}#paginate-top";
        $_SESSION['type'] = 'wisher';
    }

    /**
     * Set flash message for wishlist operations
     */
    public static function setWishlistFlash(string $message, string $type = 'success'): void
    {
        self::startSession();
        $_SESSION["wishlist_{$type}"] = $message;
    }

    /**
     * Get and clear flash messages
     */
    public static function getFlashMessages(): array
    {
        self::startSession();
        $messages = [];
        
        $flashKeys = ['success', 'error', 'wishlist_hidden', 'wishlist_public', 'wishlist_complete', 'wishlist_reactivated'];
        
        foreach ($flashKeys as $key) {
            if (isset($_SESSION[$key])) {
                $messages[$key] = $_SESSION[$key];
                unset($_SESSION[$key]);
            }
        }
        
        return $messages;
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        self::startSession();
        return isset($_SESSION['wishlist_logged_in']) && $_SESSION['wishlist_logged_in'] === true;
    }

    /**
     * Get current username from session
     */
    public static function getUsername(): ?string
    {
        self::startSession();
        return $_SESSION['username'] ?? null;
    }

    /**
     * Set user login session
     */
    public static function setLoginSession(string $username): void
    {
        self::startSession();
        $_SESSION['wishlist_logged_in'] = true;
        $_SESSION['username'] = $username;
    }

    /**
     * Logout user and clear session
     */
    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        session_destroy();
    }
}
