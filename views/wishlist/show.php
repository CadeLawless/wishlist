<?php
// Wishlist data
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

// Flash messages
$copy_from_success = isset($_SESSION['copy_from_success']) ? true : false;
if($copy_from_success) unset($_SESSION['copy_from_success']);

$wishlist_hidden = isset($_SESSION['wishlist_hidden']) ? true : false;
if($wishlist_hidden) unset($_SESSION['wishlist_hidden']);

$wishlist_public = isset($_SESSION['wishlist_public']) ? true : false;
if($wishlist_public) unset($_SESSION['wishlist_public']);

$wishlist_complete = isset($_SESSION['wishlist_complete']) ? true : false;
if($wishlist_complete) unset($_SESSION['wishlist_complete']);

$wishlist_reactivated = isset($_SESSION['wishlist_reactivated']) ? true : false;
if($wishlist_reactivated) unset($_SESSION['wishlist_reactivated']);

$item_deleted = isset($_SESSION['item_deleted']) ? true : false;
if($item_deleted) unset($_SESSION['item_deleted']);

// Initialize copy form variables
$other_wishlist_copy_from = "";
$copy_from_select_all = "Yes";
$other_wishlist_copy_to = "";
$copy_to_select_all = "Yes";
$other_wishlist_options = $other_wishlists;

// Initialize filter variables
$sort_priority = $_SESSION['wisher_sort_priority'] ?? "";
$sort_price = $_SESSION['wisher_sort_price'] ?? "";
$_SESSION['wisher_sort_priority'] = $sort_priority;
$_SESSION['wisher_sort_price'] = $sort_price;

$wishlist_name_input = $wishlist_name;

// Build SQL order clause based on filters
$priority_order = $sort_priority ? "priority ASC, " : "";
$price_order = $sort_price ? "price {$sort_price}, " : "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/wishlist/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/wishlist/css/styles.css" />
    <link rel="stylesheet" type="text/css" href="/wishlist/css/snow.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
<body class="<?php echo isset($_SESSION['dark']) && $_SESSION['dark'] ? 'dark' : ''; ?>" 
      data-current-page="<?php echo $pageno; ?>" 
      data-total-pages="<?php echo $total_pages; ?>" 
      data-base-url="/wishlist/<?php echo $wishlistID; ?>">
    <div id="body">
        <?php include __DIR__ . '/../components/header.php'; ?>
        <input type="hidden" id="wishlist_type" value="<?php echo strtolower($type); ?>" />
        <div id="container">
            <?php
            // Flash message popups
            if($copy_from_success){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require(__DIR__ . '/../../images/site-images/menu-close.php');
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p><label>Item(s) copied over successfully</label></p>
                        </div>
                    </div>
                </div>";
            }
            if($wishlist_hidden){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require(__DIR__ . '/../../images/site-images/menu-close.php');
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p><label>Wish list is now hidden</label></p>
                        </div>
                    </div>
                </div>";
            }
            if($wishlist_public){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require(__DIR__ . '/../../images/site-images/menu-close.php');
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p><label>Wish list is now public</label></p>
                        </div>
                    </div>
                </div>";
            }
            if($wishlist_complete){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require(__DIR__ . '/../../images/site-images/menu-close.php');
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p><label>Wish list successfully marked as complete</label></p>
                        </div>
                    </div>
                </div>";
            }
            if($wishlist_reactivated){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require(__DIR__ . '/../../images/site-images/menu-close.php');
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p><label>Wish list successfully reactivated</label></p>
                        </div>
                    </div>
                </div>";
            }
            if($item_deleted){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require(__DIR__ . '/../../images/site-images/menu-close.php');
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p><label>Item deleted successfully</label></p>
                        </div>
                    </div>
                </div>";
            }
            ?>

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
                    <div class='popup-container hidden'>
                        <div class='popup'>
                            <div class='close-container'>
                                <a href='#' class='close-button'>
                                <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                                </a>
                            </div>
                            <div class='popup-content'>
                                <div class="copy-link">
                                    <a class="button secondary" href="#" data-copy-text="https://cadelawless.com/wishlist/buyer-view.php?key=<?php echo $secret_key; ?>"><?php require(__DIR__ . '/../../images/site-images/icons/copy-link.php'); ?><span style="color: inherit;" class="copy-link-text">Copy Link to Wish List</span></a>
                                </div>
                                <div class="icon-options wishlist-options">
                                    <!-- Rename popup -->
                                    <a class="icon-container popup-button" href="#"><?php require(__DIR__ . '/../../images/site-images/icons/edit.php'); ?><div class="inline-label">Rename</div></a>
                                    <div class='popup-container first hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>
                                                <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                                                </a>
                                            </div>
                                            <div class='popup-content'>
                                            <h2 style="margin-top: 0;">Rename Wish List</h2>
                                            <form method="POST" action="/wishlist/<?php echo $wishlistID; ?>/rename">
                                                <div class="flex form-flex">
                                                    <div class="large-input">
                                                        <label for="wishlist_name">Name:<br/></label>
                                                        <input required type="text" id="wishlist_name" name="wishlist_name" value="<?php echo htmlspecialchars($wishlist_name_input); ?>" />
                                                    </div>
                                                    <div class="large-input">
                                                        <p class="center"><input type="submit" class="button text" name="rename_submit_button" id="submitButton" value="Rename" /></p>
                                                    </div>
                                                </div>
                                            </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Theme change popup -->
                                    <a class="icon-container popup-button choose-theme-button" href="#"><?php require(__DIR__ . '/../../images/site-images/icons/swap-theme.php'); ?><div class="inline-label">Change Theme</div></a>
                                    <div class='popup-container first hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>
                                                <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                                                </a>
                                            </div>
                                            <div class='popup-content'>
                                            <h2 style="margin-top: 0;">Change Theme</h2>
                                            <form method="POST" action="/wishlist/<?php echo $wishlistID; ?>/theme">
                                                <div class="flex form-flex">
                                                    <div class="large-input">
                                                        <label for="theme_background_id">Background:<br/></label>
                                                        <select id="theme_background_id" name="theme_background_id" required>
                                                            <option value="0">No Background</option>
                                                            <!-- Theme options would be populated here -->
                                                        </select>
                                                    </div>
                                                    <div class="large-input">
                                                        <label for="theme_gift_wrap_id">Gift Wrap:<br/></label>
                                                        <select id="theme_gift_wrap_id" name="theme_gift_wrap_id" required>
                                                            <option value="0">No Gift Wrap</option>
                                                            <!-- Theme options would be populated here -->
                                                        </select>
                                                    </div>
                                                    <div class="large-input">
                                                        <p class="center"><input type="submit" class="button text" name="theme_submit_button" id="submitButton" value="Update Theme" /></p>
                                                    </div>
                                                </div>
                                            </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <?php if(count($other_wishlist_options) > 0){ ?>
                                        <!-- Copy From popup -->
                                        <a class="icon-container popup-button" href="#"><?php require(__DIR__ . '/../../images/site-images/icons/copy-from.php'); ?><div class="inline-label">Copy From...</div></a>
                                        <div class='popup-container first center-items hidden'>
                                            <div class='popup'>
                                                <div class='close-container'>
                                                    <a href='#' class='close-button'>
                                                    <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                                                    </a>
                                                </div>
                                                <div class='popup-content'>
                                                    <h2>Copy Items From Another Wish List</h2>
                                                    <form method="POST" action="/wishlist/<?php echo $wishlistID; ?>/copy-from">
                                                        <label for="other_wishlist_copy_from">Choose Wish List:</label><br />
                                                        <select id="other_wishlist_copy_from" class="copy-select" name="other_wishlist_copy_from" data-base-url="/wishlist/<?php echo $wishlistID; ?>" required>
                                                            <option value="" disabled <?php if($other_wishlist_copy_from == "") echo "selected"; ?>>Select an option</option>
                                                            <?php
                                                            foreach($other_wishlist_options as $opt){
                                                                $other_id = $opt['id'];
                                                                $other_name = htmlspecialchars($opt['wishlist_name']);
                                                                echo "<option value='$other_id'";
                                                                if($other_id == $other_wishlist_copy_from) echo " selected";
                                                                echo ">$other_name</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                        <div class="other-items copy-from<?php if($other_wishlist_copy_from == "") echo " hidden"; ?>">
                                                            <label>Select Items:</label><br />
                                                            <div class="item-checkboxes">
                                                                <!-- Items will be loaded via AJAX -->
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                    if(count($items) > 0){ ?>
                                        <!-- Copy To popup -->
                                        <a class="icon-container popup-button" href="#"><?php require(__DIR__ . '/../../images/site-images/icons/copy-to.php'); ?><div class="inline-label">Copy To...</div></a>
                                        <div class='popup-container first center-items hidden'>
                                            <div class='popup'>
                                                <div class='close-container'>
                                                    <a href='#' class='close-button'>
                                                    <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                                                    </a>
                                                </div>
                                                <div class='popup-content'>
                                                    <h2>Copy Items to Another Wish List</h2>
                                                    <form method="POST" action="/wishlist/<?php echo $wishlistID; ?>/copy-to">
                                                        <label for="other_wishlist_copy_to">Choose Wish List:</label><br />
                                                        <select id="other_wishlist_copy_to" class="copy-select" name="other_wishlist_copy_to" data-base-url="/wishlist/<?php echo $wishlistID; ?>" required>
                                                            <option value="" disabled <?php if($other_wishlist_copy_to == "") echo "selected"; ?>>Select an option</option>
                                                            <?php
                                                            foreach($other_wishlist_options as $opt){
                                                                $other_id = $opt['id'];
                                                                $other_name = htmlspecialchars($opt['wishlist_name']);
                                                                echo "<option value='$other_id'";
                                                                if($other_id == $other_wishlist_copy_to) echo " selected";
                                                                echo ">$other_name</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                        <div class="other-items copy-to<?php if($other_wishlist_copy_to == "") echo " hidden"; ?>">
                                                            <label>Select Items:</label><br />
                                                            <div class="item-checkboxes">
                                                                <!-- Items will be loaded via AJAX -->
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                    if($complete == "No"){ ?>
                                        <!-- Hide/Show popup -->
                                        <a class="icon-container popup-button" href="#">
                                            <?php
                                            if($visibility == "Public"){
                                                require(__DIR__ . '/../../images/site-images/icons/hide-view.php');
                                            }else{
                                                require(__DIR__ . '/../../images/site-images/icons/view.php');
                                            }    
                                            ?>
                                            <div class="inline-label"><?php echo $visibility == "Public" ? "Hide" : "Make Public"; ?></div>
                                        </a>
                                        <div class='popup-container first hidden'>
                                            <div class='popup'>
                                                <div class='close-container'>
                                                    <a href='#' class='close-button'>
                                                        <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                                                    </a>
                                                </div>
                                                <div class='popup-content'>
                                                    <p>
                                                        <?php
                                                        if($visibility == "Public"){
                                                            echo "Making this wish list hidden means that the list will no longer be open for others to look at or mark items as purchased.";
                                                        }else{
                                                            echo "Making this wish list public means that the list will be open for others to look at and mark items as purchased.";
                                                        }
                                                        ?>        
                                                    </p>
                                                    <label>Are you sure you want to <?php echo $visibility == "Public" ? "hide this wish list" : "make this wish list public"; ?>?</label>
                                                    <p><?php echo $wishlistTitle; ?></p>
                                                    <p class='center'><a class='button secondary no-button'>No</a><a class='button primary' href='/wishlist/<?php echo $wishlistID; ?>/<?php echo $visibility == "Public" ? "hide" : "show"; ?>'>Yes</a></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    
                                    <!-- Complete/Reactivate popup -->
                                    <a class="icon-container popup-button" href="#"><?php require(__DIR__ . '/../../images/site-images/icons/checkmark.php'); ?><div class="inline-label"><?php echo $complete == "No" ? "Mark as Complete" : "Reactivate"; ?></div></a>
                                    <div class='popup-container first hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>
                                                <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                                                </a>
                                            </div>
                                            <div class='popup-content'>
                                                <p>
                                                    <?php
                                                    if($complete == "No"){
                                                        echo "Marking this wish list as complete means the event has passed and the list will no longer be open for others to look at or mark items as purchased.<br />";
                                                    }else{
                                                        echo "Reactivating this wish list means the list will now be open for others to look at and mark items as purchased again.<br />";
                                                    }
                                                    ?>
                                                </p>
                                                <label>Are you sure you want to <?php echo $complete == "No" ? "mark this wish list as complete" : "reactivate this wish list"; ?>?</label>
                                                <p><?php echo $wishlistTitle; ?></p>
                                                <p class='center'><a class='button secondary no-button'>No</a><a class='button primary' href='/wishlist/<?php echo $wishlistID; ?>/<?php echo $complete == "No" ? "complete" : "reactivate"; ?>'>Yes</a></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Delete popup -->
                                    <a class="icon-container popup-button" href="#"><?php require(__DIR__ . '/../../images/site-images/icons/delete-trashcan.php'); ?><div class="inline-label">Delete</div></a>
                                    <div class='popup-container first delete-wishlist-popup hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>
                                                <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                                                </a>
                                            </div>
                                            <div class='popup-content'>
                                                <label>Are you sure you want to delete this wish list?</label>
                                                <p><?php echo $wishlistTitle; ?></p>
                                                <div style="margin: 1rem 0;" class='center'>
                                                    <a class='button secondary no-button'>No</a>
                                                    <form method="POST" action="/wishlist/<?php echo $wishlistID; ?>" style="display: inline;">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class='button primary'>Yes</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                    <!-- Sort/Filter section -->
                    <div class="sort-filters">
                        <h3>Sort & Filter</h3>
                        <form class="filter-form center" method="POST" action="/wishlist/<?php echo $wishlistID; ?>/filter" id="filter-form">
                            <div class="filter-inputs">
                                <div class="filter-input">
                                    <label for="sort-priority">Sort by Priority</label><br>
                                    <select class="select-filter" id="sort-priority" name="sort_priority" data-base-url="/wishlist/<?php echo $wishlistID; ?>">
                                        <option value="">None</option>
                                        <option value="1" <?php echo $filters['sort_priority'] == "1" ? 'selected' : ''; ?>>Highest to Lowest</option>
                                        <option value="2" <?php echo $filters['sort_priority'] == "2" ? 'selected' : ''; ?>>Lowest to Highest</option>
                                    </select>
                                </div>
                                <div class="filter-input">
                                    <label for="sort-price">Sort by Price</label><br>
                                    <select class="select-filter" id="sort-price" name="sort_price" data-base-url="/wishlist/<?php echo $wishlistID; ?>">
                                        <option value="">None</option>
                                        <option value="1" <?php echo $filters['sort_price'] == "1" ? 'selected' : ''; ?>>Lowest to Highest</option>
                                        <option value="2" <?php echo $filters['sort_price'] == "2" ? 'selected' : ''; ?>>Highest to Lowest</option>
                                    </select>
                                </div>
                            </div>
                        </form>
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
                                    <?php 
                                    // Use the same item generation service as AJAX pagination
                                    foreach($items as $item): 
                                        echo \App\Services\ItemRenderService::renderItem($item, $wishlistID, $pageno);
                                    endforeach; 
                                    ?>
                                <?php else: ?>
                                    <a class='item-container add-placeholder' href='/wishlist/<?php echo $wishlist['id']; ?>/item/create'>
                                        <div class='item-image-container'>
                                            <img class='item-image' src='images/site-images/default-photo.png' alt='wishlist item image'>
                                        </div>
                                        <div class='item-description'></div>
                                        <div class='add-label'>
                                            <?php require(__DIR__ . '/../../images/site-images/icons/plus.php'); ?>
                                            Add Item
                                        </div>
                                    </a>
                                <?php endif; ?>
                    </div>
                    
                            <!-- Pagination controls -->
                            <?php if($total_pages > 1): ?>
                            <div class="center">
                                <div class="paginate-container">
                                    <a class="paginate-arrow paginate-first<?php echo $pageno <= 1 ? ' disabled' : ''; ?>" href="#">
                                        <?php require(__DIR__ . '/../../images/site-images/first.php'); ?>
                                    </a>
                                    <a class="paginate-arrow paginate-previous<?php echo $pageno <= 1 ? ' disabled' : ''; ?>" href="#">
                                        <?php require(__DIR__ . '/../../images/site-images/prev.php'); ?>
                                    </a>
                                    <div class="paginate-title">
                                        <span class="page-number"><?php echo $pageno; ?></span>/<span class="last-page"><?php echo $total_pages; ?></span>
                                    </div>
                                    <a class="paginate-arrow paginate-next<?php echo $pageno >= $total_pages ? ' disabled' : ''; ?>" href="#">
                                        <?php require(__DIR__ . '/../../images/site-images/prev.php'); ?>
                                    </a>
                                    <a class="paginate-arrow paginate-last<?php echo $pageno >= $total_pages ? ' disabled' : ''; ?>" href="#">
                                        <?php require(__DIR__ . '/../../images/site-images/first.php'); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="count-showing">Showing <?php echo (($pageno - 1) * 12) + 1; ?>-<?php echo min($pageno * 12, count($all_items)); ?> of <?php echo count($all_items); ?> items</div>
                </div>
            </div>

        </div>
    </div>
    
    <!-- Image Popup Container -->
    <div class='popup-container image-popup-container hidden'>
        <div class='popup image-popup'>
            <div class='close-container'>
                <a href='#' class='close-button'>
                    <?php require(__DIR__ . '/../../images/site-images/menu-close.php'); ?>
                </a>
            </div>
            <div class='popup-content'>
                <img class='popup-image' src='' alt='Full size item image'>
            </div>
        </div>
    </div>
</body>
</html>
<script src="public/js/copy-link.js"></script>
<script src="public/js/copy-select.js"></script>
<script src="public/js/checkbox-selection.js"></script>
<script src="public/js/wishlist-filters.js"></script>
<script src="public/js/wishlist-pagination.js"></script>
<script>$type = "wisher"; $key_url = "";</script>

