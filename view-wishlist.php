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

$copy_from_success = isset($_SESSION["copy_from_success"]) ? true : false;
if($copy_from_success) unset($_SESSION["copy_from_success"]);

$wishlist_hidden = isset($_SESSION["wishlist_hidden"]) ? true : false;
if($wishlist_hidden) unset($_SESSION["wishlist_hidden"]);

$wishlist_public = isset($_SESSION["wishlist_public"]) ? true : false;
if($wishlist_public) unset($_SESSION["wishlist_public"]);

$wishlist_complete = isset($_SESSION["wishlist_complete"]) ? true : false;
if($wishlist_complete) unset($_SESSION["wishlist_complete"]);

$wishlist_reactivated = isset($_SESSION["wishlist_reactivated"]) ? true : false;
if($wishlist_reactivated) unset($_SESSION["wishlist_reactivated"]);

$item_deleted = isset($_SESSION["item_deleted"]) ? true : false;
if($item_deleted) unset($_SESSION["item_deleted"]);

// find wishlist year and type
$findWishlistInfo = $db->select("SELECT id, type, wishlist_name, year, duplicate, secret_key, theme_background_id, theme_gift_wrap_id, visibility, complete FROM wishlists WHERE username = ? AND id = ?", [$username, $wishlistID]);
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
        if($theme_background_id != 0){
            $findBackground = $db->select("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_background_id]);
            if($findBackground->num_rows > 0){
                while($bg_row = $findBackground->fetch_assoc()){
                    $background_image = $bg_row["theme_image"];
                    $_SESSION["wisher_background_image"] = $background_image;
                }
            }
        }
        $visibility = $row["visibility"];
        $complete = $row["complete"];
    }
}else{
    header("Location: no-access.php");
}

$pageno = $_GET["pageno"] ?? 1;

// initialize copy to other wish list form field variables
$other_wishlist_copy_from = "";
$copy_from_select_all = "Yes";
$other_wishlist_copy_to = "";
$copy_to_select_all = "Yes";
$other_wishlist_options = [];
$findOtherWishLists = $db->select("SELECT wishlist_name, id FROM wishlists WHERE username = ? AND id <> ?", [$username, $wishlistID]);
if($findOtherWishLists->num_rows > 0){
    while($row = $findOtherWishLists->fetch_assoc()){
        $other_id = $row["id"];
        $other_name = $row["wishlist_name"];
        $other_array = ["id" => $other_id, "name" => $other_name];
        if(!in_array($other_array, $other_wishlist_options)) array_push($other_wishlist_options, $other_array);
    }
}
$findItems = $db->select("SELECT * FROM items WHERE wishlist_id = ?", [$wishlistID]);

// initialize filter variables
require("includes/filter-options.php");
$sort_priority = $_SESSION["wisher_sort_priority"] ?? "";
$sort_price = $_SESSION["wisher_sort_price"] ?? "";
$_SESSION["wisher_sort_priority"] = $sort_priority;
$_SESSION["wisher_sort_price"] = $sort_price;

$wishlist_name_input = $wishlist_name;

// if filter is changed
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(isset($_POST["theme_submit_button"])){
        $errors = false;
        $errorTitle = "<strong>The theme could not be changed due to the following errors:</strong>";
        $errorList = "";
    
        $theme_background_id = $_POST["theme_background_id"];
        $theme_gift_wrap_id = $_POST["theme_gift_wrap_id"];
    
        if(!$errors){    
            // update theme for wishlist
            if($db->write("UPDATE wishlists SET theme_background_id = ?, theme_gift_wrap_id = ? WHERE username = ? AND id = ?", [$theme_background_id, $theme_gift_wrap_id, $username, $wishlistID])){
                header("Location: view-wishlist.php?id=$wishlistID");
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

    if(isset($_POST["copy_from_submit"])){
        $other_wishlist_copy_from = errorCheck(input: "other_wishlist_copy_from", inputName: "Choose a Wish List", required: "Yes", errors: $errors, error_list: $errorList);
        validOptionCheck(input: $other_wishlist_copy_from, inputName: "Choose a Wish List", validArray: $other_wishlist_options, errors: $errors, error_list: $errorList, multidimensional: true, key: "id");
        $copy_from_select_all = isset($_POST["copy_from_select_all"]) ? "Yes" : "No";

        if(!$errors){
            $findWishListItems = $db->select("SELECT * FROM items WHERE wishlist_id = ?", [$other_wishlist_copy_from]);
            if($findWishListItems->num_rows > 0){
                $items_to_copy = [];
                while($row = $findWishListItems->fetch_assoc()){
                    $item_id = $row["id"];
                    $item_copy_id = $row["copy_id"];
                    if(isset($_POST["item_$item_id"])){
                        if($copy_id != ""){
                            $findCopyInCurrentList = $db->select("SELECT copy_id FROM items WHERE copy_id = ? AND wishlist_id = ?", [$item_copy_id, $wishlistID]);
                            $alreadyInList = $findCopyInCurrentList->num_rows > 0;
                        }else{
                            $alreadyInList = false;
                        }
                        if(!$alreadyInList){
                            if(!in_array($row, $items_to_copy)) array_push($items_to_copy, $row);
                            ${"copy_from_item_$item_id"} = "Yes";
                        }
                    }
                }
                if(count($items_to_copy) > 0){
                    foreach($items_to_copy as $item){
                        $item_id = $item["id"];
                        if($db->write("UPDATE items SET copy_id = ? WHERE id = ?", [$item_id, $item_id])){
                            $original_item_image = $item["image"];
                            $item_image = $original_item_image;
                            if(!is_dir("images/item-images/$wishlistID")){
                                mkdir("images/item-images/$wishlistID/");
                            }
                            if(file_exists("images/item-images/$wishlistID/$item_image")){
                                $file_exists = true;
                                $i = 1;
                                while($file_exists){
                                    $file_array = explode(".", $item_image);
                                    $image_name = $file_array[0];
                                    $image_ext = $file_array[1];
                                    $new_item_image = $image_name . $i;
                                    if(!file_exists("images/item-images/$wishlistID/$new_item_image.$image_ext")){
                                        $file_exists = false;
                                        $item_image = "$new_item_image.$image_ext";
                                    }else{
                                        $i++;
                                    }
                                }
                            }
                            $from_file = "images/item-images/$other_wishlist_copy_from/$original_item_image";
                            $to_file = "images/item-images/$wishlistID/$item_image";
                            $file_errors = false;
                            if(!copy($from_file, $to_file)){
                                $errors = true;
                                $file_errors = true;
                                $file_error = error_get_last();
                                $error_type = $file_error["type"];
                                $error_message = $file_error["message"];
                                $errorList .= "<li>New item image ($item_image) file upload failed.<ul><li>Error Type: $error_type</li><li>Error Message: $error_message</li></ul></li>";
                            }
                            $date_added = date("Y-m-d H:i:s");
                            if(!$file_errors){
                                if(!$db->write("INSERT INTO items (wishlist_id, copy_id, name, notes, price, quantity, unlimited, link, image, priority, quantity_purchased, purchased, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [$wishlistID, $item_id, $item["name"], $item["notes"], $item["price"], $item["quantity"], $item["unlimited"], $item["link"], $item_image, $item["priority"], $item["quantity_purchased"], $item["purchased"], $date_added])){
                                    echo "<script>alert('Something went wrong while trying to copy item(s) over');</script>";
                                }
                            }
                        }else{
                            echo "<script>alert('Something went wrong while trying to copy item(s) over');</script>";
                        }
                    }
                }else{
                    $errors = true;
                    $errorList .= "<li>Please select at least one item to copy</li>";
                }
            }else{
                $errors = true;
                $errorList .= "<li>The selected wish list does not have any items on it yet. Please select another.</li>";
            }
            if(!$errors){
                $_SESSION["copy_from_success"] = true;
                header("Location: {$_SESSION["home"]}");
            }else{
                $copyFromErrorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
            }
        }else{
            $copyFromErrorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
        }
    }
    if(isset($_POST["copy_to_submit"])){
        $other_wishlist_copy_to = errorCheck(input: "other_wishlist_copy_to", inputName: "Choose a Wish List", required: "Yes", errors: $errors, error_list: $errorList);
        validOptionCheck(input: $other_wishlist_copy_to, inputName: "Choose a Wish List", validArray: $other_wishlist_options, errors: $errors, error_list: $errorList, multidimensional: true, key: "id");
        $copy_to_select_all = isset($_POST["copy_to_select_all"]) ? "Yes" : "No";

        if(!$errors){
            if($findItems->num_rows > 0){
                $items_to_copy = [];
                while($row = $findItems->fetch_assoc()){
                    $item_id = $row["id"];
                    $item_copy_id = $row["copy_id"];
                    if(isset($_POST["item_$item_id"])){
                        if($item_copy_id != ""){
                            $findCopyInOtherList = $db->select("SELECT copy_id FROM items WHERE copy_id = ? AND wishlist_id = ?", [$item_copy_id, $other_wishlist_copy_to]);
                            $alreadyInList = $findCopyInOtherList->num_rows > 0;
                        }else{
                            $alreadyInList = false;
                        }
                        if(!$alreadyInList){
                            if(!in_array($row, $items_to_copy)) array_push($items_to_copy, $row);
                            ${"copy_to_item_$item_id"} = "Yes";
                        }
                    }
                }
                if(count($items_to_copy) > 0){
                    foreach($items_to_copy as $item){
                        $item_id = $item["id"];
                        if($db->write("UPDATE items SET copy_id = ? WHERE id = ?", [$item_id, $item_id])){
                            $original_item_image = $item["image"];
                            $item_image = $original_item_image;
                            if(!is_dir("images/item-images/$other_wishlist_copy_to")){
                                mkdir("images/item-images/$other_wishlist_copy_to/");
                            }
                            if(file_exists("images/item-images/$other_wishlist_copy_to/$item_image")){
                                $file_exists = true;
                                $i = 1;
                                while($file_exists){
                                    $file_array = explode(".", $item_image);
                                    $image_name = $file_array[0];
                                    $image_ext = $file_array[1];
                                    $new_item_image = $image_name . $i;
                                    if(!file_exists("images/item-images/$other_wishlist_copy_to/$new_item_image.$image_ext")){
                                        $file_exists = false;
                                        $item_image = "$new_item_image.$image_ext";
                                    }else{
                                        $i++;
                                    }
                                }
                            }
                            $from_file = "images/item-images/$wishlistID/$original_item_image";
                            $to_file = "images/item-images/$other_wishlist_copy_to/$item_image";
                            $file_errors = false;
                            if(!copy($from_file, $to_file)){
                                $errors = true;
                                $file_errors = true;
                                $file_error = error_get_last();
                                $error_type = $file_error["type"];
                                $error_message = $file_error["message"];
                                $errorList .= "<li>New item image ($item_image) file upload failed.<ul><li>Error Type: $error_type</li><li>Error Message: $error_message</li></ul></li>";
                            }
                            $date_added = date("Y-m-d H:i:s");
                            if(!$file_errors){
                                if(!$db->write("INSERT INTO items (wishlist_id, copy_id, name, notes, price, quantity, unlimited, link, image, priority, quantity_purchased, purchased, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [$other_wishlist_copy_to, $item_id, $item["name"], $item["notes"], $item["price"], $item["quantity"], $item["unlimited"], $item["link"], $item_image, $item["priority"], $item["quantity_purchased"], $item["purchased"], $date_added])){
                                    echo "<script>alert('Something went wrong while trying to copy item(s) over');</script>";
                                }
                            }
                        }else{
                            echo "<script>alert('Something went wrong while trying to copy item(s) over');</script>";
                        }
                    }
                }else{
                    $errors = true;
                    $errorList .= "<li>Please select at least one item to copy</li>";
                }
            }else{
                $errors = true;
                $errorList .= "<li>The current wish list does not have any items on it yet. Please add items if you want to copy.</li>";
            }
            if(!$errors){
                $_SESSION["copy_from_success"] = true;
                header("Location: {$_SESSION["home"]}");
            }else{
                $copyFromErrorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
            }
        }else{
            $copyFromErrorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
        }
    }
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
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <input type="hidden" id="wishlist_type" value="<?php echo strtolower($type); ?>" />
        <div id="container">
            <?php
            if($copy_from_success){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require("images/site-images/menu-close.php");
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
                            require("images/site-images/menu-close.php");
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
                            require("images/site-images/menu-close.php");
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
                            require("images/site-images/menu-close.php");
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
                            require("images/site-images/menu-close.php");
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
                            require("images/site-images/menu-close.php");
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p><label>Item deleted successfully</label></p>
                        </div>
                    </div>
                </div>";
            }
            ?>

            <?php if($theme_background_id != 0){ ?>
                <img class='background-theme desktop-background' src="images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
                <img class='background-theme mobile-background' src="images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
            <?php } ?>
            <p style="padding-top: 15px;"><a class="button accent" href="view-wishlists.php">Back to All Wish Lists</a></p>

            <div class="center">
                <div class="wishlist-header center transparent-background">
                    <h1><?php echo $wishlistTitle; ?></h1>
                    <div class="flex-row">
                        <div><strong>Status:</strong> <?php echo $complete == "Yes" ? "Complete" : "Not Complete"; ?></div>
                        <div><strong>Visibility:</strong> <?php echo htmlspecialchars($visibility); ?></div>
                    </div>
                    <a class="button primary flex-button popup-button" href="#">
                        <?php require("images/site-images/icons/settings.php"); ?>
                        <span>Wish List Options</span>
                    </a>
                    <div class='popup-container hidden'>
                        <div class='popup'>
                            <div class='close-container'>
                                <a href='#' class='close-button'>
                                <?php require("images/site-images/menu-close.php"); ?>
                                </a>
                            </div>
                            <div class='popup-content'>
                                <div class="copy-link">
                                    <a class="button secondary" href="#"><?php require("images/site-images/icons/copy-link.php"); ?><span style="color: inherit;" class="copy-link-text">Copy Link to Wish List</span></a>
                                </div>
                                <div class="icon-options wishlist-options">
                                    <a class="icon-container popup-button" href="#"><?php require("images/site-images/icons/edit.php"); ?><div class="inline-label">Rename</div></a>
                                    <div class='popup-container first hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>
                                                <?php require("images/site-images/menu-close.php"); ?>
                                                </a>
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
                                                        <p class="center"><input type="submit" class="button text" name="rename_submit_button" id="submitButton" value="Rename" /></p>
                                                    </div>
                                                </div>
                                            </form>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="icon-container popup-button choose-theme-button" href="#"><?php require("images/site-images/icons/swap-theme.php"); ?><div class="inline-label">Change Theme</div></a>
                                    <?php
                                    write_theme_popup(type: strtolower($type), swap: true);
                                    if(count($other_wishlist_options) > 0){ ?>
                                        <a class="icon-container popup-button" href="#"><?php require("images/site-images/icons/copy-from.php"); ?><div class="inline-label">Copy From...</div></a>
                                        <div class='popup-container first center-items<?php if(!isset($copyFromErrorMsg)) echo " hidden"; ?>'>
                                            <div class='popup'>
                                                <div class='close-container'>
                                                    <a href='#' class='close-button'>
                                                    <?php require("images/site-images/menu-close.php"); ?>
                                                    </a>
                                                </div>
                                                <div class='popup-content'>
                                                    <h2>Copy Items From Another Wish List</h2>
                                                    <?php echo $copyFromErrorMsg ?? ""; ?>
                                                    <form method="POST" action="">
                                                        <label for="other_wishlist_copy_from">Choose Wish List:</label><br />
                                                        <select id="other_wishlist_copy_from" class="copy-select" name="other_wishlist_copy_from" required>
                                                            <option value="" disabled <?php if($other_wishlist_copy_from == "") echo "selected"; ?>>Select an option</option>
                                                            <?php
                                                            foreach($other_wishlist_options as $opt){
                                                                $other_id = $opt["id"];
                                                                $other_name = htmlspecialchars($opt["name"]);
                                                                echo "<option value='$other_id'";
                                                                if($other_id == $other_wishlist_copy_from) echo " selected";
                                                                echo ">$other_name</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                        <div class="other-items copy-from<?php if($other_wishlist_copy_from == "") echo " hidden"; ?>">
                                                            <label>Select Items:</label><br />
                                                            <div class="item-checkboxes">
                                                                <?php
                                                                $copy_from = true;
                                                                $other_wishlist_id = $other_wishlist_copy_from;
                                                                require("includes/find-other-items.php");
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                    if($findItems->num_rows > 0){ ?>
                                        <a class="icon-container popup-button" href="#"><?php require("images/site-images/icons/copy-to.php"); ?><div class="inline-label">Copy To...</div></a>
                                        <div class='popup-container first center-items hidden'>
                                            <div class='popup'>
                                                <div class='close-container'>
                                                    <a href='#' class='close-button'>
                                                    <?php require("images/site-images/menu-close.php"); ?>
                                                    </a>
                                                </div>
                                                <div class='popup-content'>
                                                    <h2>Copy Items to Another Wish List</h2>
                                                    <form method="POST" action="">
                                                        <label for="other_wishlist_copy_to">Choose Wish List:</label><br />
                                                        <select id="other_wishlist_copy_to" class="copy-select" name="other_wishlist_copy_to" required>
                                                            <option value="" disabled <?php if($other_wishlist_copy_to == "") echo "selected"; ?>>Select an option</option>
                                                            <?php
                                                            foreach($other_wishlist_options as $opt){
                                                                $other_id = $opt["id"];
                                                                $other_name = htmlspecialchars($opt["name"]);
                                                                echo "<option value='$other_id'";
                                                                if($other_id == $other_wishlist_copy_to) echo " selected";
                                                                echo ">$other_name</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                        <div class="other-items copy-to<?php if($other_wishlist_copy_to == "") echo " hidden"; ?>">
                                                            <label>Select Items:</label><br />
                                                            <div class="item-checkboxes">
                                                                <?php
                                                                $copy_from = false;
                                                                $other_wishlist_id = $other_wishlist_copy_to;
                                                                require("includes/find-other-items.php");
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </form>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    <?php }
                                    if($complete == "No"){ ?>
                                        <a class="icon-container popup-button" href="#">
                                            <?php
                                            if($visibility == "Public"){
                                                require("images/site-images/icons/hide-view.php");
                                            }else{
                                                require("images/site-images/icons/view.php");
                                            }    
                                            ?>
                                            <div class="inline-label"><?php echo $visibility == "Public" ? "Hide" : "Make Public"; ?></div>
                                        </a>
                                        <div class='popup-container first hidden'>
                                            <div class='popup'>
                                                <div class='close-container'>
                                                    <a href='#' class='close-button'>
                                                        <?php require("images/site-images/menu-close.php"); ?>
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
                                                    <p class='center'><a class='button secondary no-button'>No</a><a class='button primary' href='<?php echo $visibility == "Public" ? "hide" : "show"; ?>-wishlist.php?id=<?php echo $wishlistID; ?>&pageno=<?php echo $pageno; ?>'>Yes</a></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <a class="icon-container popup-button" href="#"><?php require("images/site-images/icons/checkmark.php"); ?><div class="inline-label"><?php echo $complete == "No" ? "Mark as Complete" : "Reactivate"; ?></div></a>
                                    <div class='popup-container first hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>
                                                <?php require("images/site-images/menu-close.php"); ?>
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
                                                <p class='center'><a class='button secondary no-button'>No</a><a class='button primary' href='<?php echo $complete == "No" ? "complete" : "reactivate"; ?>-wishlist.php?id=<?php echo $wishlistID; ?>&pageno=<?php echo $pageno; ?>'>Yes</a></p>
                                            </div>
                                        </div>
                                    </div>
                                    <a class="icon-container popup-button" href="#"><?php require("images/site-images/icons/delete-trashcan.php"); ?><div class="inline-label">Delete</div></a>
                                    <div class='popup-container first delete-wishlist-popup hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>
                                                <?php require("images/site-images/menu-close.php"); ?>
                                                </a>
                                            </div>
                                            <div class='popup-content'>
                                                <label>Are you sure you want to delete this wish list?</label>
                                                <p><?php echo $wishlistTitle; ?></p>
                                                <p class='center'><a class='button secondary no-button'>No</a><a class='button primary' href='delete-wishlist.php?id=<?php echo $wishlistID; ?>&pageno=<?php echo $pageno; ?>'>Yes</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            require("includes/sort.php");
            $findItems = $db->select("SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ? ORDER BY $priority_order$price_order date_added DESC", [$wishlistID, $username]);
            echo "
            <div class='items-list-container'>
                <h2 class='transparent-background items-list-title' id='paginate-top'>All Items<a href='add-item.php' class='icon-container add-item'>";
                require("images/site-images/icons/plus.php");
                echo "<div class='inline-label'>Add</div></a></h2>";
                if($findItems->num_rows > 0){
                    require("includes/write-filters.php");
                }
                echo "<div class='items-list-sub-container'>";
                paginate(type: "wisher", db: $db, query: "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ? ORDER BY $priority_order$price_order date_added DESC", itemsPerPage: 12, pageNumber: $pageno, values: [$wishlistID, $username], wishlist_id: $wishlistID, username: $username);
                echo "</div>";
                ?>
            </div>
        <?php include "includes/footer.php"; ?>
        </div>
    </div>
</body>
</html>
<script src="includes/popup.js"></script>
<script>$type = "wisher"; $key_url = "";</script>
<script src="includes/page-change.js"></script>
<script src="includes/choose-theme.js"></script>
<script src="includes/filter-change.js"></script>
<script>
    $(document).ready(function(){
        $(".icon.edit").on("click", function(){
            $("#wishlist_name")[0].select();
        });

        $("#wishlist_name").on("focus", function(){
            $(this).select();
        });

        $(".copy-select").on("change", function(e) {
            $select = $(this);
            $id = $select.val();
            $copy_from = $select.attr("id") == "other_wishlist_copy_from" ? "Yes" : "No";

            $.ajax({
                type: "POST",
                url: "includes/ajax/other-wishlist-change.php",
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
    });

    // copy link to share
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
</script>
