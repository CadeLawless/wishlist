<?php
session_start();
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

$email_key = $_GET["key"] ?? "";
$username = $_GET["username"] ?? "";

$findUser = $db->select("SELECT dark, unverified_email, email_key_expiration FROM wishlist_users WHERE username = ? AND email_key = ?", [$username, $email_key]);
if($findUser->num_rows > 0){
    while($row = $findUser->fetch_assoc()){
        $email = $row["unverified_email"];
        $expiration = $row["email_key_expiration"];
        $dark = $row["dark"] == "Yes" ? true : false;
        if(date("Y-m-d H:i:s") <= $expiration){
            if($db->write("UPDATE wishlist_users SET email = ?, email_key = NULL, unverified_email = NULL, email_key_expiration = NULL WHERE username = ? AND email_key = ?", [$email, $username, $email_key])){
                $msg = "<h2>Success!</h2><p>Your email has been verified.</p><p><a class='button primary' href='index.php'>Go home</a></p>";
            }else{
                $msg = "<p>Uh oh! Something went wrong while trying to verify your email. Please try again or email <a href='mailto:support@cadelawless.com'>support@cadelawless.com</a> for help.</p>";
            }
        }else{
            $msg = "<p>Uh oh! This email verification link has expired. Try again!</p><p><a href='view-profile.php' class='button primary'>Go to your profile</a></p>";
        }
    }
}else{
    $msg = "<p>Uh oh! No account found for this email verification.</p><p><a href='view-profile.php' class='button primary'>Go to your profile</a></p>";
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
    <title>Wish List | Email Verification</title>
    <style>
        #body {
            padding-top: 84px;
        }
        h1 {
            display: inline-block;
            margin-top: 0;
        }
        h2 {
            font-size: 28px;
        }
        .form-container {
            margin: clamp(20px, 4vw, 60px) auto 30px;
            background-color: var(--background-darker);
            max-width: 500px;
        }
        input:not([type=submit], #new_password, #current_password) {
            margin-bottom: 0;
        }
        h3 {
            margin-bottom: 0.5em;
        }
        .header .title {
            flex-basis: 100%;
        }
        .menu-links, .hamburger-menu, .close-menu {
            display: none !important;
        }
    </style>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
            <div class="form-container">
                <?php echo $msg; ?>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
