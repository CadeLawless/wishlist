<?php

namespace App\Services;

class FriendRenderService
{
    /**
     * Generate HTML for admin users table rows
     */
    public static function generateUserSearchResults(array $users): string
    {
        ob_start();
        
        // Return empty state if no users
        if (empty($users)) {
            return '';
        }
        
        foreach ($users as $userRow) {
            $name = htmlspecialchars($userRow['name']);
            $username = htmlspecialchars($userRow['username']);
            $existingRequest = $userRow['existing_friend_request'] ?? false;
            $disabledClass = $existingRequest ? 'disabled' : '';
            $buttonText = $existingRequest ? 'Already Sent' : 'Send Friend Request';
            ?>
            <div class="user-result">
                <div class="user-info">
                    <div class="user-name"><?= $name; ?></div>
                    <div class="user-username">@<?= $username; ?></div>
                </div>
                <div class="friend-action">
                    <a class="button primary add-friend-button <?= $disabledClass; ?>" data-username="<?= $username; ?>">
                        <span class="send-text"><?= $buttonText; ?></span>
                        <div class="loading-spinner"></div>
                    </a>
                </div>
            </div>
            <?php
        }
        return ob_get_clean();
    }

}

