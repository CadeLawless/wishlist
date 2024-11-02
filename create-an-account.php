<?php
session_start();
ini_set('display_errors', 'On');
date_default_timezone_set("America/Chicago");

// database connections
require("includes/classes.php");
require("includes/error-functions.php");
$db = new DB();

// php mailer setup
require "../PHPMailer/php-mailer-setup/php-mailer.php";

// initialize form field variables
$name = "";
$username = "";
$email = "";
$password = "";
$confirm_password = "";

// submit php
if(isset($_POST["submit_button"])){
    $errors = false;
    $error_title = "<b>An account could not be created due to the following errors:<b>";
    $error_list = "";
    $name = errorCheck("name", "First Name", "Yes", $errors, $error_list);
    $email = errorCheck("email", "Email", "Yes", $errors, $error_list);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors = true;
        $error_list .= "<li>Please enter a valid email</li>";
    }
    if(!$errors){
        $findUsers = $db->select("SELECT email FROM wishlist_users WHERE email = ?", [$email]);
        if($findUsers->num_rows > 0){
            $errors = true;
            $error_list .= "<li>That email already has an account associated with it. Try a different one.</li>";
        }
    }
    $username = errorCheck("username", "Username", "Yes", $errors, $error_list);
    if(!$errors){
        $findUsers = $db->select("SELECT username FROM wishlist_users WHERE username = ?", [$username]);
        if($findUsers->num_rows > 0){
            $errors = true;
            $error_list .= "<li>That username is already taken. Try a different one.</li>";
        }
    }
    $password = errorCheck("password", "Password", "Yes", $errors, $error_list);
    patternCheck(regex: "/^(?=.*[0-9])(?=.*[a-zA-Z])(.+){6,}$/", input: $password, errors: $errors, error_list: $error_list, msg: "<li>Password does not meet the requirements.</li>");
    $confirm_password = errorCheck("confirm_password", "Confirm Password", "Yes", $errors, $error_list);
    if($password != "" && $confirm_password != "" && $password != $confirm_password){
        $errors = true;
        $error_list .= "<li>Password and Confirm Password must match.</li>";
    }else{
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }
    if(!$errors){
        $email_key = generateRandomString(50);
        $email_key_expiration = date("Y-m-d H:i:s", strtotime("+24 hours"));
        $expire_date = date("Y-m-d H:i:s", strtotime("+1 year"));
        if($db->write("INSERT INTO wishlist_users (name, unverified_email, username, password, session, session_expiration, email_key, email_key_expiration) VALUES(?,?,?,?,?,?,?,?)", [$name, $email, $username, $hashed_password, session_id(), $expire_date, $email_key, $email_key_expiration])){
            $cookie_time = (3600 * 24 * 365); // 1 year
            setcookie("wishlist_session_id", session_id(), time() + $cookie_time);
            $_SESSION["wishlist_logged_in"] = true;
            $_SESSION["username"] = $username;
            $_SESSION["account_created"] = true;
            $msg = "
            <h2>Welcome to Wish List!</h2>
            <p>Thank you for signing up for Wish List!</p>
            <p>To complete your account registration, please verify your email address by clicking the button below:</p>
            <a href='https://" .$_SERVER["SERVER_NAME"] . "/wishlist/verify-email.php?key=$email_key&username=$username' 
            style='display: inline-block; padding: 12px 24px; color: #ffffff; background-color: #3e5646; border-radius: 5px; text-decoration: none; font-weight: bold;'>
                Verify My Email Address
            </a>
            <p style='margin-top: 20px;'>This link will expire in 24 hours, so please complete your verification as soon as possible.</p>
            <p style='font-size: 12px;'>If you did not create a Wish List account, please ignore this email.</p>
            <p style='font-size: 12px; margin-top: 20px;'>Thank you,<br>The Wish List Team</p>";
            $subject = "Verify Your Email for Wish List";
            send_email(to: $email, subject: $subject, msg: $msg);
            header("Location: index.php");
        }else{
            echo "<script>alert('Something went wrong while trying to create this account')</script>";
            // echo $db->error;
        }
    }else{
        $error_msg = "<div class='submit-error'>$error_title<ul>$error_list</ul></div><br>";
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
    <title>Wishlist | Create an Account</title>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <div id="container">
            <p class="center"><img class="logo login" src="images/site-images/logo.png" alt="Wish List" /></p>
            <form id="login-form" method="POST" action="">
                <p><a href="login.php">Back to Login</a></p>
                <?php if(isset($error_msg)) echo $error_msg?>
                <div class="large-input center">
                    <label for="name">First Name: </label><br>
                    <input required type="text" name="name" placeholder="Dwight" value="<?php echo $name?>" id="name">
                </div>
                <div class="large-input center">
                    <label for="username">Username: </label><br>
                    <input required type="text" name="username" placeholder="belsnickel123" value="<?php echo $username?>" id="username">
                </div>
                <div class="large-input center">
                    <label for="email">Email: </label><br>
                    <input required type="email" name="email" placeholder="dwightkschrute@dundermifflin.com" value="<?php echo $email?>" id="email">
                </div>
                <div class="large-input">
                    <div style="margin: 0 0 22px;">
                        Password Requirements:
                        <ul>
                            <li>Must include at least one letter and one number</li>
                            <li>Must be at least 6 characters long</li>
                        </ul>
                    </div>
                </div>
                <div class="large-input center">
                    <label for="password">Password: </label><br>
                    <div class="password-input">
                        <input required type="password" name="password" id="password" value="<?php echo $password?>" pattern="^(?=.*[0-9])(?=.*[a-zA-Z])(.+){6,}$">
                        <span class="password-view hide-password hidden"><?php require("images/site-images/icons/hide-view.php"); ?></span>
                        <span class="password-view view-password"><?php require("images/site-images/icons/view.php"); ?></span>
                        <span class="error-msg hidden">Please match the requirements</span>
                    </div>
                </div>
                <div class="large-input center">
                    <label for="confirm_password">Confirm Password: </label><br>
                    <input required type="password" name="confirm_password" value="<?php echo $confirm_password?>" id="confirm_password">
                    <span class="error-msg hidden">Passwords must match</span>
                </div>
                <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Start Wishing"></p>
            </form>
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

    $(".view-password").on("click", function(){
        $("#password, #confirm_password").attr("type", "text");
        $(this).addClass("hidden");
        $(".hide-password").removeClass("hidden");
    });
    $(".hide-password").on("click", function(){
        $("#password, #confirm_password").attr("type", "password");
        $(this).addClass("hidden");
        $(".view-password").removeClass("hidden");
    });
});
</script>