<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

require("includes/write-theme-popup.php");

// get wishlist id from SESSION/URL
$wishlistID = $_GET["id"] ?? false;
if(!$wishlistID) header("Location: index.php");
$_SESSION["wisher_wishlist_id"] = $wishlistID;

$pageno = $_GET["pageno"] ?? 1;

$_SESSION["home"] = "view-wishlist.php?id=$wishlistID&pageno=$pageno#paginate-top";
$_SESSION["type"] = "wisher";

$ajax = false;

// find wishlist year and type
$findWishlistInfo = $db->select("SELECT id, type, wishlist_name, year, duplicate, secret_key, theme_background_id, theme_gift_wrap_id FROM wishlists WHERE username = ? AND id = ?", [$username, $wishlistID]);
if($findWishlistInfo->num_rows > 0){
    while($row = $findWishlistInfo->fetch_assoc()){
        $year = $row["year"];
        $type = $row["type"];
        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
        $wishlist_name = $row["wishlist_name"];
        $wishlistTitle = htmlspecialchars($wishlist_name);
        $secret_key = $row["secret_key"];
        $theme_background_id = $row["theme_background_id"];
        $theme_gift_wrap_id = $row["theme_gift_wrap_id"];
        $findBackground = $db->select("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_background_id]);
        if($findBackground->num_rows > 0){
            while($bg_row = $findBackground->fetch_assoc()){
                $background_image = $bg_row["theme_image"];
                $_SESSION["wisher_background_image"] = $background_image;
            }
        }
    }
}else{
    header("Location: index.php");
}

$pageno = $_GET["pageno"] ?? 1;

// initialize filter variables
$valid_options = ["", "1", "2"];
$sort_priority = $_SESSION["sort_priority"] ?? "1";
$sort_price = $_SESSION["sort_price"] ?? "";
$_SESSION["sort_priority"] = $sort_priority;
$_SESSION["sort_price"] = $sort_price;
$wishlist_name_input = $wishlist_name;

// if filter is changed
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["theme_submit_button"])){
        $errors = false;
        $errorTitle = "<strong>The theme could not be changed due to the following errors:</strong>";
        $errorList = "";
    
        $theme_background_id = $_POST["theme_background_id"];
        $theme_gift_wrap_id = $_POST["theme_gift_wrap_id"];
        echo "$theme_background_id $theme_gift_wrap_id";
    
        if(!$errors){    
            // update theme for wishlist
            if($db->write("UPDATE wishlists SET theme_background_id = ?, theme_gift_wrap_id = ? WHERE username = ? AND id = ?", [$theme_background_id, $theme_gift_wrap_id, $username, $wishlistID])){
                //header("Location: view-wishlist.php?id=$wishlistID");
            }
        }else{
            $errorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
        }
    }
    
    if(isset($_POST["rename_submit_button"])){
        $errors = false;
        $errorTitle = "<strong>The wishlist could not be renamed due to the following errors:</strong>";
        $errorList = "";
    
        $wishlist_name_input = errorCheck("wishlist_name", "Name", "Yes", $errors, $errorList);
    
        if(!$errors){    
            // update theme for wishlist
            if($db->write("UPDATE wishlists SET wishlist_name = ? WHERE username = ? AND id = ?", [$wishlist_name_input, $username, $wishlistID])){
                header("Location: view-wishlist.php?id=$wishlistID");
            }
        }else{
            $errorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
        }
    }
    
    // if filter value is changed, change session value
    $sort_priority = $_POST["sort_priority"];
    $sort_price = $_POST["sort_price"];
    if(in_array($sort_priority, $valid_options)){
        $_SESSION["sort_priority"] = $sort_priority;
    }
    if(in_array($sort_price, $valid_options)){
        $_SESSION["sort_price"] = $sort_price;
    }
    header("Location: ?id=$wishlistID&pageno=$pageno#paginate-top");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title><?php echo $wishlistTitle; ?></title>
    <style>
        h1 {
            display: inline-block;
        }
        h2.items-list-title {
            position: relative;
        }
        .popup.fullscreen .gift-wrap-content .popup-content {
            max-height: calc(100% - 184px);
        }
        #container {
            background-image: url("images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>");
        }
        @media (max-width: 600px){
            #container {
                background-image: url("images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>");
            }
        }
        @media (max-width: 460px){
            .popup.fullscreen .gift-wrap-content .popup-content {
                max-height: calc(100% - 223px);
            }
        }
        @media (max-width: 284px){
            .popup.fullscreen .gift-wrap-content .popup-content {
                max-height: calc(100% - 254px);
            }
        }
    </style>
</head>
<body>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <input type="hidden" id="wishlist_type" value="<?php echo strtolower($type); ?>" />
        <div id="container">
            <p style="padding-top: 15px;"><a class="button accent" href="view-wishlists.php">Back to All Wish Lists</a></p>

            <div class="center">
                <h1 class="center transparent-background">
                    <?php echo $wishlistTitle; ?>
                    <div class="wishlist-options">
                        <a class="icon edit popup-button" href="#"><div class="inline-popup">Rename</div></a>
                        <div class='popup-container hidden'>
                            <div class='popup'>
                                <div class='close-container'>
                                    <img src='images/site-images/menu-close.png' class='close-button'>
                                </div>
                                <div class='popup-content'>
                                <h2 style="margin-top: 0;">Rename Wish List</h2>
                                <form method="POST" action="">
                                    <?php echo $errorMsg ?? ""; ?>
                                    <div class="flex form-flex">
                                        <div class="large-input">
                                            <label for="wishlist_name">Name:<br/></label>
                                            <input required type="text" id="wishlist_name" name="wishlist_name" value="<?php echo htmlspecialchars($wishlist_name_input); ?>" />
                                        </div>
                                        <div class="large-input">
                                            <p class="center"><input type="submit" name="rename_submit_button" id="submitButton" value="Rename" /></p>
                                        </div>
                                    </div>
                                </form>
                                </div>
                            </div>
                        </div>
                        <a class="icon swap popup-button choose-theme-button" href="#"><div class="inline-popup">Change Theme</div></a>
                        <?php
                        write_theme_popup(type: strtolower($type), swap: true);
                        ?>
                        <a class="icon copy-link" href="#"><div class="inline-popup">Copy Link to Wish List</div></a>
                        <a class="icon trashcan popup-button" href="#"><div class="inline-popup">Delete</div></a>
                        <div class='popup-container delete-wishlist-popup hidden'>
                            <div class='popup'>
                                <div class='close-container'>
                                    <img src='images/site-images/menu-close.png' class='close-button'>
                                </div>
                                <div class='popup-content'>
                                    <label>Are you sure you want to delete this wishlist?</label>
                                    <p><?php echo $wishlistTitle; ?></p>
                                    <p class='center'><a class='red_button no-button'>No</a><a class='green_button' href='delete-wishlist.php?id=<?php echo $wishlistID; ?>'>Yes</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </h1>
            </div>
            <?php
            require("includes/sort.php");
            $findItems = $db->select("SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ? ORDER BY $priority_order$price_order date_added DESC", [$wishlistID, $username]);
            echo "
            <div class='items-list-container'>
                <h2 class='transparent-background items-list-title' id='paginate-top'>All Items<a href='add-item.php' class='icon plus add-item'><div class='inline-popup'>Add Item</div></a></h2>";
                if($findItems->num_rows > 0){ ?>
                    <form class="filter-form" method="POST" action="">
                        <div class="filter-input">
                            <label for="sort-priority">Sort by Priority</label><br>
                            <select id="sort-priority" name="sort_priority">
                                <option value="">None</option>
                                <option value="1" <?php if($sort_priority == "1") echo "selected"; ?>>Highest to Lowest</option>
                                <option value="2" <?php if($sort_priority == "2") echo "selected"; ?>>Lowest to Highest</option>
                            </select>
                        </div>
                        <div class="filter-input">
                            <label for="sort-price">Sort by Price</label><br>
                            <select id="sort-price" name="sort_price">
                                <option value="">None</option>
                                <option value="1" <?php if($sort_price == "1") echo "selected"; ?>>Lowest to Highest</option>
                                <option value="2" <?php if($sort_price == "2") echo "selected"; ?>>Highest to Lowest</option>
                            </select>
                        </div>
                    </form>
                <?php }
                paginate(type: "wisher", db: $db, query: "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ? ORDER BY $priority_order$price_order date_added DESC", itemsPerPage: 12, pageNumber: $pageno, wishlist_id: $wishlistID, username: $username);
                ?>
            </div>
        <?php include "includes/footer.php"; ?>
        </div>
    </div>
</body>
</html>
<script src="includes/popup.js"></script>
<script src="includes/page-change.js"></script>
<script src="includes/choose-theme.js"></script>
<script>
    $(document).ready(function(){
        $(".icon.edit").on("click", function(){
            $("#wishlist_name")[0].select();
        });

        $("#wishlist_name").on("focus", function(){
            $(this).select();
        });
    });

    // submit form on filter change
    for(const sel of document.querySelectorAll("select")){
        sel.addEventListener("change", function(){
            document.querySelector("form").submit();
        });
    }

    // copy link to share
    document.querySelector(".copy-link").addEventListener("click", function(e){
        e.preventDefault();
        navigator.clipboard.writeText("https://cadelawless.com/wishlist/buyer-view.php?key=<?php echo $secret_key; ?>");
        this.querySelector(".inline-popup").textContent = "Copied!";
        setTimeout(() => {
            this.querySelector(".inline-popup").textContent = "Copy Link to Wish List";
        }, 1300);
    });
</script>