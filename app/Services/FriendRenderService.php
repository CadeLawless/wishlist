<?php

namespace App\Services;

class FriendRenderService
{
    /**
     * Generate HTML for admin users table rows
     */
    public static function generateUserSearchResults(array $users, string $type = 'search', ?string $searchTerm = null): string
    {
        ob_start();
        
        // Return empty state if no users
        if (empty($users)) {
            return '';
        }
        
        foreach ($users as $userRow) {
            $name = htmlspecialchars($userRow['name']);
            $username = htmlspecialchars($userRow['username']);
            $profilePicture = $userRow['profile_picture'] ?? null;
            $existingFriend = $userRow['already_added'] ?? false;
            $disabledClass = $existingFriend ? 'disabled' : '';
            $buttonText = $existingFriend ? 'Added' : 'Add Friend';
            $hasAddedUser = $userRow['has_added_user'] ?? false;
            $marginBottomStyle = $type === 'public' ? 'margin-bottom: 1rem;' : '';
            $userUploadClass = $type === 'profile' ? 'user-upload' : '';
            $userResultClass = $type === 'profile' ? 'user-result-profile' : '';
            ?>
            <div class="user-result <?= $userResultClass; ?>" style="<?= $marginBottomStyle; ?>">
                <div class="user-info">
                    <div class="user-profile-picture <?= $userUploadClass; ?>" <?= $userUploadClass ? 'title="Upload New Photo"' : ''; ?>>
                        <?php if ($profilePicture): ?>
                            <img class="profile-picture popup-button" src="/public/images/profile-pictures/<?= htmlspecialchars($profilePicture); ?>?t=<?= time(); ?>" alt="<?= $name; ?>'s Profile Picture" />
                        <?php else: ?>
                            <div class="profile-picture popup-button placeholder"><?= strtoupper($name[0]); ?></div>
                        <?php endif; ?>
                        <?php if($type === 'profile'): ?>
                            <div class="popup-container hidden">
                                <div class="popup">
                                    <div class="close-container">
                                        <a href="#" class="close-button">
                                            <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                        </a>
                                    </div>
                                    <div class="popup-content">
                                        <h2 style="margin-top: 0;">Change Profile Picture</h2>
                                        <input type="file" id="upload" accept="image/*" />

                                        <div id="croppie-container" class="hidden"></div>

                                        <div class="center">
                                            <button id="crop-btn" class="button primary">
                                                <span class="send-text">Save Picture</span>
                                                <div class="loading-spinner"></div>
                                            </button>
                                        </div>                                
                                    </div>                                
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?= $name; ?></div>
                        <div class="user-username">@<?= $username; ?></div>
                        <?php if($type !== 'public' && $type !== 'profile'): ?>
                            <div class="user-wishlists-link">
                                <a class="button" href="/<?= $username; ?>/wishlists?search=<?=  $searchTerm ?? $username; ?>">
                                    <?php require __DIR__ . '/../../public/images/site-images/icons/wishlist.php'; ?>
                                    <span>View Wishlists</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if($type !== 'public'): ?>
                    <div class="friend-actions">
                        <?php if ($type === 'profile'): ?>
                            <button class="button primary" id="change-profile-picture">Change Profile Picture</button>
                        <?php else: ?>
                            <?php if ($type === 'search'): ?>
                                <div class="add-friend-button-container">
                                    <a class="button primary add-friend-button full-width <?= $disabledClass; ?>" data-username="<?= $username; ?>" data-original-text="Add Friend" data-fail-text="Failed">
                                        <span class="send-text"><?= $buttonText; ?></span>
                                        <div class="loading-spinner"></div>
                                    </a>
                                    <?php if ($hasAddedUser && !$existingFriend): ?>
                                        <div class="info-text">Wisher has already added you as a friend!</div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($type === 'friend'): ?>
                                    <a class="button secondary remove-button <?= $disabledClass; ?>" data-username="<?= $username; ?>" data-original-text="Remove Friend" data-fail-text="Failed">
                                        <span class="send-text">Remove Friend</span>
                                        <div class="loading-spinner"></div>
                                    </a>
                            <?php elseif ($type === 'received'): ?>
                                <div class="decision-buttons">
                                    <a class="button secondary decline-button" data-username="<?= $username; ?>"data-original-text="Remove" data-fail-text="Failed">
                                        <span class="send-text">Remove</span>
                                        <div class="loading-spinner"></div>
                                    </a>
                                    <a class="button primary add-friend-button accept-button" data-username="<?= $username; ?>"data-original-text="Add Friend" data-fail-text="Failed">
                                        <span class="send-text">Add Friend</span>
                                        <div class="loading-spinner"></div>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }
        return ob_get_clean();
    }

}

