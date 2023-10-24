<?php
session_start();
$_SESSION["home"] = "buyer-view.php";
$_SESSION["password_entered"] = $_SESSION["password_entered"] ?? false;
$passwordEntered = $_SESSION["password_entered"];
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title>Cade's Christmas Wishlist</title>
</head>
<body>
    <div id="body">
        <div class="snowing">
        <?php for($i=0; $i<20; $i++){ ?>
            <div class="snow small-snow"></div>
        <?php }
        for($i=0; $i<20; $i++){ ?>
            <div class="snow medium-snow"></div>
        <?php } ?>
        </div>
        <video class="background desktop-background" src="images/site-images/christmas-background.mp4" autoplay="true" muted="muted"></video>
        <video class="background mobile-background" src="images/site-images/christmas-background-mobile.mp4" playsinline autoplay="true" muted="muted"></video>
        <h1 class="center list-title">Cade's Christmas Wishlist</h1>
        <div id="container">
            <?php
            echo "<h2 class='center'>All Items</h2>";
            if(isset($_GET["pageno"])){
                $pageno = $_GET["pageno"];
            }else{
                $pageno = 1;
            }
            paginate("buyer", $db, "SELECT * FROM items ORDER BY purchased ASC", 12, $pageno);
            ?>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script>
    // open purchase popup for specified item on click of mark as purchased button
    for(const purchase of document.querySelectorAll(".purchased-button")){
        purchase.addEventListener("click", function(e){
            e.preventDefault();
            document.querySelector(".purchased-popup-" + purchase.id).classList.remove("hidden");
        });
    }

    // close popup on click of x or no button
    for(const x of document.querySelectorAll(".close-button")){
        x.addEventListener("click", function(){
            x.parentElement.parentElement.classList.add("hidden");
        })
    }
    for(const x of document.querySelectorAll(".no-button")){
        x.addEventListener("click", function(){
            x.parentElement.parentElement.parentElement.parentElement.classList.add("hidden");
        })
    }
    for(const video of document.querySelectorAll(".background")){
        video.play();
        video.addEventListener("ended", function(){
            setTimeout(function(){
                video.play();
            }, 5000);
        });
    }
</script>