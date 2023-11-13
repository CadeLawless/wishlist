<?php
session_start();
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

// get item id from URL
$itemID = $_GET["id"] ?? "";

// find item information
$findItemInformation = $db->select("SELECT name, wishlist_id, notes, price, link, image, priority, date_added FROM items WHERE id = ?", "i", [$itemID]);
if($findItemInformation->num_rows > 0){
    while($row = $findItemInformation->fetch_assoc()){
        $wishlistID = $row["wishlist_id"];
        $item_name = htmlspecialchars($row["name"]);
        $notes = $row["notes"] !=  "" ? htmlspecialchars($row["notes"]) : "None";
        $price = htmlspecialchars($row["price"]);
        $link = htmlspecialchars($row["link"]);
        $image = htmlspecialchars($row["image"]);
        $priority = htmlspecialchars($row["priority"]);
        $date_added = htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_added"])));
    }
}

// find wishlist based off of id
$findWishlistInfo = $db->select("SELECT username, year, type, duplicate FROM wishlists WHERE id = ?", "i", [$wishlistID]);
if($findWishlistInfo->num_rows > 0){
    while($row = $findWishlistInfo->fetch_assoc()){
        $username = $row["username"];
        $year = $row["year"];
        $type = $row["type"];
        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
    }
}

// find name based off of username
$findName = $db->select("SELECT name FROM wishlist_users WHERE username = ?", "s", [$username]);
if($findName->num_rows > 0){
    while($row = $findName->fetch_assoc()){
        $name = htmlspecialchars($row["name"]);
    }
}
$wishlistTitle = "$name's $year $type Wishlist$duplicate";

$priorities = [
    1 => "$name absolutely needs this item",
    2 => "$name really wants this item",
    3 => "It would be cool if $name had this item",
    4 => "Eh, $name could do without this item"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title><?php echo $wishlistTitle; ?></title>
</head>
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <h1 class="center"><?php echo $wishlistTitle; ?></h1>
        <div id="container">
            <a id="back-home" href="<?php echo $_SESSION["home"]; ?>">Back to List</a>
            <div class="center flex view-item-container">
                <h2 class="center"><?php echo $item_name; ?></h2>
                <img class="view-item-image" src="images/item-images/<?php echo "$wishlistID/$image"; ?>" alt="item image">
                <div class="view-item-content">
                    <label>Item Name:<br></label><?php echo $item_name; ?><br>
                    <label>Item Price:<br></label>$<?php echo $price; ?><br>
                    <label>Website Link:<br></label><a target="_blank" href="<?php echo $link; ?>">View on Website</a><br>
                    <label>Notes: </label><br>
                    <?php echo $notes; ?><br>
                    <label>Priority:<br></label><?php echo "($priority) $priorities[$priority]"; ?><br>
                    <label>Date Added:<br></label><?php echo $date_added; ?><br>
                </div>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>