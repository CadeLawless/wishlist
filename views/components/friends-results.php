<?php if(count($receivedInvitations) > 0){ ?>
    <h2 style="margin-bottom: 0;">Suggested Friends</h2>
    <p>These fellow wishers have recently added you as their friend. Add them back below!</p>

    <div class="received-invitations-container">
        <?php echo \App\Services\FriendRenderService::generateUserSearchResults($receivedInvitations, 'received'); ?>
    </div>
<?php } ?>

<?php if (isset($newFriends) && count($newFriends) > 0): ?>
    <?php echo \App\Services\FriendRenderService::generateUserSearchResults($newFriends, $type ?? 'friend'); ?>
<?php endif; ?>

<?php if (count($friendList) > 0): ?>
    <h2>Your Friends</h2>

    <?php echo \App\Services\FriendRenderService::generateUserSearchResults($friendList, 'friend'); ?>
<?php endif; ?>