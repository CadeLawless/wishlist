<?php
// Wishlist data
$wishlistID = $wishlist['id'];
$wishlist_name_input = $wishlist['wishlist_name'];
$wishlist_name = htmlspecialchars($wishlist_name_input);
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
$background_image = \App\Services\ThemeService::getBackgroundImage($theme_background_id) ?? '';

// All popup messages are now handled by PopupHelper

// Check for copy errors and preserve selected wishlists BEFORE handling flash messages
$copy_from_error = isset($_GET['copy_from_error']) && $_GET['copy_from_error'] == '1';
$copy_from_wishlist_id = null;
if ($copy_from_error && isset($_GET['copy_from_wishlist_id'])) {
    $copy_from_wishlist_id = (int)$_GET['copy_from_wishlist_id'];
}

$copy_to_error = isset($_GET['copy_to_error']) && $_GET['copy_to_error'] == '1';
$copy_to_wishlist_id = null;
if ($copy_to_error && isset($_GET['copy_to_wishlist_id'])) {
    $copy_to_wishlist_id = (int)$_GET['copy_to_wishlist_id'];
}

// Handle all popup messages using the helper, but skip general error popup for copy errors
\App\Helpers\PopupHelper::handleSessionPopups();
if (!$copy_from_error && !$copy_to_error) {
    \App\Helpers\PopupHelper::handleFlashMessages($flash);
} else {
    // Only show success messages, not errors (copy errors are shown in the popup)
    if (isset($flash['success'])) {
        \App\Helpers\PopupHelper::handleFlashMessages(['success' => $flash['success']]);
    }
}

// Initialize copy form variables
$other_wishlist_copy_from = "";
$copy_from_select_all = "Yes";
$other_wishlist_copy_to = "";
$copy_to_select_all = "Yes";
$other_wishlist_options = $other_wishlists;

// Preserve selected wishlist if there's a copy error
if ($copy_from_error && $copy_from_wishlist_id !== null) {
    $other_wishlist_copy_from = $copy_from_wishlist_id;
}
if ($copy_to_error && $copy_to_wishlist_id !== null) {
    $other_wishlist_copy_to = $copy_to_wishlist_id;
}

// Initialize filter variables
$sort_priority = $_SESSION['wisher_sort_priority'] ?? "";
$sort_price = $_SESSION['wisher_sort_price'] ?? "";
$_SESSION['wisher_sort_priority'] = $sort_priority;
$_SESSION['wisher_sort_price'] = $sort_price;

// Build SQL order clause based on filters
$priority_order = $sort_priority ? "priority ASC, " : "";
$price_order = $sort_price ? "price {$sort_price}, " : "";
?>
<input type="hidden" id="wishlist_type" value="<?php echo strtolower($type); ?>" />
            <?php
            // All popups are now handled by PopupHelper at the top of the file
            ?>

            <?php if($theme_background_id != 0 && $background_image){ ?>
                <img class='background-theme desktop-background' src="/public/images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
                <img class='background-theme mobile-background' src="/public/images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
            <?php } ?>
            <p>
                <a class="button accent" href="<?php echo isset($isAdminView) && $isAdminView ? '/admin/wishlists' : '/wishlists'; ?>">
                    Back to All Wish Lists
                </a>
            </p>

            <div class="center">
                <div class="wishlist-header center transparent-background">
                    <h1><?php echo $wishlistTitle; ?></h1>
                    <div class="flex-row">
                        <div><strong>Status:</strong> <?php echo $complete == "Yes" ? "Inactive" : "Active"; ?></div>
                        <div><strong>Visibility:</strong> <?php echo htmlspecialchars($visibility); ?></div>
                    </div>
                    <a class="button primary flex-button popup-button" href="#">
                        <?php require(__DIR__ . '/../../public/images/site-images/icons/settings.php'); ?>
                        <span>Wish List Options</span>
                    </a>
                    <div class='popup-container<?php echo ($copy_from_error || $copy_to_error ? '' : ' hidden'); ?>'>
                        <div class='popup'>
                            <div class='close-container'>
                                <a href='#' class='close-button'>
                                <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                </a>
                            </div>
                            <div class='popup-content'>
                                <div class="copy-link">
                                    <?php
                                    // Build the full buyer URL
                                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
                                    $host = $_SERVER['HTTP_HOST'];
                                    $buyerUrl = $protocol . $host . '/buyer/' . $secret_key;
                                    ?>
                                    <a class="button secondary" href="#" data-copy-text="<?php echo $buyerUrl; ?>"><?php require(__DIR__ . '/../../public/images/site-images/icons/copy-link.php'); ?><span style="color: inherit;" class="copy-link-text">Copy Link to Wish List</span></a>
                                </div>
                                <div class="icon-options wishlist-options">
                                    <!-- Rename popup -->
                                    <a class="icon-container popup-button" href="#"><?php require(__DIR__ . '/../../public/images/site-images/icons/edit.php'); ?><div class="inline-label">Rename</div></a>
                                    <div class='popup-container first hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>
                                                <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                                </a>
                                            </div>
                                            <div class='popup-content'>
                                            <h2 style="margin-top: 0;">Rename Wish List</h2>
                                            <?php if (isset($flash['rename_error'])): ?>
                                                <div class="validation-error" style="display: block; margin-bottom: 15px;">
                                                    <span class="error-item" style="color: #e74c3c;"><?php echo htmlspecialchars($flash['rename_error']); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <form method="POST" action="/wishlists/<?php echo $wishlistID; ?>/rename">
                                                <div class="flex form-flex">
                                                    <div class="large-input">
                                                        <label for="wishlist_name">Name:<br/></label>
                                                        <input required maxlength="100" type="text" id="wishlist_name" name="wishlist_name" value="<?php echo htmlspecialchars($wishlist_name_input); ?>" />
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
                                    <a class="icon-container popup-button choose-theme-button" href="#"><?php require(__DIR__ . '/../../public/images/site-images/icons/swap-theme.php'); ?><div class="inline-label">Change Theme</div></a>
                                    <?php echo \App\Helpers\ThemePopupHelper::renderThemePopup(strtolower($type), true); ?>
                                    
                                    <?php if(count($other_wishlist_options) > 0){ ?>
                                        <!-- Copy From popup -->
                                        <a class="icon-container popup-button" href="#"><?php require(__DIR__ . '/../../public/images/site-images/icons/copy-from.php'); ?><div class="inline-label">Copy From...</div></a>
                                        <div class='popup-container first center-items<?php echo ($copy_from_error ? '' : ' hidden'); ?>'>
                                            <div class='popup'>
                                                <div class='close-container'>
                                                    <a href='#' class='close-button'>
                                                    <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                                    </a>
                                                </div>
                                                <div class='popup-content'>
                                                    <h2>Copy Items From Another Wish List</h2>
                                                    <?php if ($copy_from_error && isset($flash['error'])): ?>
                                                        <div class="validation-error" style="display: block; margin-bottom: 15px;">
                                                            <span class="error-item" style="color: #e74c3c;"><?php echo htmlspecialchars($flash['error']); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <form method="POST" action="/wishlists/<?php echo $wishlistID; ?>/copy-from">
                                                        <label for="other_wishlist_copy_from">Choose Wish List:</label><br />
                                                        <select id="other_wishlist_copy_from" class="copy-select" name="other_wishlist_copy_from" data-base-url="<?php echo isset($isAdminView) && $isAdminView ? "/admin/wishlists/view?id={$wishlistID}" : "/wishlists/{$wishlistID}"; ?>" required>
                                                            <option value="" disabled <?php if($other_wishlist_copy_from == "") echo "selected"; ?>>Select an option</option>
                                                            <?php
                                                            foreach($other_wishlist_options as $opt){
                                                                $other_id = $opt['id'];
                                                                $other_name = htmlspecialchars($opt['wishlist_name']);
                                                                $item_count = isset($opt['item_count']) ? (int)$opt['item_count'] : 0;
                                                                $item_count_text = $item_count === 1 ? "1 item" : "$item_count items";
                                                                echo "<option value='$other_id'";
                                                                if($item_count === 0) {
                                                                    echo " disabled";
                                                                } else if($other_id == $other_wishlist_copy_from) {
                                                                    echo " selected";
                                                                }
                                                                echo ">$other_name ($item_count_text)</option>";
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
                                        <a class="icon-container popup-button" href="#"><?php require(__DIR__ . '/../../public/images/site-images/icons/copy-to.php'); ?><div class="inline-label">Copy To...</div></a>
                                        <div class='popup-container first center-items<?php echo ($copy_to_error ? '' : ' hidden'); ?>'>
                                            <div class='popup'>
                                                <div class='close-container'>
                                                    <a href='#' class='close-button'>
                                                    <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                                    </a>
                                                </div>
                                                <div class='popup-content'>
                                                    <h2>Copy Items to Another Wish List</h2>
                                                    <?php if ($copy_to_error && isset($flash['error'])): ?>
                                                        <div class="validation-error" style="display: block; margin-bottom: 15px;">
                                                            <span class="error-item" style="color: #e74c3c;"><?php echo htmlspecialchars($flash['error']); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <form method="POST" action="/wishlists/<?php echo $wishlistID; ?>/copy-to">
                                                        <label for="other_wishlist_copy_to">Choose Wish List:</label><br />
                                                        <select id="other_wishlist_copy_to" class="copy-select" name="other_wishlist_copy_to" data-base-url="<?php echo isset($isAdminView) && $isAdminView ? "/admin/wishlists/view?id={$wishlistID}" : "/wishlists/{$wishlistID}"; ?>" required>
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
                                                require(__DIR__ . '/../../public/images/site-images/icons/hide-view.php');
                                            }else{
                                                require(__DIR__ . '/../../public/images/site-images/icons/view.php');
                                            }    
                                            ?>
                                            <div class="inline-label"><?php echo $visibility == "Public" ? "Hide" : "Make Public"; ?></div>
                                        </a>
                                        <div class='popup-container first hidden'>
                                            <div class='popup'>
                                                <div class='close-container'>
                                                    <a href='#' class='close-button'>
                                                        <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
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
                                                    <div style="margin: 1rem 0;" class='center'>
                                                        <a class='button secondary no-button'>No</a>
                                                        <form method="POST" action="/wishlists/<?php echo $wishlistID; ?>/<?php echo $visibility == "Public" ? "hide" : "show"; ?>" style="display: inline;">
                                                            <button type="submit" class='button primary'>Yes</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    
                                    <!-- Complete/Reactivate popup -->
                                    <a class="icon-container popup-button" href="#"><?php require(__DIR__ . '/../../public/images/site-images/icons/' . ($complete == "No" ? "cancel" : "checkmark") . '.php'); ?><div class="inline-label"><?php echo $complete == "No" ? "Deactivate" : "Reactivate"; ?></div></a>
                                    <div class='popup-container first hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>
                                                <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                                </a>
                                            </div>
                                            <div class='popup-content'>
                                                <p>
                                                    <?php
                                                    if($complete == "No"){
                                                        echo "Deactivating this wish list as complete means you are done with this list and it will no longer be open for others to look at or mark items as purchased.<br />";
                                                    }else{
                                                        echo "Reactivating this wish list means the list will now be open for others to look at and mark items as purchased again.<br />";
                                                    }
                                                    ?>
                                                </p>
                                                <label>Are you sure you want to <?php echo $complete == "No" ? "deactivate" : "reactivate"; ?> this wish list?</label>
                                                <p><?php echo $wishlistTitle; ?></p>
                                                <div style="margin: 1rem 0;" class='center'>
                                                    <a class='button secondary no-button'>No</a>
                                                    <form method="POST" action="/wishlists/<?php echo $wishlistID; ?>/<?php echo $complete == "No" ? "complete" : "reactivate"; ?>" style="display: inline;">
                                                        <button type="submit" class='button primary'>Yes</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Delete popup -->
                                    <a class="icon-container popup-button" href="#"><?php require(__DIR__ . '/../../public/images/site-images/icons/delete-trashcan.php'); ?><div class="inline-label">Delete</div></a>
                                    <div class='popup-container first delete-wishlist-popup hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>
                                                <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                                                </a>
                                            </div>
                                            <div class='popup-content'>
                                                <label>Are you sure you want to delete this wish list?</label>
                                                <p><?php echo $wishlistTitle; ?></p>
                                                <div style="margin: 1rem 0;" class='center'>
                                                    <a class='button secondary no-button'>No</a>
                                                    <form method="POST" action="/wishlists/<?php echo $wishlistID; ?>" style="display: inline;">
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
                                                    
            <?php if(isset($wishlist_total_price)): ?>
                <div class="center">
                    <div class="wishlist-total">
                        <strong>Total Price: </strong><span>$<?= htmlspecialchars($wishlist_total_price); ?></span>
                    </div>
                </div>
            <?php endif; ?>

                    <!-- Sort/Filter section -->
                    <?php if(count($items) > 0): ?>
                    <div class="sort-filters">
                        <?php 
                        $baseUrl = isset($isAdminView) && $isAdminView ? "/admin/wishlists/view?id={$wishlistID}" : "/wishlists/{$wishlistID}";
                        $options = [
                            'form_action' => "/wishlists/{$wishlistID}/filter",
                            'form_class' => 'filter-form center',
                            'sort_priority' => $filters['sort_priority'] ?? '',
                            'sort_price' => $filters['sort_price'] ?? '',
                            'data_attributes' => 'data-base-url="' . $baseUrl . '"'
                        ];
                        include __DIR__ . '/../components/sort-filter-form.php';
                        ?>
                    </div>
                    <?php endif; ?>

            <div class='items-list-container'>
                <h2 class='transparent-background items-list-title' id='paginate-top'>
                    All Items
                    <a href='/wishlists/<?php echo $wishlistID; ?>/item/add' class='icon-container add-item'>
                        <?php require(__DIR__ . '/../../public/images/site-images/icons/plus.php'); ?>
                        <div class='inline-label'>Add</div>
                    </a>
                </h2>
                
                <!-- Search input for items -->
                <?php if(count($items) > 0 || !empty($searchTerm ?? '')): ?>
                <?php
                $options = [
                    'placeholder' => 'Search items by name, price, link, or notes...',
                    'input_id' => 'items-search',
                    'input_class' => 'items-search-input',
                    'container_class' => 'center',
                    'search_term' => $searchTerm ?? ''
                ];
                include __DIR__ . '/../components/admin-table-search.php';
                ?>
                <?php endif; ?>
                
                <!-- Container for pagination and items (loading overlay covers this area) -->
                <div class='items-content-container'>
                    <!-- Top Pagination controls -->
                    <?php 
                    $position = 'top';
                    $total_count = count($all_items);
                    include __DIR__ . '/../components/pagination-controls.php'; 
                    ?>
                    
                    <div class='items-list-sub-container'>
                    <div class="items-list main">
                                <?php if(count($items) > 0): ?>
                                    <?php 
                                    // Use the same item generation service as AJAX pagination
                                    foreach($items as $item): 
                                        echo \App\Services\ItemRenderService::renderItem($item, $wishlistID, $pageno, 'wisher', $searchTerm ?? '');
                                    endforeach; 
                                    ?>
                                <?php else: ?>
                                    <a class='item-container add-placeholder' href='/wishlists/<?php echo $wishlist['id']; ?>/item/add'>
                                        <div class='item-image-container'>
                                            <img class='item-image' src='/public/images/site-images/default-photo.png' alt='wishlist item image'>
                                        </div>
                                        <div class='item-description'></div>
                                        <div class='add-label'>
                                            <?php require(__DIR__ . '/../../public/images/site-images/icons/plus.php'); ?>
                                            Add Item
                                        </div>
                                    </a>
                                <?php endif; ?>
                    </div>
                    
                            <!-- Pagination controls -->
                            <?php 
                            $position = 'bottom';
                            $total_count = count($all_items);
                            $item_label = 'items';
                            include __DIR__ . '/../components/pagination-controls.php'; 
                            ?>
                    </div>
                </div>
            </div>

        </div>
    
    <!-- Image Popup Container -->
    <div class='popup-container image-popup-container hidden'>
        <div class='popup image-popup'>
            <div class='close-container'>
                <a href='#' class='close-button'>
                    <?php require(__DIR__ . '/../../public/images/site-images/menu-close.php'); ?>
                </a>
            </div>
            <div class='popup-content'>
                <img class='popup-image' src='' alt='Full size item image'>
            </div>
        </div>
    </div>

<!-- Wishlist-specific scripts -->
<script src="/public/js/copy-link.js?v=2.1"></script>
<script src="/public/js/copy-select.js?v=2.1"></script>
<script src="/public/js/checkbox-selection.js?v=2.1"></script>
<script src="/public/js/wishlist-filters.js?v=2.1"></script>
<script src="/public/js/pagination.js?v=2.1"></script>
<script src="/public/js/admin-table-search.js?v=2.1"></script>
<script src="/public/js/choose-theme.js?v=2.1"></script>
<script src="/public/js/popup.js?v=2.1"></script>
<script src="/public/js/form-validation.js?v=2.1"></script>
<script>$type = "wisher"; $key_url = "";</script>
<script>
$(document).ready(function() {
    // Initialize form validation for rename form
    const renameForm = $('form[action*="/rename"]');
    if (renameForm.length) {
        FormValidator.init('form[action*="/rename"]', {
            wishlist_name: {
                required: true,
                minLength: 1,
                maxLength: 100
            }
        });
    }
    
    // Auto-open popups if there's a rename error
    <?php if (isset($flash['rename_error'])): ?>
    // Open settings popup first, then rename popup
    const settingsButton = $('.button.primary.flex-button.popup-button');
    if (settingsButton.length) {
        // Click settings button to open settings popup
        settingsButton.trigger('click');
        
        // Wait for settings popup to open, then open rename popup
        setTimeout(function() {
            // Find the rename button within the wishlist-options section
            const renameButton = $('.wishlist-options .icon-container.popup-button').first();
            if (renameButton.length) {
                renameButton.trigger('click');
            }
        }, 150);
    }
    <?php endif; ?>
    
    // Reusable function to load items when copy error occurs and wishlist is preselected
    function loadItemsForCopyError(selectId, hasError) {
        if (hasError) {
            setTimeout(function() {
                const select = $(selectId);
                if (select.length && select.val()) {
                    select.trigger('change');
                }
            }, 100);
        }
    }
    
    // Load items for copy-from if error and wishlist was selected
    loadItemsForCopyError('#other_wishlist_copy_from', <?php echo $copy_from_error && $other_wishlist_copy_from != "" ? 'true' : 'false'; ?>);
    
    // Load items for copy-to if error and wishlist was selected
    loadItemsForCopyError('#other_wishlist_copy_to', <?php echo $copy_to_error && $other_wishlist_copy_to != "" ? 'true' : 'false'; ?>);
});
</script>

