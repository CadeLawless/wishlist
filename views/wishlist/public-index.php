<p>
    <a class="button accent" href="/add-friends<?= isset($_GET['search']) ? '?search=' . htmlspecialchars(urlencode($_GET['search'])) : ''; ?>">
        Back to Add Friends
    </a>
</p>

<?php
$friendList = [];
$receivedInvitations = [];
$newFriends = [$public_user];
$type = 'public';
require __DIR__ . '/../../views/components/friends-results.php';
?>

<div class="wishlist-grid">
    <?php
    if(count($wishlists) > 0){
        // Use WishlistRenderService to generate the HTML
        echo \App\Services\WishlistRenderService::generateWishlistsHtml($wishlists, 'public');
    }else{
        echo "<p style='grid-column: 1 / -1;' class='center'>It doesn't look like " . htmlspecialchars($public_user['name']) . " have any wish lists created yet!</p>";
    }
    ?>
</div>
