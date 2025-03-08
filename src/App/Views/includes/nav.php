<?php
$currentPage = explode("?", $_SERVER["REQUEST_URI"])[0];
?>
<div class="header-container">
    <div class="header">
        <div class="title">
            <a class="nav-title" href="index.php"><?php require("$homeDir/wishlist/public/assets/images/site-images/logo.php"); ?></a>
            <a href="#" class="dark-mode-link"><?php require("$homeDir/wishlist/public/assets/images/site-images/icons/dark-mode.php"); ?></a>
            <a href="#" class="light-mode-link"><?php require("$homeDir/wishlist/public/assets/images/site-images/icons/light-mode.php"); ?></a>
        </div>
        <div class="menu">
            <?php
            require("$homeDir/wishlist/public/assets/images/site-images/hamburger-menu.php");
            require("$homeDir/wishlist/public/assets/images/site-images/menu-close.php");
            ?>
            <div class="menu-links">
                <a class="nav-link<?php if($currentPage == "/wishlist/") echo " active"; ?>" href="/wishlist/index">Home<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/wishlist/create-wishlist") echo " active"; ?>" href="/wishlist/create-wishlist">Create Wishlist<div class="underline"></div></a>
                <a class="nav-link<?php if(in_array($currentPage, ["/wishlist/view-wishlists", "/wishlist/view-wishlist", "/wishlist/add-item", "/wishlist/edit-item"])) echo " active"; ?>" href="/wishlist/view-wishlists">View Wishlists<div class="underline"></div></a>
                <div class="nav-link dropdown-link profile-link<?php if(in_array($currentPage, ["/wishlist/admin-center", "/wishlist/view-profile", "/wishlist/backgrounds", "/wishlist/edit-user", "/wishlist/add-background","/wishlist/edit-background", "/wishlist/gift-wraps", "/wishlist/add-gift-wrap", "/wishlist/edit-gift-wrap", "/wishlist/wishlists"])) echo " active-page"; ?>">
                    <div class="outer-link">
                        <span class="profile-icon"><?php require("$homeDir/wishlist/public/assets/images/site-images/profile-icon.php"); ?></span>
                        <span>My Account</span>
                        <span class="dropdown-arrow"><?php require("$homeDir/wishlist/public/assets/images/site-images/dropdown-arrow.php"); ?></span>
                    </div>
                    <div class="underline"></div>
                    <div class="dropdown-menu hidden">
                        <a class="dropdown-menu-link" href="/wishlist/view-profile">View Profile</a>
                        <?php if(isset($user) && $user->admin){ ?>
                            <a class="dropdown-menu-link" href="/wishlist/admin-center">Admin Center</a>
                        <?php } ?>
                        <a class="dropdown-menu-link" href="/wishlist/logout">Log Out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.querySelector(".hamburger-menu").addEventListener("click", function(){
        this.classList.add("hidden");
        document.querySelector(".close-menu").classList.remove("hidden");
        document.querySelector(".menu-links").style.display = "flex";
        document.querySelector(".menu-links").classList.remove("hidden");
    });
    document.querySelector(".close-menu").addEventListener("click", function(){
        this.classList.add("hidden");
        document.querySelector(".hamburger-menu").classList.remove("hidden");
        document.querySelector(".menu-links").classList.add("hidden");
    });
    window.addEventListener("click", function(e){
        if(document.querySelector(".menu-links").style.display == "flex"){
            if(!e.target.classList.contains("header") && !document.querySelector(".header").contains(e.target)){
                document.querySelector(".close-menu").click();
            }
        }
        if(!document.querySelector(".dropdown-menu").classList.contains("hidden")){
            if(!e.target.classList.contains("dropdown-menu") && !e.target.classList.contains("dropdown-link") && !document.querySelector(".dropdown-link").contains(e.target)){
                document.querySelector(".dropdown-link").click();
            }
        }
    });
    document.querySelector(".dropdown-link").addEventListener("click", function(e){
        let dropdown_menu = this.querySelector(".dropdown-menu");
        if(dropdown_menu.classList.contains("hidden")){
            dropdown_menu.classList.remove("hidden");
            this.classList.add("active");
        }else{
            if(!e.target.classList.contains("dropdown-menu-link")){
                dropdown_menu.classList.add("hidden");
                this.classList.remove("active");
            }
        }
    });
</script>
