<?php
// Get wishlist data
$wishlistID = $wishlist['id'];
$wishlistTitle = htmlspecialchars($wishlist['wishlist_name']);
$background_image = $wishlist['background_image'] ?? '';

// Initialize form field variables
$item_name = $item_name ?? '';
$price = $price ?? '';
$quantity = $quantity ?? '1';
$unlimited = $unlimited ?? 'No';
$link = $link ?? '';
$filename = $filename ?? '';
$notes = $notes ?? '';
$priority = $priority ?? '1';
$priority_options = ["1", "2", "3", "4"];
?>
<?php if($background_image != ""){ ?>
    <img class='background-theme desktop-background' src="/wishlist/public/images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
    <img class='background-theme mobile-background' src="/wishlist/public/images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
<?php } ?>
<p style="padding-top: 15px;"><a class="button accent" href="/wishlist/<?php echo $wishlistID; ?>">Back to List</a></p>
<div class="center">
    <div class="wishlist-header center transparent-background">
        <h1><?php echo $wishlistTitle; ?></h1>
    </div>
</div>
<div class="form-container">
    <h2>Add Item</h2>
    <?php if(isset($fetched_data) && $fetched_data): ?>
        <div class="fetched-data-notice">
            <p>✅ Product details were automatically filled from the URL you provided.</p>
        </div>
    <?php endif; ?>
    <?php if(isset($error_msg)) echo $error_msg?>
    <form method="POST" action="/wishlist/<?php echo $wishlistID; ?>/item" enctype="multipart/form-data" data-wishlist-id="<?php echo $wishlistID; ?>">
        <div class="flex form-flex">
            <?php
            $add = true;
            include __DIR__ . '/_form.php';
            ?>
            <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Add Item"></p>
        </div>
    </form>
</div>

<script>
// Load autosize and item-form scripts
document.addEventListener('DOMContentLoaded', function() {
    // Load autosize script
    const autosizeScript = document.createElement('script');
    autosizeScript.src = '/wishlist/public/scripts/autosize-master/autosize-master/dist/autosize.js';
    autosizeScript.onload = function() {
        // Load item-form script after autosize is loaded
        const itemFormScript = document.createElement('script');
        itemFormScript.src = '/wishlist/public/js/item-form.js';
        document.head.appendChild(itemFormScript);
    };
    document.head.appendChild(autosizeScript);
});
</script>
