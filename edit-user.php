<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

if(!$admin) header("Location: index.php");

$pageno = $_GET["pageno"] ?? 1;

$wishlist_user_username = $_GET["username"] ?? "";
$findUser = $db->select("SELECT name, email, role FROM wishlist_users WHERE username = ?", [$wishlist_user_username]);
if($findUser->num_rows > 0){
    while($row = $findUser->fetch_assoc()){
        $wishlist_user_name = $row["name"];
        $wishlist_user_email = $row["email"];
        $wishlist_user_role = $row["role"];
    }
}else{
    header("Location: no-user-found.php");
}

require("includes/view-profile-alert-session.php");

// initialize form field variables
$email = $wishlist_user_email;
$edit_name = $wishlist_user_name;
$edit_role = $wishlist_user_role;
$role_options = ["Admin", "User"];

// php mailer setup
require "../PHPMailer/php-mailer-setup/php-mailer.php";

$admin_forms = true;
require("includes/view-profile-submit.php");

// if role submit button is clicked
if(isset($_POST["role_submit_button"])){
    $errors = false;
    $error_title = "<b>Role could not be updated due to the following errors:<b>";
    $error_list = "";
    $edit_role = errorCheck("role", "Role", "Yes", $errors, $error_list);
    validOptionCheck($edit_role, "Role", $role_options, $errors, $error_list);

    if($wishlist_user_role == "Admin" && $edit_role == "User"){
        $findAdmins = $db->select("SELECT role FROM wishlist_users WHERE role = 'Admin' AND username <> ?", [$wishlist_user_username]);
        if($findAdmins->num_rows == 0){
            $errors = true;
            $error_list .= "<li>There must be at least one user with the Admin role assigned to them</li>";
        }
    }

    if(!$errors){
        if($db->write("UPDATE wishlist_users SET role = ? WHERE username = ?", [$edit_role, $wishlist_user_username])){
            $_SESSION["role_updated"] = true;
            header("Location: edit-user.php?username=$wishlist_user_username");
        }else{
            echo "<script>alert('Something went wrong while trying to create this account')</script>";
            // echo $db->error;
        }
    }else{
        $role_error_msg = "<div class='submit-error'>$error_title<ul>$error_list</ul></div><br>";
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
    <title>Wish List | Edit User</title>
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
        input:not([type=submit], #new_password, #current_password), select {
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
            <p style="padding-top: 15px;"><a class="button accent" href="admin-center.php?pageno=<?php echo $pageno; ?>">Back to All Users</a></p>
            <div class="form-container">
                <h2>Edit User</h2>
                <?php require("includes/view-profile-forms.php"); ?>
                <br />
                <form method="POST" action="">
                    <h3>Change User's Role</h3>
                    <?php echo $role_error_msg ?? ""; ?>
                    <div class="flex form-flex">
                        <div class="large-input">
                            <label for="role">Role:<br></label>
                            <select required name="role" id="role">
                            <?php
                            if(count($role_options) > 0){
                                foreach($role_options as $opt){
                                    echo "<option value='$opt'";
                                    if($edit_role == $opt) echo " selected";
                                    echo ">$opt</option>";
                                }
                            }
                            ?>
                            </select>
                        </div>
                        <p class="large-input"><input type="submit" class="button text" id="role_submit_button" name="role_submit_button" value="Change Role"></p>
                    </div>
                </form>
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

    if($(".submit-error").length > 0){
        $(".submit-error")[0].scrollIntoView({ behavior: "smooth" })
    }
});
</script>