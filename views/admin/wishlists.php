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

<h1 class="center">Admin Center</h1>
<div class="sidebar-main">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <div class="content">
        <h2 style="margin: 0;" class="items-list-title">All Wish Lists</h2>
        
        <div class="admin-center-table-container">
            <table class="admin-center-table">
                <thead>
                    <tr>
                        <th class="th_border">ID</th>
                        <th class="th_border">Name</th>
                        <th class="th_border">User</th>
                        <th class="th_border">Type</th>
                        <th class="th_border">Secret Key</th>
                        <th class="th_border">Date Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <?php echo \App\Services\AdminRenderService::generateWishlistsTableHtml($wishlists, $currentPage); ?>
                </tbody>
            </table>
        </div>
        
        <?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($totalPages) && $totalPages > 1): ?>
            <!-- Bottom Pagination controls -->
            <?php 
            $pageno = $currentPage;
            $total_pages = $totalPages;
            $type = 'admin';
            $position = 'bottom';
            $total_count = count($all_wishlists);
            $item_label = 'wishlists';
            include __DIR__ . '/../components/pagination-controls.php'; 
            ?>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($all_wishlists) && count($all_wishlists) > 0 && isset($totalPages) && $totalPages > 1): ?>
<script src="/public/js/pagination.js"></script>
<?php endif; ?>

