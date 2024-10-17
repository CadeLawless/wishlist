<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

require("includes/write-theme-popup.php");

// initialize form field variables
$wishlist_type = "";
$wishlist_type_options = ["Birthday", "Christmas"];
$theme_background_id = "";
$theme_gift_wrap_id = "";
$wishlist_name = "";

if(isset($_POST["submit_button"])){
    $errors = false;
    $errorTitle = "<strong>The wishlist could not be created due to the following errors:</strong>";
    $errorList = "";

    $wishlist_type = errorCheck("wishlist_type", "Type", "Yes", $errors, $errorList);
    validOptionCheck($wishlist_type, "Type", $wishlist_type_options, $errors, $errorList);

    $theme_background_id = $_POST["theme_background_id"];
    $theme_gift_wrap_id = $_POST["theme_gift_wrap_id"];

    $wishlist_name = errorCheck("wishlist_name", "Name", "Yes", $errors, $errorList);

    if(!$errors){
        // function that generates random string
        function generateRandomString($length) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[random_int(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        // generate random key for wishlist
        $unique = false;
        while(!$unique){
            $secret_key = generateRandomString(10);
            // check to make sure that key doesn't exist for another wishlist in the database
            $checkKey = $db->select("SELECT secret_key FROM wishlists WHERE secret_key = ?", [$secret_key]);
            if($checkKey->num_rows == 0) $unique = true;
        }

        // get year for wishlist
        $currentYear = date("Y");
        $year = date("m/d/Y") >= "12/25/$currentYear" ? $currentYear + 1 : $currentYear;

        // find if there is a duplicate type and year in database
        $findDuplicates = $db->select("SELECT id FROM wishlists WHERE type = ? AND wishlist_name = ? AND username = ?", [$wishlist_type, $wishlist_name, $username]);
        $duplicateValue = $findDuplicates->num_rows;

        // create new christmas wishlist for user
        if($db->write("INSERT INTO wishlists(type, wishlist_name, theme_background_id, theme_gift_wrap_id, year, duplicate, username, secret_key) VALUES(?, ?, ?, ?, ?, ?, ?, ?)", [$wishlist_type, $wishlist_name, $theme_background_id, $theme_gift_wrap_id, $year, $duplicateValue, $username, $secret_key])){
            $wishlistID = $db->insert_id();
            header("Location: view-wishlist.php?id=$wishlistID");
        }
    }else{
        $errorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
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
    <title><?php echo $name; ?>'s Wish Lists</title>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
            <h1 class="center"><?php echo $name; ?>'s Wish Lists</h1>
            <p class="center" style="margin: 0 0 36px;"><a class="button primary" href="create-wishlist.php">Create a New Wish List</a></p>
            <div class="wishlist-grid">
                <?php
                $findWishlists = $db->select("SELECT id, type, wishlist_name, duplicate, theme_background_id, theme_gift_wrap_id FROM wishlists WHERE username = ? ORDER BY date_created DESC", [$username]);
                if($findWishlists->num_rows > 0){
                    while($row = $findWishlists->fetch_assoc()){
                        $id = $row["id"];
                        $type = $row["type"];
                        $list_name = $row["wishlist_name"];
                        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
                        $theme_background_id = $row["theme_background_id"];
                        $theme_gift_wrap_id = $row["theme_gift_wrap_id"];
                        if($theme_background_id != 0){
                            $findBackground = $db->select("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_background_id]);
                            if($findBackground->num_rows > 0){
                                while($bg_row = $findBackground->fetch_assoc()){
                                    $background_image = $bg_row["theme_image"];
                                }
                            }
                        }else{
                            $background_image = "";
                        }
                        $findGiftWrap = $db->select("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_gift_wrap_id]);
                        if($findGiftWrap->num_rows > 0){
                            while($gw_row = $findGiftWrap->fetch_assoc()){
                                $wrap_image = $gw_row["theme_image"];
                            }
                        }
                        echo "
                        <a class='wishlist-grid-item' href='view-wishlist.php?id=$id'>
                            <div class='items-list preview' style='";
                            echo $background_image == "" ? "" : "background-image: url(images/site-images/themes/desktop-thumbnails/$background_image);";
                            echo "'>
                                <div class='item-container'>
                                    <img src='images/site-images/themes/gift-wraps/$wrap_image/1.png' class='gift-wrap' alt='gift wrap'>
                                    <div class='item-description'>
                                        <div class='bar title'></div>
                                        <div class='bar'></div>
                                        <div class='bar'></div>
                                        <div class='bar'></div>
                                        <div class='bar'></div>
                                    </div>
                                </div>
                            </div>
                            <div class='wishlist-overlay'></div>
                            <div class='wishlist-name'><span>$list_name$duplicate</span></div>
                        </a>";
                    }
                }else{
                    echo "<p style='grid-column: 1 / -1;' class='center'>It doesn't look like you have any wish lists created yet</p>";
                }
                ?>
            </div>
            <?php include "includes/footer.php"; ?>
        </div>
    </div>
</body>
</html>