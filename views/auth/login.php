<p class="center"><img class="logo login" src="public/images/site-images/logo.png" alt="Wish List" /></p>
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
            <span class="password-view hide-password hidden"><?php require(__DIR__ . "/../../public/images/site-images/icons/hide-view.php"); ?></span>
            <span class="password-view view-password"><?php require(__DIR__ . "/../../public/images/site-images/icons/view.php"); ?></span>
        </div>
    </div>
    <div class="large-input">
        <input type="checkbox" id="remember_me" name="remember_me" <?php if($remember_me) echo "checked"; ?> />
        <label style="float: none; font-weight: normal;" for="remember_me">Remember me</label>
    </div>
    <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Login"></p>
    <p style="font-size: 14px" class="large-input center"><a style="font-size: inherit;" href="/forgot-password">Forgot password?</a></p>
    <p style="font-size: 14px" class="large-input center">Don't have an account? <a style="font-size: inherit;" href="/wishlist/register">Create one here</a></p>
</form>

<script src="/wishlist/public/js/form-validation.js"></script>
<script>
$(document).ready(function(){
    // Initialize form validation
    FormValidator.init('#login-form', {
        username: {
            required: true
        },
        password: {
            required: true
        }
    });

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
