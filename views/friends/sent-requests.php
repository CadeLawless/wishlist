
<?php if(count($sentFriendRequests) === 0): ?>
    <div class="center">
        <p>You don't have any sent friend requests that are still pending.</p>
    </div>
<?php else: ?>
    <p>
        <a class="button accent" href="/add-friends">
            Back to Add Friends
        </a>
    </p>
    <div class="friends-section">
        <?php if(count($sentFriendRequests) > 0): ?>
            <h2>Sent Friend Requests</h2>
            <div class="friends-search-results-container">
                <?php echo \App\Services\FriendRenderService::generateUserSearchResults($sentFriendRequests, 'sent'); ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>