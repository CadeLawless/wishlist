<?php 

?>

<h1 class="center">Add Friends</h1>

<?php if(count($friendships) === 0 && $sentRequestsCount === 0 && $receivedRequestsCount === 0): ?>
    <div class="center">
        <p>You have no friends or friend requests yet. <a href="/add-friends/find">Find friends to get started!</a></p>
    </div>
<?php else: ?>
    <div class="friends-section">
        <?php if($receivedRequestsCount > 0){ ?>
            <div class="notification-banner info">
                You have <?= $receivedRequestsCount; ?> pending friend request<?= $receivedRequestsCount > 1 ? 's' : ''; ?>. 
                <a class="view-requests-link" href="/add-friends/requests">View Requests</a>
            </div>
        <?php } ?>

        <?php if($sentRequestsCount > 0){ ?>
            <div class="notification-banner info">
                You have <?= $sentRequestsCount; ?> sent friend request<?= $sentRequestsCount > 1 ? 's' : ''; ?> pending. 
                <a class="view-requests-link" href="/add-friends/sent-requests">View Sent Requests</a>
            </div>
        <?php } ?>

        <?php if (count($friendships) > 0): ?>
            <div class="center">
                <?php
                $options = [
                    'placeholder' => 'Search for friends by name or username...',
                    'input_id' => 'friends-search',
                    'input_class' => 'friends-search-input',
                    'container_class' => 'center',
                    'search_term' => $searchTerm ?? ''
                ];
                include __DIR__ . '/../components/admin-table-search.php';
                ?>
            </div>
        <?php endif; ?>

        <h2>Your Friends</h2>

        <div class="friends-search-results-container">
            <?php if (count($friendships) === 0): ?>
                <p class="center">You have no friends yet. <a href="/add-friends/find">Find friends to get started!</a></p>
            <?php else: ?>
                <?php foreach($friendships as $friend){ ?>
                    <div class="friend-item">
                        <div class="friend-info">
                            <div class="friend-name"><?= htmlspecialchars($friend['name']); ?></div>
                            <div class="friend-username">@<?= htmlspecialchars($friend['username']); ?></div>
                        </div>
                    </div>
                <?php } ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>