<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title><?php echo $name; ?>'s Wishlists</title>
</head>
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <h1 class="center"><?php echo $name; ?>'s Wishlists</h1>
        <a class="logout-button" href="logout.php">Log out</a>
        <div id="container">
            <div class="center">
                <a class="create-wishlist-button" href="create-wishlist.php">Create New Christmas Wishlist</a>
                <h2 class="center">All Wishlists</h2>
                <?php
                $findWishlists = $db->select("SELECT id, type, year, duplicate FROM wishlists WHERE username = ?", "s", [$username]);
                if($findWishlists->num_rows > 0){
                    while($row = $findWishlists->fetch_assoc()){
                        $id = $row["id"];
                        $type = $row["type"];
                        $year = $row["year"];
                        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
                        echo "<p><a class='view-list' href='view-wishlist.php?id=$id'>$name's $year $type Wishlist$duplicate</a></p>";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script>
</script>