<?php
// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../public/images/site-images/menu-close.php');
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
                require(__DIR__ . '/../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['error']) . "</label></p>
            </div>
        </div>
    </div>";
}
?>

<p class="center"><img class="logo login" src="public/images/site-images/logo.png" alt="Wish List" /></p>

<form id="reset-password-form" method="POST" action="">
    <input type="hidden" name="key" value="<?php echo htmlspecialchars($key ?? ''); ?>" />
    <input type="hidden" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" />
    
    <h2>Reset Your Password</h2>
    
    <?php echo $error_msg ?? ''; ?>
    
    <div class="flex form-flex">
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
            <label for="password">New Password: </label><br>
            <div class="password-input">
                <input required type="password" name="password" id="password" value="" pattern="^(?=.*[0-9])(?=.*[a-zA-Z])(.+){6,}$">
                <span class="password-view hide-password hidden"><?php require(__DIR__ . '/../../public/images/site-images/icons/hide-view.php'); ?></span>
                <span class="password-view view-password"><?php require(__DIR__ . '/../../public/images/site-images/icons/view.php'); ?></span>
                <span class="error-msg hidden">Please match the requirements</span>
            </div>
        </div>
        
        <div class="large-input">
            <label for="password_confirmation">Confirm New Password: </label><br>
            <input required type="password" name="password_confirmation" value="" id="password_confirmation">
            <span class="error-msg hidden">Passwords must match</span>
        </div>
        
        <p class="large-input"><input type="submit" class="button text" id="password_submit_button" name="password_submit_button" value="Change Password"></p>
    </div>
</form>

<script src="/wishlist/public/js/form-validation.js"></script>
<script>
$(document).ready(function() {
    FormValidator.init('#reset-password-form', {
        password: {
            required: true,
            password: true
        },
        password_confirmation: {
            required: true,
            confirmPassword: '#password'
        }
    });
    
    $(".view-password").on("click", function(){
        $("#password, #password_confirmation").attr("type", "text");
        $(this).addClass("hidden");
        $(".hide-password").removeClass("hidden");
    });
    
    $(".hide-password").on("click", function(){
        $("#password, #password_confirmation").attr("type", "password");
        $(this).addClass("hidden");
        $(".view-password").removeClass("hidden");
    });
});
</script>

