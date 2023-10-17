<?php
session_start();
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
    <title>Cade's Christmas Wishlist</title>
</head>
<body>
    <div id="container">
        <h1 class="center">Cade's Christmas Wishlist</h1>
        <?php
        echo "<h2 class='center'>All Items</h2>";
        if(isset($_GET["pageno"])){
            $pageno = $_GET["pageno"];
        }else{
            $pageno = 1;
        }
        paginate("buyer", $db, "SELECT * FROM items", 12, $pageno);
        ?>
    </div>
</body>
</html>