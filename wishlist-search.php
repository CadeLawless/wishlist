<?php
session_start();
ini_set('display_errors', 'On');
date_default_timezone_set("America/Chicago");

// database connections
require("includes/classes.php");
require("includes/error-functions.php");
$db = new DB();

// initialize form field variables
$key = "";

// submit php
if(isset($_POST["submit_button"])){
    $errors = false;
    $error_title = "<b>Search failed due to the following errors:<b>";
    $errorList = "";
    $key = errorCheck("key", "Wishlist Secret Key", "Yes", $errors, $errorList);
    if(!$errors){
        $checkKey = $db->select("SELECT secret_key FROM wishlists WHERE secret_key = ?", "s", [$key]);
        if($checkKey->num_rows > 0){
            header("Location: buyer-view.php?key=$key");
        }else{
            $error_msg = "<div class='submit-error'><strong>Oops! No wishlist found. Try again!</strong></div>";
        }
    }else{
        $error_msg = "<div class='submit-error'>$error_title<ul>$errorList</ul></div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title>Wishlist | Search</title>
</head>
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <h1 class="center">Wishlist Search</h1>
        <form id="login-form" method="POST" action="">
            <?php if(isset($error_msg)) echo $error_msg; ?>
            <div class="large-input center">
                <label for="key">Wishlist Secret Key: </label><br>
                <input required type="text" value="<?php echo $key; ?>" name="key" id="key">
            </div>
            <p class="large-input center"><input type="submit" name="submit_button" value="Search"></p>
        </form>
    </div>
</body>
</html>