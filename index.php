<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";
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
