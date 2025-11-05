<?php
$currentPage = isset($_SERVER["REQUEST_URI"]) ? explode("?", $_SERVER["REQUEST_URI"])[0] : "/";
?>
<div class="header-container">
    <div class="header">
        <div class="title">
            <a class="nav-title" href="/"><?php require(__DIR__ . "/../../public/images/site-images/logo.php"); ?></a>
            <a href="#" class="dark-mode-link"><?php require(__DIR__ . "/../../public/images/site-images/icons/dark-mode.php"); ?></a>
            <a href="#" class="light-mode-link"><?php require(__DIR__ . "/../../public/images/site-images/icons/light-mode.php"); ?></a>
        </div>
        <div class="menu">
            <?php
            require(__DIR__ . "/../../public/images/site-images/hamburger-menu.php");
            require(__DIR__ . "/../../public/images/site-images/menu-close.php");
            ?>
            <div class="menu-links">
                <a class="nav-link<?php if($currentPage == "/" || $currentPage == "/wishlist") echo " active"; ?>" href="/">Home<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/create") echo " active"; ?>" href="/create">Create Wishlist<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/wishlists") echo " active"; ?>" href="/wishlists">View Wishlists<div class="underline"></div></a>
                <div class="nav-link dropdown-link profile-link<?php if(in_array($currentPage, ["/profile", "/admin"])) echo " active-page"; ?>">
                    <div class="outer-link">
                        <span class="profile-icon"><?php require(__DIR__ . "/../../public/images/site-images/profile-icon.php"); ?></span>
                        <span>My Account</span>
                        <span class="dropdown-arrow"><?php require(__DIR__ . "/../../public/images/site-images/dropdown-arrow.php"); ?></span>
                    </div>
                    <div class="underline"></div>
                    <div class="dropdown-menu hidden">
                        <a class="dropdown-menu-link" href="/profile">View Profile</a>
                        <?php if(isset($user['role']) && $user['role'] == 'Admin'){ ?>
                            <a class="dropdown-menu-link" href="/admin">Admin Center</a>
                        <?php } ?>
                        <a class="dropdown-menu-link" href="/logout">Log Out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
