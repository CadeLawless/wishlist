<?php

namespace App\Helpers;

class PopupHelper
{
    /**
     * Check for and display session-based popup messages
     */
    public static function handleSessionPopups(): void
    {
        $popups = [
            'wishlist_hidden' => 'Wish list successfully hidden',
            'wishlist_public' => 'Wishlist is now public',
            'wishlist_complete' => 'Wish list successfully marked as complete',
            'wishlist_reactivated' => 'Wish list successfully reactivated',
            'item_deleted' => 'Item deleted successfully',
            'copy_from_success' => 'Item(s) copied over successfully'
        ];

        foreach ($popups as $sessionKey => $message) {
            if (\App\Services\SessionManager::has($sessionKey)) {
                self::displayPopup($message);
                \App\Services\SessionManager::remove($sessionKey);
            }
        }
    }

    /**
     * Display a popup with the given message
     */
    public static function displayPopup(string $message, string $type = 'success'): void
    {
        $textColor = $type === 'error' ? "style='color: #e74c3c;'" : '';
        
        echo "
        <div class='popup-container'>
            <div class='popup active'>
                <div class='close-container'>
                    <a href='#' class='close-button'>";
                    require(__DIR__ . '/../../public/images/site-images/menu-close.php');
                    echo "</a>
                </div>
                <div class='popup-content'>
                    <p><label {$textColor}>{$message}</label></p>
                </div>
            </div>
        </div>";
    }

    /**
     * Handle modern flash messages from Response class
     * @param array|null $flash Optional flash data array (if null, will try to get from session)
     */
    public static function handleFlashMessages(?array $flash = null): void
    {
        // If flash data not provided, try to get from session
        // (though it may have already been cleared by getFlashMessages() in View constructor)
        if ($flash === null) {
            $flash = \App\Services\SessionManager::get('flash', []);
        }
        
        if (isset($flash['success'])) {
            self::displayPopup($flash['success'], 'success');
        }

        if (isset($flash['error'])) {
            self::displayPopup($flash['error'], 'error');
        }
        
        // Don't clear rename_error - it's handled separately in the rename form popup
        // The flash data was already cleared by getFlashMessages() in View constructor
    }
}
