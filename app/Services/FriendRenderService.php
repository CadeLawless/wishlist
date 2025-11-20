<?php

namespace App\Services;

class FriendRenderService
{
    /**
     * Generate HTML for admin users table rows
     */
    public static function generateUserSearchResults(array $users, string $type = 'search'): string
    {
        ob_start();
        
        // Return empty state if no users
        if (empty($users)) {
            return '';
        }
        
        foreach ($users as $userRow) {
            $name = htmlspecialchars($userRow['name']);
            $username = htmlspecialchars($userRow['username']);
            $existingFriend = $userRow['already_added'] ?? false;
            $disabledClass = $existingFriend ? 'disabled' : '';
            $buttonText = $existingFriend ? 'Added' : 'Add Friend';
            ?>
            <div class="user-result">
                <div class="user-info">
                    <div class="user-name"><?= $name; ?></div>
                    <div class="user-username">@<?= $username; ?></div>
                    <div class="user-wishlists-link">
                        <a class="button" href="/<?= $username; ?>/wishlists">
                            <?php require __DIR__ . '/../../public/images/site-images/icons/wishlist.php'; ?>
                            <span>View Wishlists</span>
                        </a>
                    </div>
                </div>
                <div class="friend-actions">
                    <?php if ($type === 'search'): ?>
                        <a class="button primary add-friend-button <?= $disabledClass; ?>" data-username="<?= $username; ?>" data-original-text="Add Friend" data-fail-text="Failed">
                            <span class="send-text"><?= $buttonText; ?></span>
                            <div class="loading-spinner"></div>
                        </a>
                    <?php elseif ($type === 'sent'): ?>
                        <a href="/add-friends/cancel/<?= htmlspecialchars($userRow['receiver_username']); ?>" class="button secondary cancel-button" data-original-text="Cancel" data-fail-text="Failed">Cancel</a>
                    <?php elseif ($type === 'received'): ?>
                        <div class="decision-buttons">
                            <a class="button secondary decline-button" data-username="<?= $username; ?>"data-original-text="Remove" data-fail-text="Failed">
                                <span>Remove</span>
                                <div class="loading-spinner"></div>
                            </a>
                            <a class="button primary accept-button" data-username="<?= $username; ?>"data-original-text="Accept" data-fail-text="Failed">
                                <span>Add Friend</span>
                                <div class="loading-spinner"></div>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        return ob_get_clean();
    }

}

