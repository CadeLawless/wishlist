<?php
$currentPage = $_SERVER["REQUEST_URI"];
?>
<div class="header-container">
    <div class="header">
        <div class="title">
            <a class="nav-title" href="index.php"><img class="logo" src="images/site-images/logo.png" alt="Wish List" /></a>
        </div>
        <div class="menu">
            <img src="images/site-images/hamburger-menu.png" class="hamburger-menu" />
            <img src="images/site-images/menu-close.png" class="close-menu hidden" />
            <div class="menu-links">
                <a class="nav-link<?php if($currentPage == "/wishlist/index.php") echo " active"; ?>" href="index.php">Home<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/wishlist/create-wishlist.php") echo " active"; ?>" href="create-wishlist.php">Create Wishlist<div class="underline"></div></a>
                <a class="nav-link<?php if($currentPage == "/wishlist/view-wishlists.php") echo " active"; ?>" href="view-wishlists.php">View Wishlists<div class="underline"></div></a>
                <a class="nav-link logout" href="logout.php">Log out</a>
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
    });
</script>