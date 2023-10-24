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
        <?php require "includes/background.php"; ?>
        <h1 class="center">Cade's Christmas Wishlist</h1>
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
</script>