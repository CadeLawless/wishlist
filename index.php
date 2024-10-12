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
    <title>Wish List</title>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
            <div class="big-buttons-container">
                <a class="big-button create-wish-list" href="create-wishlist.php"><?php require("images/site-images/icons/plus.php"); ?>Create Wish List</a>
                <a class="big-button view-wish-list" href="view-wishlists.php"><?php require("images/site-images/icons/search.php"); ?>View Wish Lists</a>
            </div>
            <?php include "includes/footer.php"; ?>
        </div>
    </div>
</body>
</html>
