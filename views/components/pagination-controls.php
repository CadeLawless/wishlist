<?php
/**
 * Reusable pagination controls component
 * 
 * @param int $pageno Current page number
 * @param int $total_pages Total number of pages
 * @param string $type Type of pagination (wisher or buyer)
 * @param string $position Position of pagination (top, bottom, or empty)
 * @param int|null $total_count Total count of items for count-showing (only shown in bottom position)
 * @param string|null $item_label Label for items (e.g., "items", "wishlists")
 */
$pageno = $pageno ?? 1;
$total_pages = $total_pages ?? 1;
$type = $type ?? 'wisher';
$position = $position ?? '';
$total_count = $total_count ?? null;
$item_label = $item_label ?? 'items';
?>

<?php if($total_pages > 1): ?>
<div class="center">
    <div class="paginate-container<?php echo $position ? ' ' . $position : ''; ?>">
        <a class="paginate-arrow paginate-first<?php echo $pageno <= 1 ? ' disabled' : ''; ?>" href="#">
            <?php require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'site-images' . DIRECTORY_SEPARATOR . 'first.php'); ?>
        </a>
        <a class="paginate-arrow paginate-previous<?php echo $pageno <= 1 ? ' disabled' : ''; ?>" href="#">
            <?php require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'site-images' . DIRECTORY_SEPARATOR . 'prev.php'); ?>
        </a>
        <div class="paginate-title">
            <span class="page-number"><?php echo $pageno; ?></span>/<span class="last-page"><?php echo $total_pages; ?></span>
        </div>
        <a class="paginate-arrow paginate-next<?php echo $pageno >= $total_pages ? ' disabled' : ''; ?>" href="#">
            <?php require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'site-images' . DIRECTORY_SEPARATOR . 'prev.php'); ?>
        </a>
        <a class="paginate-arrow paginate-last<?php echo $pageno >= $total_pages ? ' disabled' : ''; ?>" href="#">
            <?php require(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'site-images' . DIRECTORY_SEPARATOR . 'first.php'); ?>
        </a>
        <?php if($position === 'bottom' && $total_count !== null && $total_count > 0): ?>
            <div class="count-showing">Showing <?php echo (($pageno - 1) * 12) + 1; ?>-<?php echo min($pageno * 12, $total_count); ?> of <?php echo $total_count; ?> <?php echo $item_label; ?></div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
