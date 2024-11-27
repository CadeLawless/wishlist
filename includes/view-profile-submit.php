<?php
$return_page = $admin_forms ? "edit-user.php?username=$wishlist_user_username" : "view-profile.php";
$edit_username = $admin_forms ? $wishlist_user_username : $username;
// if name submit button is clicked
if(isset($_POST["name_submit_button"])){
    $errors = false;
    $error_title = "<b>Name could not be updated due to the following errors:<b>";
    $error_list = "";
    $edit_name = errorCheck("name", "Name", "Yes", $errors, $error_list);
    if(!$errors){
        if($db->write("UPDATE wishlist_users SET name = ? WHERE username = ?", [$edit_name, $edit_username])){
            $_SESSION["name_updated"] = true;
            header("Location: $return_page");
        }else{
            echo "<script>alert('Something went wrong while trying to update your name')</script>";
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
        if($db->write("UPDATE wishlist_users SET unverified_email = ?, email_key = ?, email_key_expiration = ? WHERE username = ?", [$email, $email_key, $email_key_expiration, $edit_username])){
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
            header("Location: $return_page");
        }else{
            echo "<script>alert('Something went wrong while trying to update your email')</script>";
            // echo $db->error;
        }
    }else{
        $error_msg = "<div class='submit-error'>$error_title<ul>$error_list</ul></div><br>";
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
                header("Location: $return_page");
            }else{
                echo "<script>alert('Something went wrong while trying to send the reset email')</script>";
                // echo $db->error;
            }
        }
    }
}
?>