<?php
session_start();
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

$reset_password_key = $_GET["key"] ?? "";
$email = $_GET["email"] ?? "";

$findUser = $db->select("SELECT dark, reset_password_expiration FROM wishlist_users WHERE email = ? AND reset_password_key = ?", [$email, $reset_password_key]);
if($findUser->num_rows > 0){
    while($row = $findUser->fetch_assoc()){
        $expiration = $row["reset_password_expiration"];
        $dark = $row["dark"] == "Yes" ? true : false;
        if(date("Y-m-d H:i:s") <= $expiration){
            $valid = true;
            $success = false;
            $new_password = "";
            $confirm_password = "";
        }else{
            $valid = false;
            $msg = "<p>Uh oh! This password reset link has expired. Try again!</p><p><a href='login.php' class='button primary'>Go to Login</a></p>";
        }
    }
}else{
    $valid = false;
    $msg = "<p>Uh oh! No account found for this password reset.</p><p><a href='login.php' class='button primary'>Go to Login</a></p>";
}

// if password submit button is clicked
if(isset($_POST["password_submit_button"])){
    $errors = false;
    $error_title = "<b>Password could not be changed due to the following errors:<b>";
    $error_list = "";

    $new_password = errorCheck("new_password", "New Password", "Yes", $errors, $error_list);
    patternCheck(regex: "/^(?=.*[0-9])(?=.*[a-zA-Z])(.+){6,}$/", input: $new_password, errors: $errors, error_list: $error_list, msg: "<li>New Password does not meet the requirements.</li>");
    $confirm_password = errorCheck("confirm_password", "Confirm New Password", "Yes", $errors, $error_list);
    if($new_password != "" && $confirm_password != "" && $new_password != $confirm_password){
        $errors = true;
        $error_list .= "<li>New Password and Confirm New Password must match.</li>";
    }else{
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    }
    if(!$errors){
        if($db->write("UPDATE wishlist_users SET password = ?, reset_password_key = NULL, reset_password_expiration = NULL WHERE email = ?", [$hashed_password, $email])){
            $success = true;
            unset($_COOKIE['wishlist_session_id']);
            setcookie('wishlist_session_id', "", 1); // empty value and old timestamp
            foreach($_SESSION as $key => $val){
                unset($_SESSION[$key]);
            }
            session_unset();
            session_destroy();
            setcookie("PHPSESSID", "", 1);
        }else{
            echo "<script>alert('Something went wrong while trying to create this account')</script>";
            // echo $db->error;
        }
    }else{
        $password_error_msg = "<div class='submit-error'>$error_title<ul>$error_list</ul></div><br>";
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
    <title>Wish List | Reset Password</title>
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
            margin: 0.5em 0;
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
                <?php
                if($valid){ 
                    if($success){
                        echo "<h3>Success! Your password has been reset successfully.</h3><p><a class='button primary' href='login.php'>Go to Login</a></p>";
                    }else{ ?>
                        <form method="POST" action="">
                            <h2>Reset Your Password</h2>
                            <?php echo $password_error_msg ?? ""; ?>
                            <div class="flex form-flex">
                                <div class="large-input">
                                    <div style="margin: 0 0 22px;">
                                        Password Requirements:
                                        <ul>
                                            <li>Must include at least one letter and one number</li>
                                            <li>Must be at least 6 characters long</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="large-input">
                                    <label for="new_password">New Password: </label><br>
                                    <div class="password-input">
                                        <input required type="password" name="new_password" id="new_password" value="<?php echo $new_password?>" pattern="^(?=.*[0-9])(?=.*[a-zA-Z])(.+){6,}$">
                                        <span class="password-view hide-password-new hidden"><?php require("images/site-images/icons/hide-view.php"); ?></span>
                                        <span class="password-view view-password-new"><?php require("images/site-images/icons/view.php"); ?></span>
                                        <span class="error-msg hidden">Please match the requirements</span>
                                    </div>
                                </div>
                                <div class="large-input">
                                    <label for="confirm_password">Confirm New Password: </label><br>
                                    <input required type="password" name="confirm_password" value="<?php echo $confirm_password?>" id="confirm_password">
                                    <span class="error-msg hidden">Passwords must match</span>
                                </div>

                                <p class="large-input"><input type="submit" class="button text" id="password_submit_button" name="password_submit_button" value="Change Password"></p>
                            </div>
                        </form>
                    <?php }
                }else{
                    echo $msg;
                }
                ?>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script>
$(document).ready(function(){
    $("input").on("input", function(){
        if(this.validity.patternMismatch){
            setTimeout(() => {
                if(this.validity.patternMismatch){
                    if($(this).hasClass("price-input")){
                        $(this).parent().addClass("invalid");
                    }else{
                        $(this).addClass("invalid");
                    }
                }
            }, 2500);
        }else{
            if($(this).hasClass("price-input")){
                $(this).parent().removeClass("invalid");
            }else{
                $(this).removeClass("invalid");
            }
        }
    });

    $(".view-password-new").on("click", function(){
        $("#new_password, #confirm_password").attr("type", "text");
        $(this).addClass("hidden");
        $(".hide-password-new").removeClass("hidden");
    });
    $(".hide-password-new").on("click", function(){
        $("#new_password, #confirm_password").attr("type", "password");
        $(this).addClass("hidden");
        $(".view-password-new").removeClass("hidden");
    });

    $(".view-password").on("click", function(){
        $("#current_password").attr("type", "text");
        $(this).addClass("hidden");
        $(".hide-password").removeClass("hidden");
    });
    $(".hide-password").on("click", function(){
        $("#current_password").attr("type", "password");
        $(this).addClass("hidden");
        $(".view-password").removeClass("hidden");
    });

    if($(".submit-error").length > 0){
        $(".submit-error")[0].scrollIntoView({ behavior: "smooth" })
    }
});
</script>