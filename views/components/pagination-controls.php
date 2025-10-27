<?php
/**
 * Reusable pagination controls component
 * 
 * @param int $pageno Current page number
 * @param int $total_pages Total number of pages
 * @param string $type Type of pagination (wisher or buyer)
 */
$pageno = $pageno ?? 1;
$total_pages = $total_pages ?? 1;
$type = $type ?? 'wisher';
?>

<?php if($total_pages > 1): ?>
<div class="center">
    <div class="paginate-container">
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
    </div>
</div>
<?php endif; ?>
