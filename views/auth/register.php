<p class="center"><img class="logo login" src="public/images/site-images/logo.png" alt="Wish List" /></p>
<form id="login-form" method="POST" action="">
    <p><a href="/wishlist/login">Back to Login</a></p>
    <?php if(isset($error_msg)) echo $error_msg?>
    <div class="large-input center">
        <label for="name">First Name: </label><br>
        <input required type="text" name="name" placeholder="Dwight" value="<?php echo $name?>" id="name" maxlength="50">
    </div>
    <div class="large-input center">
        <label for="username">Username: </label><br>
        <input required type="text" name="username" placeholder="belsnickel123" value="<?php echo $username?>" id="username">
    </div>
    <div class="large-input center">
        <label for="email">Email: </label><br>
        <input required type="email" name="email" placeholder="dwightkschrute@dundermifflin.com" value="<?php echo $email?>" id="email">
    </div>
    <div class="large-input">
        <div style="margin: 0 0 22px;">
            Password Requirements:
            <ul>
                <li>Must be at least 8 characters long</li>
                <li>Must contain at least one uppercase letter</li>
                <li>Must contain at least one lowercase letter</li>
                <li>Must contain at least one number</li>
            </ul>
        </div>
    </div>
    <div class="large-input center">
        <label for="password">Password: </label><br>
        <div class="password-input">
            <input required type="password" name="password" id="password" value="<?php echo $password?>" pattern="^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(.+){8,}$">
            <span class="password-view hide-password hidden"><?php require(__DIR__ . "/../../public/images/site-images/icons/hide-view.php"); ?></span>
            <span class="password-view view-password"><?php require(__DIR__ . "/../../public/images/site-images/icons/view.php"); ?></span>
            <span class="error-msg hidden">Please match the requirements</span>
        </div>
    </div>
    <div class="large-input center">
        <label for="password_confirmation">Confirm Password: </label><br>
        <input required type="password" name="password_confirmation" value="<?php echo $password_confirmation?>" id="password_confirmation">
        <span class="error-msg hidden">Passwords must match</span>
    </div>
    <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Start Wishing"></p>
</form>

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

    $("#confirm_password").on("input", function(){
        if($(this).val() != $("#password").val()){
            $(this).addClass("invalid");
        }else{
            $(this).removeClass("invalid");
        }
    });
});
</script>
