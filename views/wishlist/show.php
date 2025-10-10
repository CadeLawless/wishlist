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
<body class="<?php echo isset($_SESSION['dark']) && $_SESSION['dark'] ? 'dark' : ''; ?>">
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
                                    <a class="button secondary" href="#"><?php require(__DIR__ . '/../../images/site-images/icons/copy-link.php'); ?><span style="color: inherit;" class="copy-link-text">Copy Link to Wish List</span></a>
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
                                                        <select id="other_wishlist_copy_from" class="copy-select" name="other_wishlist_copy_from" required>
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
                                                        <select id="other_wishlist_copy_to" class="copy-select" name="other_wishlist_copy_to" required>
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
                                                <p class='center'><a class='button secondary no-button'>No</a><a class='button primary' href='/wishlist/<?php echo $wishlistID; ?>/delete'>Yes</a></p>
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
                                    <select class="select-filter" id="sort-priority" name="sort_priority">
                                        <option value="">None</option>
                                        <option value="1" <?php echo $filters['sort_priority'] == "1" ? 'selected' : ''; ?>>Highest to Lowest</option>
                                        <option value="2" <?php echo $filters['sort_priority'] == "2" ? 'selected' : ''; ?>>Lowest to Highest</option>
                                    </select>
                                </div>
                                <div class="filter-input">
                                    <label for="sort-price">Sort by Price</label><br>
                                    <select class="select-filter" id="sort-price" name="sort_price">
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
                                    <p class="center">No items in this wishlist yet. <a href="/wishlist/add-item.php">Add your first item!</a></p>
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
</body>
</html>
<script src="/wishlist/includes/popup.js"></script>
<script>$type = "wisher"; $key_url = "";</script>
<script src="/wishlist/includes/page-change.js"></script>
<script src="/wishlist/includes/choose-theme.js"></script>
<script src="/wishlist/includes/filter-change.js"></script>
<script>
        $(document).ready(function(){
            // Copy link functionality
            document.querySelector(".copy-link a").addEventListener("click", function(e){
                e.preventDefault();
                navigator.clipboard.writeText("https://cadelawless.com/wishlist/buyer-view.php?key=<?php echo $secret_key; ?>");
                this.querySelector("svg").classList.add("hidden");
                this.querySelector(".copy-link-text").textContent = "Copied!";
                setTimeout(() => {
                    this.querySelector("svg").classList.remove("hidden");
                    this.querySelector(".copy-link-text").textContent = "Copy Link to Wish List";
                }, 1300);
            });

            // Copy select functionality
            $(".copy-select").on("change", function(e) {
                $select = $(this);
                $id = $select.val();
                $copy_from = $select.attr("id") == "other_wishlist_copy_from" ? "Yes" : "No";

                $.ajax({
                    type: "POST",
                    url: "/wishlist/<?php echo $wishlistID; ?>/items",
                    data: {
                        wishlist_id: $id,
                        copy_from: $copy_from,
                    },
                    success: function(html) {
                        $select.next().removeClass("hidden");
                        $select.next().find(".item-checkboxes").html(html);
                    }
                });
            });

            // Checkbox selection functionality
            $(document.body).on("click", ".select-item-container", function(e){
                e.preventDefault();
                $checkbox = $(this).find("input")[0];
                $all_checkboxes =  $(this).parent().find(".option-checkbox > input:not(.check-all, .already-in-list)");
                if($checkbox.checked){
                    $checkbox.checked = false;
                    if($checkbox.classList.contains("check-all")){
                        $all_checkboxes.each(function(){
                            $(this)[0].checked = false;
                        });
                    }
                }else{
                    $checkbox.checked = true;
                    if($checkbox.classList.contains("check-all")){
                        $all_checkboxes.each(function(){
                            $(this)[0].checked = true;
                        });
                    }
                }
                $number_checked = 0;
                $all_checkboxes.each(function(){
                    if($(this)[0].checked) $number_checked++;
                });
                if($number_checked == $all_checkboxes.length){
                    $(this).parent().find(".check-all")[0].checked = true;
                }else{
                    $(this).parent().find(".check-all")[0].checked = false;
                }
            });

            // Filter form AJAX submission
            $("#filter-form").on("submit", function(e) {
                e.preventDefault();
                
                var formData = {
                    sort_priority: $("#sort-priority").val(),
                    sort_price: $("#sort-price").val()
                };
                
                $.ajax({
                    type: "POST",
                    url: "/wishlist/<?php echo $wishlistID; ?>/filter",
                    data: formData,
                    success: function(html) {
                        $(".items-list.main").html(html);
                        // Update URL without page refresh
                        var newUrl = "/wishlist/<?php echo $wishlistID; ?>?pageno=1#paginate-top";
                        history.pushState(null, null, newUrl);
                    },
                    error: function(xhr, status, error) {
                        console.error('Filter failed:', error);
                        alert('Filter failed. Please try again.');
                    }
                });
            });

            // Pagination AJAX functionality - use event delegation
            $(document).on("click", ".paginate-arrow", function(e) {
                e.preventDefault();
                
                console.log('Pagination arrow clicked:', $(this).attr('class'));
                
                if ($(this).hasClass("disabled")) {
                    console.log('Arrow is disabled, ignoring click');
                    return;
                }
                
                var currentPage = <?php echo $pageno; ?>;
                var totalPages = <?php echo $total_pages; ?>;
                var newPage = currentPage;
                
                console.log('Current page:', currentPage, 'Total pages:', totalPages);
                
                if ($(this).hasClass("paginate-first")) {
                    newPage = 1;
                } else if ($(this).hasClass("paginate-previous")) {
                    newPage = Math.max(1, currentPage - 1);
                } else if ($(this).hasClass("paginate-next")) {
                    newPage = Math.min(totalPages, currentPage + 1);
                } else if ($(this).hasClass("paginate-last")) {
                    newPage = totalPages;
                }
                
                if (newPage !== currentPage) {
                    $.ajax({
                        type: "POST",
                        url: "/wishlist/<?php echo $wishlistID; ?>/paginate",
                        data: { new_page: newPage },
                        dataType: "json",
                        success: function(data) {
                            // jQuery automatically parses JSON when dataType is "json"
                            
                            if (data.status === 'success') {
                                // Update items HTML
                                $(".items-list.main").html(data.html);
                                
                                // Update pagination controls
                                $('.page-number').text(data.current);
                                $('.last-page').text(data.total);
                                $('.count-showing').text(data.paginationInfo);
                                
                                // Update arrow states based on new page
                                var totalPages = parseInt(data.total);
                                
                                // First and Previous arrows
                                $('.paginate-first, .paginate-previous').each(function() {
                                    if (data.current <= 1) {
                                        $(this).addClass('disabled');
                                    } else {
                                        $(this).removeClass('disabled');
                                    }
                                });
                                
                                // Next and Last arrows
                                $('.paginate-next, .paginate-last').each(function() {
                                    if (data.current >= totalPages) {
                                        $(this).addClass('disabled');
                                    } else {
                                        $(this).removeClass('disabled');
                                    }
                                });
                                
                                // Update URL without page refresh
                                var newUrl = "/wishlist/<?php echo $wishlistID; ?>?pageno=" + data.current + "#paginate-top";
                                history.pushState(null, null, newUrl);
                                
                                // Update the currentPage variable for next pagination
                                currentPage = data.current;
                            } else {
                                console.error('Pagination error:', data.message);
                                alert('Pagination failed: ' + data.message);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Pagination failed:', error);
                            alert('Pagination failed. Please try again.');
                        }
                    });
                }
            });
        });
</script>

