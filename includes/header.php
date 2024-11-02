<?php
$currentPage = $_SERVER["REQUEST_URI"];
?>
<div class="header-container">
    <div class="header">
        <div class="title">
            <a class="nav-title" href="index.php"><?php require("images/site-images/logo.php"); ?></a>
            <a href="#" class="dark-mode-link"><?php require("images/site-images/icons/dark-mode.php"); ?></a>
            <a href="#" class="light-mode-link"><?php require("images/site-images/icons/light-mode.php"); ?></a>
        </div>
        <div class="menu">
            <?php
            require("images/site-images/hamburger-menu.php");
            require("images/site-images/menu-close.php");
            ?>
            <div class="menu-links">
                <a class="nav-link<?php if($currentPage == "/wishlist/index.php") echo " active"; ?>" href="index.php">Home<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/wishlist/create-wishlist.php") echo " active"; ?>" href="create-wishlist.php">Create Wishlist<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/wishlist/view-wishlists.php") echo " active"; ?>" href="view-wishlists.php">View Wishlists<div class="underline"></div></a>
                <div class="nav-link dropdown-link profile-link">
                    <span class="profile-icon"><?php require("images/site-images/profile-icon.php"); ?></span>
                    <span><?php echo $name; ?></span>
                    <span class="dropdown-arrow"><?php require("images/site-images/dropdown-arrow.php"); ?></span>
                    <div class="underline"></div>
                    <div class="dropdown-menu hidden">
                        <a class="dropdown-menu-link" href="view-profile.php">View Profile</a>
                        <a class="dropdown-menu-link" href="logout.php">Log Out</a>
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