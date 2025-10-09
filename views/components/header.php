<?php
$currentPage = explode("?", $_SERVER["REQUEST_URI"])[0];
?>
<div class="header-container">
    <div class="header">
        <div class="title">
            <a class="nav-title" href="/"><?php require("images/site-images/logo.php"); ?></a>
            <a href="#" class="dark-mode-link"><?php require("images/site-images/icons/dark-mode.php"); ?></a>
            <a href="#" class="light-mode-link"><?php require("images/site-images/icons/light-mode.php"); ?></a>
        </div>
        <div class="menu">
            <?php
            require("images/site-images/hamburger-menu.php");
            require("images/site-images/menu-close.php");
            ?>
            <div class="menu-links">
                <a class="nav-link<?php if($currentPage == "/" || $currentPage == "/index.php") echo " active"; ?>" href="/">Home<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/wishlist/create") echo " active"; ?>" href="/wishlist/create">Create Wishlist<div class="underline"></div></a>
                <a class="nav-link<?php if(in_array($currentPage, ["/wishlist", "/wishlist/", "/wishlist/1", "/wishlist/2"])) echo " active"; ?>" href="/wishlist">View Wishlists<div class="underline"></div></a>
                <div class="nav-link dropdown-link profile-link<?php if(in_array($currentPage, ["/profile", "/admin"])) echo " active-page"; ?>">
                    <div class="outer-link">
                        <span class="profile-icon"><?php require("images/site-images/profile-icon.php"); ?></span>
                        <span>My Account</span>
                        <span class="dropdown-arrow"><?php require("images/site-images/dropdown-arrow.php"); ?></span>
                    </div>
                    <div class="underline"></div>
                    <div class="dropdown-menu hidden">
                        <a class="dropdown-menu-link" href="/profile">View Profile</a>
                        <?php if($user['admin']){ ?>
                            <a class="dropdown-menu-link" href="/admin">Admin Center</a>
                        <?php } ?>
                        <a class="dropdown-menu-link" href="/logout">Log Out</a>
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
