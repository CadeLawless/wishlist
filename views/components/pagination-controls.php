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

// For admin pages, use ADMIN_ITEMS_PER_PAGE (10), otherwise use 12
$items_per_page = ($type === 'admin') ? 10 : 12;

// Show pagination container based on position
// Top: only show if there are more than items_per_page (more than 1 page)
// Bottom: show if we have results to show count, or if there's more than one page
// BUT always render containers (even if hidden) so JavaScript can show them dynamically
if ($position === 'top') {
    // Top pagination: only show if more than items_per_page results
    // BUT always render the container (even if hidden) so JavaScript can show it dynamically
    $show_pagination = ($total_count !== null && $total_count > $items_per_page) || ($total_count === null && $total_pages > 1);
    $always_render = true; // Always render top pagination container for JavaScript control
} else {
    // Bottom pagination: show if we have results to show count, or if there's more than one page
    // BUT always render the container (even if hidden) so JavaScript can show it dynamically
    $show_pagination = ($position === 'bottom' && $total_count !== null && $total_count > 0) || $total_pages > 1;
    $always_render = true; // Always render bottom pagination container for JavaScript control
}
?>

<?php if($show_pagination || $always_render): ?>
<div class="center"<?php echo (!$show_pagination) ? ' style="display: none;"' : ''; ?>>
    <div class="paginate-container<?php echo $position ? ' ' . $position : ''; ?>">
        <?php 
        // Always render controls (even if total_pages is 1) so JavaScript can update them dynamically
        // This ensures pagination controls are available when search results change
        $render_controls = $always_render || $total_pages > 1;
        ?>
        <?php if($render_controls): ?>
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
        <?php endif; ?>
        <?php 
        // For bottom pagination, always render count-showing div so JavaScript can update it
        if ($position === 'bottom') {
            if ($total_count !== null && $total_count > 0) {
                echo '<div class="count-showing">Showing ' . ((($pageno - 1) * $items_per_page) + 1) . '-' . min($pageno * $items_per_page, $total_count) . ' of ' . $total_count . ' ' . $item_label . '</div>';
            } else {
                // Render empty div for JavaScript to populate later when results change
                echo '<div class="count-showing"></div>';
            }
        }
        ?>
    </div>
</div>
<?php endif; ?>
