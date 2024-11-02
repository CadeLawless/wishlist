<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// php mailer setup
require "../PHPMailer/php-mailer-setup/php-mailer.php";

$password_changed = isset($_SESSION["password_changed"]) ? true : false;
if($password_changed) unset($_SESSION["password_changed"]);

$email_needs_verified = isset($_SESSION["email_needs_verified"]) ? true : false;
if($email_needs_verified) unset($_SESSION["email_needs_verified"]);

$reset_email_sent = isset($_SESSION["reset_email_sent"]) ? true : false;
if($reset_email_sent) unset($_SESSION["reset_email_sent"]);

//initialize form field variables
$current_password = "";
$new_password = "";
$confirm_password = "";
$email = $user_email;

// if name submit button is clicked
if(isset($_POST["name_submit_button"])){
    $errors = false;
    $error_title = "<b>Name could not be updated due to the following errors:<b>";
    $error_list = "";
    $user_fullname = errorCheck("name", "First Name", "Yes", $errors, $error_list);
    if(!$errors){
        if($db->write("UPDATE wishlist_users SET name = ? WHERE username = ?", [$user_fullname, $username])){
            $_SESSION["name_updated"] = true;
            header("Location: view-profile.php");
        }else{
            echo "<script>alert('Something went wrong while trying to create this account')</script>";
            // echo $db->error;
        }
    }else{
        $name_error_msg = "<div class='submit-error'>$error_title<ul>$error_list</ul></div><br>";
    }
}

// if email submit button is clicked
if(isset($_POST["email_submit_button"])){
    $errors = false;
    $error_title = "<b>Email could not be updated due to the following errors:<b>";
    $error_list = "";
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
    if(!$errors){
        $email_key = generateRandomString(50);
        $email_key_expiration = date("Y-m-d H:i:s", strtotime("+24 hours"));
        if($db->write("UPDATE wishlist_users SET unverified_email = ?, email_key = ?, email_key_expiration = ? WHERE username = ?", [$email, $email_key, $email_key_expiration, $username])){
            $msg = "
            <h2>Hi from Wish List!</h2>
            <p>Thank you for setting up your email for Wish List!</p>
            <p>To complete your email setup, please verify your email address by clicking the button below:</p>
            <a href='https://" .$_SERVER["SERVER_NAME"] . "/wishlist/verify-email.php?key=$email_key&username=$username' 
            style='display: inline-block; padding: 12px 24px; color: #ffffff; background-color: #3e5646; border-radius: 5px; text-decoration: none; font-weight: bold;'>
                Verify My Email Address
            </a>
            <p style='margin-top: 20px;'>This link will expire in 24 hours, so please complete your verification as soon as possible.</p>
            <p style='font-size: 12px;'>If you did not attempt to set up an email for a Wish List account, please ignore this email.</p>
            <p style='font-size: 12px; margin-top: 20px;'>Thank you,<br>The Wish List Team</p>";
            $subject = "Verify Your Email for Wish List";
            send_email(to: $email, subject: $subject, msg: $msg);
            $_SESSION["email_needs_verified"] = true;
            header("Location: view-profile.php");
        }else{
            echo "<script>alert('Something went wrong while trying to create this account')</script>";
            // echo $db->error;
        }
    }else{
        $error_msg = "<div class='submit-error'>$error_title<ul>$error_list</ul></div><br>";
    }
}

// if password submit button is clicked
if(isset($_POST["password_submit_button"])){
    $errors = false;
    $error_title = "<b>Password could not be changed due to the following errors:<b>";
    $error_list = "";

    $current_password = errorCheck("current_password", "Current Password", "Yes", $errors, $error_list);
    if($current_password != ""){
        $checkPassword = $db->select("SELECT password FROM wishlist_users WHERE username = ?", [$username]);
        if($checkPassword->num_rows > 0){
            while($row = $checkPassword->fetch_assoc()){
                $saved_password = $row["password"];
                if(!password_verify($current_password, $saved_password)){
                    $errors = true;
                    $error_list .= "<li>Incorrect value for Current Password</li>";
                }
            }
        }else{
            $errors = true;
            $error_list .= "<li>Account not found</li>";
        }
    }
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
        if($db->write("UPDATE wishlist_users SET password = ? WHERE username = ?", [$hashed_password, $username])){
            $_SESSION["password_changed"] = true;
            header("Location: view-profile.php");
        }else{
            echo "<script>alert('Something went wrong while trying to create this account')</script>";
            // echo $db->error;
        }
    }else{
        $password_error_msg = "<div class='submit-error'>$error_title<ul>$error_list</ul></div><br>";
    }
}

// forgot password submit php
if(isset($_POST["forgot_password_submit_button"])){
    if($user_email != ""){
        $findUser = $db->select("SELECT email FROM wishlist_users WHERE email = ?", [$user_email]);
        if($findUser->num_rows > 0){
            // generate random key for password reset
            $unique = false;
            while(!$unique){
                $reset_password_key = generateRandomString(50);
                // check to make sure that key doesn't exist for another wish list in the database
                $checkKey = $db->select("SELECT reset_password_key FROM wishlist_users WHERE reset_password_key = ?", [$reset_password_key]);
                if($checkKey->num_rows == 0) $unique = true;
            }
            $reset_password_expiration = date("Y-m-d H:i:s", strtotime("+24 hours"));
            if($db->write("UPDATE wishlist_users SET reset_password_key = ?, reset_password_expiration = ? WHERE email = ?", [$reset_password_key, $reset_password_expiration, $user_email])){
                $msg = "
                <h2>Forgot your password? No problem!</h2>
                <p>To reset your Wish List password, click the button below:</p>
                <a href='https://" .$_SERVER["SERVER_NAME"] . "/wishlist/reset-password.php?key=$reset_password_key&email=$user_email' 
                style='display: inline-block; padding: 12px 24px; color: #ffffff; background-color: #3e5646; border-radius: 5px; text-decoration: none; font-weight: bold;'>
                    Reset Password
                </a>
                <p style='margin-top: 20px;'>This link will expire in 24 hours, so please complete your password reset as soon as possible.</p>
                <p style='font-size: 12px;'>If you did not attempt to reset a password for a Wish List account, please ignore this email.</p>
                <p style='font-size: 12px; margin-top: 20px;'>Thank you,<br>The Wish List Team</p>";
                $subject = "Reset Your Password for Wish List";
                send_email(to: $user_email, subject: $subject, msg: $msg);
                $_SESSION["reset_email_sent"] = true;
                header("Location: view-profile.php");
            }else{
                echo "<script>alert('Something went wrong while trying to create this account')</script>";
                // echo $db->error;
            }
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
    <title>Wish List | View Profile</title>
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
    </style>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
            <?php
            if($password_changed){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require("images/site-images/menu-close.php");
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p>Password changed successfully.</p>
                        </div>
                    </div>
                </div>";
            }
            if($email_needs_verified){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require("images/site-images/menu-close.php");
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p>An email has been sent to you. Please click the link in it to complete your email setup.</p>
                        </div>
                    </div>
                </div>";
            }
            if($reset_email_sent){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require("images/site-images/menu-close.php");
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p>A reset email has been sent to you. Please click the link in it to reset your password.</p>
                        </div>
                    </div>
                </div>";
            }
            ?>
            <div class="form-container">
                <h2>Your Profile</h2>
                <?php if(isset($errorMsg)) echo $errorMsg; ?>
                <form method="POST" action="">
                    <h3>Change Your Name</h3>
                    <?php echo $name_error_msg ?? ""; ?>
                    <div class="flex form-flex">
                        <div class="large-input">
                            <label for="name">Name:<br></label>
                            <input required type="text" name="name" id="name" autocapitalize="words" value="<?php echo htmlspecialchars($user_fullname); ?>" />
                        </div>
                        <p class="large-input"><input type="submit" class="button text" id="name_submit_button" name="name_submit_button" value="Change Name"></p>
                    </div>
                </form>
                <br />
                <form method="POST" action="">
                    <h3>Change Your Email</h3>
                    <?php echo $email_error_msg ?? ""; ?>
                    <div class="flex form-flex">
                        <?php
                        if($user_email == "") echo "<p class='large-input no-margin-top'>Set up your email in case you ever forget your password!</p>";
                        if(!$email_verified){
                            echo "<p class='large-input'><strong>A link has been emailed to the email you entered. It will expire within 24 hours.</strong></p>";
                        }
                        ?>
                        <div class="large-input">
                            <label for="email">Email:<br></label>
                            <input required name="email" type="email" id="email" inputmode="email" value="<?php echo htmlspecialchars($email); ?>" />
                        </div>
                        <p class="large-input"><input type="submit" class="button text" id="email_submit_button" name="email_submit_button" value="<?php echo $user_email == "" ? "Set Up" : "Change"; ?> Email"></p>
                    </div>
                </form>
                <br />
                <form method="POST" action="">
                    <h3>Change Your Password</h3>
                    <?php echo $password_error_msg ?? ""; ?>
                    <div class="flex form-flex">
                        <div class="large-input">
                            <label for="current_password">Current Password:<br></label>
                            <div class="password-input">
                                <input required type="password" name="current_password" id="current_password" value="<?php echo $current_password?>" />
                                <span class="password-view hide-password hidden"><?php require("images/site-images/icons/hide-view.php"); ?></span>
                                <span class="password-view view-password"><?php require("images/site-images/icons/view.php"); ?></span>
                                <span class="error-msg hidden">Please match the requirements</span>
                            </div>
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
                <br />
                <h3>Forgot Your Password?</h3>
                <?php
                if($user_email == ""){
                    echo "<p>In order for you to reset a password that you don't know, an email with a password reset link needs to be sent to you. Please set up your email above before trying to reset your password</p>";
                }else{
                    echo "
                    <form method='POST' action=''>
                        <p class='large-input'><input type='submit' class='button text' id='forgot_password_submit_button' name='forgot_password_submit_button' value='Send Reset Email' /></p>
                    </form>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script src="includes/popup.js"></script>
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