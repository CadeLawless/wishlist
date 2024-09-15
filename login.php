<?php
session_start();
ini_set('display_errors', 'On');
date_default_timezone_set("America/Chicago");

// database connections
require("includes/classes.php");
require("includes/error-functions.php");
$db = new DB();

// initialize form field variables
$username = "";
$password = "";

// submit php
if(isset($_POST["submit_button"])){
    $errors = false;
    $error_title = "<b>Login failed due to the following errors:<b>";
    $errorList = "";
    $username = errorCheck("username", "Username", "Yes", $errors, $errorList);
    $password = errorCheck("password", "Password", "Yes", $errors, $errorList);
    if(!$errors){
        $findUser = $db->select("SELECT password FROM wishlist_users WHERE username = ?", [$username]);
        if($findUser->num_rows > 0){
            while($row = $findUser->fetch_assoc()){
                $hashed_password = $row["password"];
                if(password_verify($password, $hashed_password)){
                    $expire_date = date("Y-m-d H:i:s", strtotime("+1 year"));
                    if($db->write("UPDATE wishlist_users SET session = ?, session_expiration = ? WHERE username = ?", [session_id(), $expire_date, $username])){
                        $cookie_time = (3600 * 24 * 365); // 1 year
                        setcookie("wishlist_session_id", session_id(), time() + $cookie_time);
                        $_SESSION["logged_in"] = true;
                        $_SESSION["username"] = $username;
                        header("Location: index.php");
                    }
                }else{
                    $errors = true;
                    $errorList .= "<li>Username or password is incorrect</li>";
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
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <p class="center"><img class="logo login" src="images/site-images/logo.png" alt="Wish List" /></p>
        <?php if(isset($error_msg)) echo $error_msg; ?>
        <form id="login-form" method="POST" action="">
            <div class="large-input center">
                <label for="username">Username: </label><br>
                <input required type="text" value="<?php echo $username; ?>" name="username" id="username">
                <span class="error-msg hidden">Username must include</span>
            </div>
            <div class="large-input center">
                <label for="password">Password: </label><br>
                <input required type="password" name="password" id="password">
                <span class="error-msg hidden">Password must include</span>
            </div>
            <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Login"></p>
            <p style="font-size: 14px" class="large-input center">Dont have an account? <a href="create-an-account.php">Create one here</a></p>
        </form>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>