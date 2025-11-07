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
        <h2 style="margin: 0;" class="items-list-title">All Gift Wraps<a href="/admin/gift-wraps/add?pageno=<?php echo $currentPage; ?>" class="icon-container add-item">
            <?php require(__DIR__ . "/../../public/images/site-images/icons/plus.php"); ?>
            <div class='inline-label'>Add</div></a></h2>
        
        <div class="admin-center-table-container">
            <table class="admin-center-table">
                <thead>
                    <tr>
                        <th class="th_border">ID</th>
                        <th class="th_border">Tag</th>
                        <th class="th_border">Name</th>
                        <th class="th_border">Preview</th>
                        <th class="th_border">Folder</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="admin-table-body">
                    <?php echo \App\Services\AdminRenderService::generateGiftWrapsTableHtml($giftWraps, $currentPage); ?>
                </tbody>
            </table>
        </div>
        
        <?php if(isset($all_gift_wraps) && count($all_gift_wraps) > 0 && isset($totalPages) && $totalPages > 1): ?>
            <!-- Bottom Pagination controls -->
            <?php 
            $pageno = $currentPage;
            $total_pages = $totalPages;
            $type = 'admin';
            $position = 'bottom';
            $total_count = count($all_gift_wraps);
            $item_label = 'gift wraps';
            include __DIR__ . '/../components/pagination-controls.php'; 
            ?>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($all_gift_wraps) && count($all_gift_wraps) > 0 && isset($totalPages) && $totalPages > 1): ?>
<script src="/public/js/pagination.js"></script>
<?php endif; ?>

