<?php
// This is a placeholder - the file is being built in parts
// Wishlist ID and basic data
$wishlistID = $wishlist['id'];
$wishlist_name = htmlspecialchars($wishlist['wishlist_name']);
$wishlistTitle = $wishlist_name;
$year = $wishlist['year'];
$type = $wishlist['type'];
$duplicate = $wishlist['duplicate'] == 0 ? "" : " ({$wishlist['duplicate']})";
$secret_key = $wishlist['secret_key'];
$theme_background_id = $wishlist['theme_background_id'];
$theme_gift_wrap_id = $wishlist['theme_gift_wrap_id'];
$visibility = $wishlist['visibility'];
$complete = $wishlist['complete'];

// Get background image if theme is set
$background_image = '';
if($theme_background_id != 0){
    $stmt = \App\Core\Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_background_id]);
    $bg_row = $stmt->get_result()->fetch_assoc();
    if($bg_row){
        $background_image = $bg_row['theme_image'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/wishlist/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/wishlist/css/styles.css" />
    <link rel="stylesheet" type="text/css" href="/wishlist/css/snow.css" />
    <title><?php echo $wishlistTitle; ?></title>
    <style>
        h1 {
            display: inline-block;
        }
        h2.items-list-title {
            position: relative;
        }
        #container .background-theme.mobile-background {
            display: none;
        }
        @media (max-width: 600px){
            #container .background-theme.mobile-background {
                display: block;
            }
            #container .background-theme.desktop-background {
                display: none;
            }
        }
    </style>
</head>
<body class="<?php echo isset($_SESSION['dark']) && $_SESSION['dark'] ? 'dark' : ''; ?>">
    <div id="body">
        <?php include __DIR__ . '/../components/header.php'; ?>
        <input type="hidden" id="wishlist_type" value="<?php echo strtolower($type); ?>" />
        <div id="container">
            <?php if($theme_background_id != 0 && $background_image){ ?>
                <img class='background-theme desktop-background' src="/wishlist/images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
                <img class='background-theme mobile-background' src="/wishlist/images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
            <?php } ?>
            <p style="padding-top: 15px;"><a class="button accent" href="/wishlist/wishlists">Back to All Wish Lists</a></p>

            <div class="center">
                <div class="wishlist-header center transparent-background">
                    <h1><?php echo $wishlistTitle; ?></h1>
                    <div class="flex-row">
                        <div><strong>Status:</strong> <?php echo $complete == "Yes" ? "Complete" : "Not Complete"; ?></div>
                        <div><strong>Visibility:</strong> <?php echo htmlspecialchars($visibility); ?></div>
                    </div>
                    <a class="button primary flex-button popup-button" href="#">
                        <?php require(__DIR__ . '/../../images/site-images/icons/settings.php'); ?>
                        <span>Wish List Options</span>
                    </a>
                    <!-- Wishlist options popup will go here -->
                </div>
            </div>

            <div class='items-list-container'>
                <h2 class='transparent-background items-list-title' id='paginate-top'>
                    All Items
                    <a href='/wishlist/add-item.php' class='icon-container add-item'>
                        <?php require(__DIR__ . '/../../images/site-images/icons/plus.php'); ?>
                        <div class='inline-label'>Add</div>
                    </a>
                </h2>
                
                <div class='items-list-sub-container'>
                    <div class="items-list main">
                        <?php if(count($items) > 0): ?>
                            <?php foreach($items as $item): ?>
                                <div class='item-container'>
                                    <div class='item-image-container image-popup-button'>
                                        <img class='item-image' src='/wishlist/images/item-images/<?php echo $wishlist_id; ?>/<?php echo htmlspecialchars($item['image']); ?>?t=<?php echo time(); ?>' alt='wishlist item image'>
                                    </div>
                                    <div class='item-description'>
                                        <div class='line'><h3><?php echo htmlspecialchars(substr($item['name'], 0, 25)) . (strlen($item['name']) > 25 ? '...' : ''); ?></h3></div>
                                        <div class='line'><h4>Price: $<?php echo htmlspecialchars($item['price']); ?></h4></div>
                                        <div class='line'><h4 class='notes-label'>Quantity Needed:</h4> <?php echo $item['unlimited'] == 'Yes' ? 'Unlimited' : htmlspecialchars($item['quantity']); ?></div>
                                        <div class='line'><h4 class='notes-label'>Notes: </h4><span><?php echo htmlspecialchars(substr($item['notes'], 0, 30)) . (strlen($item['notes']) > 30 ? '...' : ''); ?></span></div>
                                        <div class='line'><h4 class='notes-label'>Priority: </h4><span>(<?php echo $item['priority']; ?>)</span></div>
                                        <div class='icon-options item-options wisher-item-options'>
                                            <a class='icon-container' href='/wishlist/edit-item.php?id=<?php echo $item['id']; ?>&pageno=<?php echo $pageno; ?>'>
                                                <?php require(__DIR__ . '/../../images/site-images/icons/edit.php'); ?>
                                                <div class='inline-label'>Edit</div>
                                            </a>
                                            <a class='icon-container popup-button' href='#'>
                                                <?php require(__DIR__ . '/../../images/site-images/icons/delete-x.php'); ?>
                                                <div class='inline-label'>Delete</div>
                                            </a>
                                        </div>
                                        <p class='date-added center'><em>Date Added: <?php echo date("n/j/Y g:i A", strtotime($item['date_added'])); ?></em></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="center">No items in this wishlist yet. <a href="/wishlist/add-item.php">Add your first item!</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php include __DIR__ . '/../components/footer.php'; ?>
        </div>
    </div>
</body>
</html>
<script src="/wishlist/includes/popup.js"></script>
<script>$type = "wisher"; $key_url = "";</script>

