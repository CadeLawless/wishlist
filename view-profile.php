<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// php mailer setup
require "../PHPMailer/php-mailer-setup/php-mailer.php";

require("includes/view-profile-alert-session.php");

//initialize form field variables
$current_password = "";
$new_password = "";
$confirm_password = "";
$email = $user_email;
$edit_name = $user_fullname;

$admin_forms = false;
require("includes/view-profile-submit.php");
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
        <?php require("includes/view-profile-alerts.php"); ?>
            <div class="form-container">
                <h2>Your Profile</h2>
                <?php require("includes/view-profile-forms.php"); ?>
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