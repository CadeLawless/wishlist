<?php
if($account_created){
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . "/../../public/images/site-images/menu-close.php");
                echo "</a>
            </div>
            <div class='popup-content'>
                <p>Welcome to Wish List! Please check your email to complete your account registration.</p>
            </div>
        </div>
    </div>";
}
?>

<?php if($user): ?>
    <!-- Authenticated user content -->
    <div class="big-buttons-container">
        <a class="big-button create-wish-list" href="/wishlist/create"><?php require(__DIR__ . "/../../public/images/site-images/icons/plus.php"); ?>Create Wish List</a>
        <a class="big-button view-wish-list" href="/wishlist/wishlists"><?php require(__DIR__ . "/../../public/images/site-images/icons/search.php"); ?>View Wish Lists</a>
    </div>
<?php else: ?>
    <!-- Guest user content -->
    <div class="big-buttons-container">
        <a class="big-button login-button" href="/wishlist/login"><?php require(__DIR__ . "/../../public/images/site-images/icons/view.php"); ?>Login</a>
        <a class="big-button register-button" href="/wishlist/register"><?php require(__DIR__ . "/../../public/images/site-images/icons/plus.php"); ?>Create Account</a>
    </div>
<?php endif; ?>

<script src="js/popup.js"></script>
