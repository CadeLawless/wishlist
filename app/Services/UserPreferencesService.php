<?php

namespace App\Services;

use App\Models\User;
use App\Core\Response;

class UserPreferencesService
{
    /**
     * Toggle dark mode for a user
     */
    public static function toggleDarkMode(int $userId, string $darkMode): Response
    {
        if ($darkMode !== 'Yes' && $darkMode !== 'No') {
            return new Response('invalid_data', 400);
        }

        try {
            $result = User::update($userId, ['dark' => $darkMode]);
            
            if ($result) {
                // Update session
                SessionManager::updateDarkMode($darkMode === 'Yes');
                return new Response('success');
            } else {
                return new Response('error', 500);
            }
        } catch (\Exception $e) {
            error_log('Dark mode toggle failed: ' . $e->getMessage());
            return new Response('error', 500);
        }
    }

    /**
     * Get user's dark mode preference
     */
    public static function getDarkModePreference(array $user): bool
    {
        return isset($user['dark']) && $user['dark'] === 'Yes';
    }
}
