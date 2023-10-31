<?php
// includes db and paginate class and checks if logged in
require "inlcudes/setup.php";
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
        <a class="logout-button" href="logout.php">Log out</a>
        <div id="container">
            <h2>All Wishlists</h2>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script>
</script>