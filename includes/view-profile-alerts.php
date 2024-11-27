<?php
if($name_updated){
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require("images/site-images/menu-close.php");
                echo "</a>
            </div>
            <div class='popup-content'>
                <p>Name updated successfully.</p>
            </div>
        </div>
    </div>";
}
if($role_updated){
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require("images/site-images/menu-close.php");
                echo "</a>
            </div>
            <div class='popup-content'>
                <p>Role updated successfully.</p>
            </div>
        </div>
    </div>";
}
if($password_changed){
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require("images/site-images/menu-close.php");
                echo "</a>
            </div>
            <div class='popup-content'>
                <p>Password changed successfully.</p>
            </div>
        </div>
    </div>";
}
if($email_needs_verified){
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require("images/site-images/menu-close.php");
                echo "</a>
            </div>
            <div class='popup-content'>
                <p>An email has been sent to you. Please click the link in it to complete your email setup.</p>
            </div>
        </div>
    </div>";
}
if($reset_email_sent){
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require("images/site-images/menu-close.php");
                echo "</a>
            </div>
            <div class='popup-content'>
                <p>A reset email has been sent to you. Please click the link in it to reset your password.</p>
            </div>
        </div>
    </div>";
}
?>
