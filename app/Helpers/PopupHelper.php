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
     */
    public static function handleFlashMessages(): void
    {
        if (\App\Services\SessionManager::has('flash.success')) {
            self::displayPopup(\App\Services\SessionManager::get('flash.success'), 'success');
            \App\Services\SessionManager::remove('flash.success');
        }

        if (\App\Services\SessionManager::has('flash.error')) {
            self::displayPopup(\App\Services\SessionManager::get('flash.error'), 'error');
            \App\Services\SessionManager::remove('flash.error');
        }
    }
}
