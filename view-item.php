<?php
session_start();
$_SESSION["password_entered"] = $_SESSION["password_entered"] ?? false;
$passwordEntered = $_SESSION["password_entered"];
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

// check to see if already logged in
require("includes/autologin.php");
if($_SESSION["home"] == "index.php"){
    if(!$passwordEntered) header("Location: index.php");
}

// get item id from URL
$itemID = $_GET["id"] ?? "";

// find item information
$findItemInformation = $db->select("SELECT name, notes, price, link, image, date_added FROM items WHERE id = ?", "i", [$itemID]);
if($findItemInformation->num_rows > 0){
    while($row = $findItemInformation->fetch_assoc()){
        $name = htmlspecialchars($row["name"]);
        $notes = $row["notes"] !=  "" ? htmlspecialchars($row["notes"]) : "None";
        $price = htmlspecialchars($row["price"]);
        $link = htmlspecialchars($row["link"]);
        $image = htmlspecialchars($row["image"]);
        $date_added = htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_added"])));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title><?php echo $name; ?></title>
</head>
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <h1 class="center">Cade's Christmas Wishlist</h1>
        <div id="container">
            <a id="back-home" href="<?php echo $_SESSION["home"]; ?>">Back to Home</a>
            <div class="center flex view-item-container">
                <h2 class="center"><?php echo $name; ?></h2>
                <img class="view-item-image" src="images/item-images/<?php echo $image; ?>" alt="item image">
                <div class="view-item-content">
                    <label>Item Name:<br></label><?php echo $name; ?><br>
                    <label>Item Price:<br></label>$<?php echo $price; ?><br>
                    <label>Website Link:<br></label><a target="_blank" href="<?php echo $link; ?>">View on Website</a><br>
                    <label>Notes: </label><br>
                    <?php echo $notes; ?><br>
                    <label>Date Added:<br></label><?php echo $date_added; ?><br>
                </div>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>