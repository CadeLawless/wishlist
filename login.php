<?php
session_start();
ini_set('display_errors', 'On');
date_default_timezone_set("America/Chicago");

// database connections
require("includes/classes.php");
require("includes/error-functions.php");
$db = new DB();

if(isset($_SESSION["wishlist_logged_in"])) header("Location: index.php");

// initialize form field variables
$username = "";
$password = "";
$remember_me = "No";

// submit php
if(isset($_POST["submit_button"])){
    $errors = false;
    $error_title = "<b>Login failed due to the following errors:<b>";
    $errorList = "";
    $username = errorCheck("username", "Username", "Yes", $errors, $errorList);
    $password = errorCheck("password", "Password", "Yes", $errors, $errorList);
    $remember_me = isset($_POST["remember_me"]) ? "Yes" : "No";
    if(!$errors){
        $findUser = $db->select("SELECT username, password FROM wishlist_users WHERE username = ? OR email = ?", [$username, $username]);
        if($findUser->num_rows > 0){
            while($row = $findUser->fetch_assoc()){
                $hashed_password = $row["password"];
                $username = $row["username"];
                if(password_verify($password, $hashed_password)){
                    if($remember_me == "Yes"){
                        $expire_date = date("Y-m-d H:i:s", strtotime("+1 year"));
                        $sql = "UPDATE wishlist_users SET session = ?, session_expiration = ? WHERE username = ?";
                        $values = [session_id(), $expire_date, $username];
                    }else{
                        $sql = "UPDATE wishlist_users SET session = NULL, session_expiration = NULL WHERE username = ?";
                        $values = [$username];
                    }
                    if($db->write($sql, $values)){
                        if($remember_me == "Yes"){
                            $cookie_time = (3600 * 24 * 365); // 1 year
                            setcookie("wishlist_session_id", session_id(), time() + $cookie_time);
                        }
                        $_SESSION["wishlist_logged_in"] = true;
                        $_SESSION["username"] = $username;
                        header("Location: index.php");
                    }
                }else{
                    $errors = true;
                    $errorList .= "<li>Username/email or password is incorrect</li>";
                    $error_msg = "<div class='submit-error'>$error_title<ul>$errorList</ul></div>";
                }
            }
        }else{
            $errors = true;
            $errorList .= "<li>Username or password is incorrect</li>";
            $error_msg = "<div class='submit-error'>$error_title<ul>$errorList</ul></div>";
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
    <link rel="icon" type="image/x-icon" href="images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title>Wishlist | Login</title>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <div id="container">
            <p class="center"><img class="logo login" src="images/site-images/logo.png" alt="Wish List" /></p>
            <form id="login-form" style="max-width: 350px;" method="POST" action="">
                <?php if(isset($error_msg)) echo $error_msg; ?>
                <div class="large-input center">
                    <label for="username">Username or Email: </label><br>
                    <input required type="text" value="<?php echo $username; ?>" name="username" id="username">
                    <span class="error-msg hidden">Username must include</span>
                </div>
                <div class="large-input center">
                    <label for="password">Password: </label><br>
                    <div class="password-input">
                        <input required type="password" name="password" id="password" />
                        <span class="password-view hide-password hidden"><?php require("images/site-images/icons/hide-view.php"); ?></span>
                        <span class="password-view view-password"><?php require("images/site-images/icons/view.php"); ?></span>
                    </div>
                </div>
                <div class="large-input">
                    <input type="checkbox" id="remember_me" name="remember_me" <?php if($remember_me == "Yes") echo "checked"; ?> />
                    <label style="float: none; font-weight: normal;" for="remember_me">Remember me</label>
                </div>
                <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Login"></p>
                <p style="font-size: 14px" class="large-input center"><a style="font-size: inherit;" href="forgot-password.php">Forgot password?</a></p>
                <p style="font-size: 14px" class="large-input center">Don't have an account? <a style="font-size: inherit;" href="create-an-account.php">Create one here</a></p>
            </form>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script>
$(document).ready(function(){
    $(".view-password").on("click", function(){
        $("#password").attr("type", "text");
        $(this).addClass("hidden");
        $(".hide-password").removeClass("hidden");
    });
    $(".hide-password").on("click", function(){
        $("#password").attr("type", "password");
        $(this).addClass("hidden");
        $(".view-password").removeClass("hidden");
    });
});
</script>