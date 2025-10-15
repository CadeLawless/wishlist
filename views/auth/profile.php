<?php
$pageTitle = 'Wish List | View Profile';
$bodyClass = 'profile-page';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/wishlist/public/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/wishlist/public/css/styles.css" />
    <link rel="stylesheet" type="text/css" href="/wishlist/public/css/snow.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <title><?php echo $pageTitle; ?></title>
    <style>
        #body {
            padding-top: 84px;
        }
        h1 {
            display: inline-block;
            margin-top: 0;
        }
        h2 {
            font-size: 28px;
        }
        .form-container {
            margin: clamp(20px, 4vw, 60px) auto 30px;
            background-color: var(--background-darker);
            max-width: 500px;
        }
        input:not([type=submit], #new_password, #current_password) {
            margin-bottom: 0;
        }
        h3 {
            margin-bottom: 0.5em;
        }
    </style>
</head>
<body class="<?php echo $bodyClass; ?>">
    <div id="body">
        <?php 
        // Include header without JavaScript (we'll handle it in the page)
        $currentPage = explode("?", $_SERVER["REQUEST_URI"])[0];
        ?>
        <div class="header-container">
            <div class="header">
                <div class="title">
                    <a class="nav-title" href="/wishlist/"><?php require("images/site-images/logo.php"); ?></a>
                    <a href="#" class="dark-mode-link"><?php require("images/site-images/icons/dark-mode.php"); ?></a>
                    <a href="#" class="light-mode-link"><?php require("images/site-images/icons/light-mode.php"); ?></a>
                </div>
                <div class="menu">
                    <?php
                    require("images/site-images/hamburger-menu.php");
                    require("images/site-images/menu-close.php");
                    ?>
                    <div class="menu-links">
                        <a class="nav-link<?php if($currentPage == "/wishlist/" || $currentPage == "/wishlist") echo " active"; ?>" href="/wishlist/">Home<div class="underline"></div></a>
                        <a class="nav-link<?php if($currentPage == "/wishlist/create") echo " active"; ?>" href="/wishlist/create">Create Wishlist<div class="underline"></div></a>
                        <a class="nav-link<?php if($currentPage == "/wishlist/wishlists") echo " active"; ?>" href="/wishlist/wishlists">View Wishlists<div class="underline"></div></a>
                        <div class="nav-link dropdown-link profile-link<?php if(in_array($currentPage, ["/profile", "/admin"])) echo " active-page"; ?>">
                            <div class="outer-link">
                                <span class="profile-icon"><?php require("images/site-images/profile-icon.php"); ?></span>
                                <span>My Account</span>
                                <span class="dropdown-arrow"><?php require("images/site-images/dropdown-arrow.php"); ?></span>
                            </div>
                            <div class="underline"></div>
                            <div class="dropdown-menu hidden">
                                <a class="dropdown-menu-link" href="/wishlist/profile">View Profile</a>
                                <?php if(isset($user['role']) && $user['role'] == 'Admin'){ ?>
                                    <a class="dropdown-menu-link" href="/wishlist/admin">Admin Center</a>
                                <?php } ?>
                                <a class="dropdown-menu-link" href="/wishlist/logout">Log Out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="container">
            <?php include __DIR__ . '/../components/alerts.php'; ?>
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
        </div>
    </div>
</body>
<footer>
  <p class="center">&copy; <?php echo date("Y"); ?> Wishlist.<br>
  Designed by Cade and Meleah Lawless. All rights reserved.</p>
</footer>
<script src="/wishlist/public/js/popups.js"></script>
<script>
  $(document).ready(function(){
    // Dark mode functionality
    $(".dark-mode-link, .light-mode-link").on("click", function(e){
      e.preventDefault();
      $(document.body).toggleClass("dark");

      $dark = $(document.body).hasClass("dark") ? "Yes" : "No";
      $.ajax({
            type: "POST",
            url: "/wishlist/toggle-dark-mode",
            data: {
                dark: $dark,
            },
            success: function(response) {
                // Dark mode toggle successful
            },
            error: function(xhr, status, error) {
                console.error('Dark mode toggle failed:', error);
            }
        });
    });

    // Header dropdown functionality
    $(".hamburger-menu").on("click", function(){
        $(this).addClass("hidden");
        $(".close-menu").removeClass("hidden");
        $(".menu-links").css("display", "flex").removeClass("hidden");
    });
    
    $(".close-menu").on("click", function(){
        $(this).addClass("hidden");
        $(".hamburger-menu").removeClass("hidden");
        $(".menu-links").addClass("hidden");
    });
    
    $(window).on("click", function(e){
        if($(".menu-links").css("display") == "flex"){
            if(!$(e.target).hasClass("header") && !$(".header").has(e.target).length){
                $(".close-menu").click();
            }
        }
        if(!$(".dropdown-menu").hasClass("hidden")){
            if(!$(e.target).hasClass("dropdown-menu") && !$(e.target).hasClass("dropdown-link") && !$(".dropdown-link").has(e.target).length){
                $(".dropdown-link").click();
            }
        }
    });
    
    $(".dropdown-link").on("click", function(e){
        let dropdown_menu = $(this).find(".dropdown-menu");
        if(dropdown_menu.hasClass("hidden")){
            dropdown_menu.removeClass("hidden");
            $(this).addClass("active");
        }else{
            if(!$(e.target).hasClass("dropdown-menu-link")){
                dropdown_menu.addClass("hidden");
                $(this).removeClass("active");
            }
        }
    });
  });
</script>
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
</html>
