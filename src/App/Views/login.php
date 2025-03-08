<?php require(__DIR__ . "/includes/header.php"); ?>

<div id="container">
    <p class="center login-logo"><?php require("$homeDir/wishlist1/public/assets/images/site-images/logo.php"); ?></p>
    <form id="login-form" style="max-width: 350px;" method="POST" action="">
        <?php
        $formValidation->printErrorMessage();
        $formValidation->printFormFields();
        ?>
        <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Login"></p>
        <p style="font-size: 14px" class="large-input center"><a style="font-size: inherit;" href="forgot-password.php">Forgot password?</a></p>
        <p style="font-size: 14px" class="large-input center">Don't have an account? <a style="font-size: inherit;" href="create-an-account.php">Create one here</a></p>
    </form>
</div>

<?php require(__DIR__ . "/includes/footer.php"); ?>