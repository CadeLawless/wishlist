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
$email = "";
$success = false;

// submit php
if(isset($_POST["submit_button"])){
    $errors = false;
    $error_title = "<b>Login failed due to the following errors:<b>";
    $errorList = "";
    $email = errorCheck("email", "Email", "Yes", $errors, $error_list);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors = true;
        $error_list .= "<li>Please enter a valid email</li>";
    }
    if(!$errors){
        $findUser = $db->select("SELECT email FROM wishlist_users WHERE email = ?", [$email]);
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
            if($db->write("UPDATE wishlist_users SET reset_password_key = ?, reset_password_expiration = ? WHERE email = ?", [$reset_password_key, $reset_password_expiration, $email])){
                $msg = "
                <h2>Forgot your password? No problem!</h2>
                <p>To reset your Wish List password, click the button below:</p>
                <a href='https://" .$_SERVER["SERVER_NAME"] . "/wishlist/reset-password.php?key=$reset_password_key&email=$email' 
                style='display: inline-block; padding: 12px 24px; color: #ffffff; background-color: #3e5646; border-radius: 5px; text-decoration: none; font-weight: bold;'>
                    Reset Password
                </a>
                <p style='margin-top: 20px;'>This link will expire in 24 hours, so please complete your password reset as soon as possible.</p>
                <p style='font-size: 12px;'>If you did not attempt to reset a password for a Wish List account, please ignore this email.</p>
                <p style='font-size: 12px; margin-top: 20px;'>Thank you,<br>The Wish List Team</p>";
                $subject = "Reset Your Password for Wish List";
                send_email(to: $email, subject: $subject, msg: $msg);
            }else{
                echo "<script>alert('Something went wrong while trying to create this account')</script>";
                // echo $db->error;
            }
        }
        $success = true;
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
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title>Wish List | Forgot Password</title>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <div id="container">
            <p class="center"><img class="logo login" src="images/site-images/logo.png" alt="Wish List" /></p>
            <form id="login-form" style="max-width: 500px;" method="POST" action="">
                <p><a href="login.php">Back to Login</a></p>
                <?php if($success){
                    echo "<p>Success! If the email you entered is associated with an account, you will receive an email with a password reset link.</p>";
                }else{ ?>
                    <h2 class="center">Forgot Password?</h2>
                    <p class="large-input center">Enter your email below. If the email is associated with your account, you will receive an email with a password reset link.</p>
                    <?php if(isset($error_msg)) echo $error_msg; ?>
                    <div class="large-input center" style="max-width: 350px; margin: auto;">
                        <label for="email">Email: </label><br>
                        <input required type="email" value="<?php echo htmlspecialchars($email); ?>" name="email" id="email">
                    </div>
                    <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Send Email"></p>
                <?php } ?>
            </form>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>