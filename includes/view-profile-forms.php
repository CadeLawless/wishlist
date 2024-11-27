<?php $user_label = $admin_forms ? "User's" : "Your"; ?>
<?php if(isset($errorMsg)) echo $errorMsg; ?>
<form method="POST" action="">
    <h3>Change <?php echo $user_label; ?> Name</h3>
    <?php echo $name_error_msg ?? ""; ?>
    <div class="flex form-flex">
        <div class="large-input">
            <label for="name">Name:<br></label>
            <input required type="text" name="name" id="name" autocapitalize="words" value="<?php echo htmlspecialchars($edit_name); ?>" />
        </div>
        <p class="large-input"><input type="submit" class="button text" id="name_submit_button" name="name_submit_button" value="Change Name"></p>
    </div>
</form>
<br />
<form method="POST" action="">
    <h3>Change <?php echo $user_label; ?> Email</h3>
    <?php echo $email_error_msg ?? ""; ?>
    <div class="flex form-flex">
        <?php
        if(!$admin_forms && $user_email == "") echo "<p class='large-input no-margin-top'>Set up your email in case you ever forget your password!</p>";
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
<?php if(!$admin_forms){ ?>
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
<?php } ?>
<h3><?php echo $admin_forms ? "User Forgot Password?" : "Forgot Your Password?"; ?></h3>
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
