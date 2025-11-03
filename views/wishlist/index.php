<?php
// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['success']) . "</label></p>
            </div>
        </div>
    </div>";
}

if (isset($flash['error'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['error']) . "</label></p>
            </div>
        </div>
    </div>";
}
?>

<h1 class="center"><?php echo $user['name']; ?>'s Wish Lists</h1>
<p class="center" style="margin: 0 0 36px;"><a class="button primary" href="/wishlist/create">Create a New Wish List</a></p>

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
    <!-- Top Pagination controls -->
    <?php 
    $position = 'top';
    include __DIR__ . '/../components/pagination-controls.php'; 
    ?>
<?php endif; ?>

<div class="wishlist-grid">
    <?php
    if(count($wishlists) > 0){
        // Use WishlistRenderService to generate the HTML
        echo \App\Services\WishlistRenderService::generateWishlistsHtml($wishlists);
    }else{
        echo "<p style='grid-column: 1 / -1;' class='center'>It doesn't look like you have any wish lists created yet</p>";
    }
    ?>
</div>

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
    <!-- Bottom Pagination controls -->
    <?php 
    $position = 'bottom';
    include __DIR__ . '/../components/pagination-controls.php'; 
    ?>
    
    <?php if(count($all_wishlists) > 0): ?>
    <div class="center count-showing">Showing <?php echo (($pageno - 1) * 12) + 1; ?>-<?php echo min($pageno * 12, count($all_wishlists)); ?> of <?php echo count($all_wishlists); ?> wishlists</div>
    <?php endif; ?>
<?php endif; ?>

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($total_pages) && $total_pages > 1): ?>
<script src="/wishlist/public/js/pagination.js"></script>
<?php endif; ?>
