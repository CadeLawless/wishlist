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
if(!$passwordEntered) header("Location: index.php");

// get item id from URL
$itemID = $_GET["id"] ?? "";

// find item information
$findItemInformation = $db->select("SELECT name, notes, price, link, image, date_added FROM items WHERE id = ?", "i", [$itemID]);
if($findItemInformation->num_rows > 0){
    while($row = $findItemInformation->fetch_assoc()){
        $name = htmlspecialchars($row["name"]);
        $notes = htmlspecialchars($row["notes"]);
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
    <title><?php echo $name; ?></title>
</head>
<body>
    <div id="container">
        <a href="index.php">Back to Home</a>
        <h1 class="center"><?php echo $name; ?></h1>
        <div class="center flex view-item-container">
            <div class="item-image-container">
                <img class="view-item-image" src="images/<?php echo $image; ?>" alt="item image">
            </div>
            <div class="view-item-content">
                <label>Item Name: </label><?php echo $name; ?><br>
                <label>Item Price: </label>$<?php echo $price; ?><br>
                <label>Website Link: </label><a target="_blank" href="<?php echo $link; ?>">View on Website</a><br>
                <label>Notes: </label><br>
                <?php echo $notes; ?><br>
                <label>Date Added: </label><?php echo $date_added; ?><br>
            </div>
        </div>
    </div>
</body>
</html>