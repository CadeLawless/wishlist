<?php if(count($receivedInvitations) > 0){ ?>
    <h2 style="margin-bottom: 0;">Suggested Friends</h2>
    <p>These fellow wishers have recently added you as their friend. Add them back below!</p>

    <div class="received-invitations-container">
        <?php echo \App\Services\FriendRenderService::generateUserSearchResults($receivedInvitations, 'received', $searchTerm ?? null); ?>
    </div>
<?php } ?>

<?php if (isset($newFriends) && count($newFriends) > 0): ?>
    <?php echo \App\Services\FriendRenderService::generateUserSearchResults($newFriends, $type ?? 'search', $searchTerm ?? null); ?>
<?php else: ?>
    <?php if (isset($newFriends)): ?>
        <div class="center" style="padding: 40px 20px; color: var(--text-secondary); font-size: 1.1em;">No results found</div>
    <?php endif; ?>
<?php endif; ?>

<?php if (count($friendList) > 0): ?>
    <h2>Your Friends</h2>

    <?php echo \App\Services\FriendRenderService::generateUserSearchResults($friendList, 'friend', $searchTerm ?? null); ?>
<?php endif; ?>