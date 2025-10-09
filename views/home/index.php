<?php
if($account_created){
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require("images/site-images/menu-close.php");
                echo "</a>
            </div>
            <div class='popup-content'>
                <p>Welcome to Wish List! Please check your email to complete your account registration.</p>
            </div>
        </div>
    </div>";
}
?>
<div class="big-buttons-container">
    <a class="big-button create-wish-list" href="/wishlist/create"><?php require("images/site-images/icons/plus.php"); ?>Create Wish List</a>
    <a class="big-button view-wish-list" href="/wishlist"><?php require("images/site-images/icons/search.php"); ?>View Wish Lists</a>
</div>

<script src="includes/popup.js"></script>
