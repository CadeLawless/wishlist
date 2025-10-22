<?php
// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['success']) . "</label></p>
            </div>
        </div>
    </div>";
}

if (isset($flash['error'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['error']) . "</label></p>
            </div>
        </div>
    </div>";
}
?>

<div class="form-container">
    <h2>Your Profile</h2>
    
    <!-- Name Update Form -->
    <form method="POST" action="">
        <h3>Change Your Name</h3>
        <?php echo $name_error_msg ?? ""; ?>
        <div class="flex form-flex">
            <div class="large-input">
                <label for="name">Name:<br></label>
                <input required type="text" name="name" id="name" autocapitalize="words" value="<?php echo htmlspecialchars($name); ?>" />
            </div>
            <p class="large-input"><input type="submit" class="button text" id="name_submit_button" name="name_submit_button" value="Change Name"></p>
        </div>
    </form>
    <br />

    <!-- Email Update Form -->
    <form method="POST" action="">
        <h3>Change Your Email</h3>
        <?php echo $email_error_msg ?? ""; ?>
        <div class="flex form-flex">
            <?php if (empty($user['email'])): ?>
                <p class='large-input no-margin-top'>Set up your email in case you ever forget your password!</p>
            <?php endif; ?>
            <div class="large-input">
                <label for="email">Email:<br></label>
                <input required name="email" type="email" id="email" inputmode="email" value="<?php echo htmlspecialchars($email); ?>" />
            </div>
            <p class="large-input"><input type="submit" class="button text" id="email_submit_button" name="email_submit_button" value="<?php echo empty($user['email']) ? "Set Up" : "Change"; ?> Email"></p>
        </div>
    </form>
    <br />

    <!-- Password Change Form -->
    <form method="POST" action="">
        <h3>Change Your Password</h3>
        <?php echo $password_error_msg ?? ""; ?>
        <div class="flex form-flex">
            <div class="large-input">
                <label for="current_password">Current Password:<br></label>
                <div class="password-input">
                    <input required type="password" name="current_password" id="current_password" value="<?php echo htmlspecialchars($current_password); ?>" />
                    <span class="password-view hide-password hidden"><?php include __DIR__ . '/../../public/images/site-images/icons/hide-view.php'; ?></span>
                    <span class="password-view view-password"><?php include __DIR__ . '/../../public/images/site-images/icons/view.php'; ?></span>
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
                    <input required type="password" name="new_password" id="new_password" value="<?php echo htmlspecialchars($new_password); ?>" pattern="^(?=.*[0-9])(?=.*[a-zA-Z])(.+){6,}$">
                    <span class="password-view hide-password-new hidden"><?php include __DIR__ . '/../../public/images/site-images/icons/hide-view.php'; ?></span>
                    <span class="password-view view-password-new"><?php include __DIR__ . '/../../public/images/site-images/icons/view.php'; ?></span>
                    <span class="error-msg hidden">Please match the requirements</span>
                </div>
            </div>
            <div class="large-input">
                <label for="confirm_password">Confirm New Password: </label><br>
                <input required type="password" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>" id="confirm_password">
                <span class="error-msg hidden">Passwords must match</span>
            </div>

            <p class="large-input"><input type="submit" class="button text" id="password_submit_button" name="password_submit_button" value="Change Password"></p>
        </div>
    </form>
    <br />

    <!-- Forgot Password Form -->
    <h3>Forgot Your Password?</h3>
    <?php if (empty($user['email'])): ?>
        <p>In order for you to reset a password that you don't know, an email with a password reset link needs to be sent to you. Please set up your email above before trying to reset your password</p>
    <?php else: ?>
        <form method='POST' action=''>
            <p class='large-input'><input type='submit' class='button text' id='forgot_password_submit_button' name='forgot_password_submit_button' value='Send Reset Email' /></p>
        </form>
    <?php endif; ?>
</div>

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
