<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// get wishlist id from SESSION/URL
$wishlistID = $_GET["id"] ?? false;
if(!$wishlistID) header("Location: index.php");
$_SESSION["wishlist_id"] = $wishlistID;

$pageno = $_GET["pageno"] ?? 1;

$_SESSION["home"] = "view-wishlist.php?id=$wishlistID&pageno=$pageno#paginate-top";
$_SESSION["type"] = "wisher";

// find wishlist year and type
$findWishlistInfo = $db->select("SELECT id, type, wishlist_name, year, duplicate, secret_key FROM wishlists WHERE username = ? AND id = ?", [$username, $wishlistID]);
if($findWishlistInfo->num_rows > 0){
    while($row = $findWishlistInfo->fetch_assoc()){
        $year = $row["year"];
        $type = $row["type"];
        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
        $wishlistTitle = htmlspecialchars($row["wishlist_name"]);
        $secret_key = $row["secret_key"];
    }
}else{
    header("Location: index.php");
}

$pageno = $_GET["pageno"] ?? 1;

// initialize filter variables
$valid_options = ["", "1", "2"];
$sort_priority = $_SESSION["sort_priority"] ?? "1";
$sort_price = $_SESSION["sort_price"] ?? "";
$_SESSION["sort_priority"] = $sort_priority;
$_SESSION["sort_price"] = $sort_price;

// if filter is changed
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // if filter value is changed, change session value
    $sort_priority = $_POST["sort_priority"];
    $sort_price = $_POST["sort_price"];
    if(in_array($sort_priority, $valid_options)){
        $_SESSION["sort_priority"] = $sort_priority;
    }
    if(in_array($sort_price, $valid_options)){
        $_SESSION["sort_price"] = $sort_price;
    }
    header("Location: ?id=$wishlistID&pageno=$pageno#paginate-top");
}
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
        <div style="display: inline-block; width: 100%;">
            <a class="logout-button" style="float: left;" href="logout.php">Log out</a>
            <a class="create-wishlist-button" id="copy-link" style="float: right;" href="#">Copy Link to Wishlist</a>
        </div>
        <div><a id="back-home" href="index.php">Back to Home</a></div>
        <div id="container">
            <p class="center"><a id="add-item" href='add-item.php?pageno=<?php echo $pageno; ?>'>Add Item to Wishlist</a></p>
            <div class="center">
                <a class="delete-wishlist popup-button" href="#">Delete Wishlist</a>
                <div class='popup-container delete-wishlist-popup hidden'>
                    <div class='popup'>
                        <div class='close-container'>
                            <img src='images/site-images/close.png' class='close-button'>
                        </div>
                        <div class='popup-content'>
                            <label>Are you sure you want to delete this wishlist?</label>
                            <p><?php echo $wishlistTitle; ?></p>
                            <p class='center'><a class='red_button no-button'>No</a><a class='green_button' href='delete-wishlist.php'>Yes</a></p>
                        </div>
                    </div>
                </div>
            </div>
            <h2 id='paginate-top' class='center'>All Items</h2>
            <?php
            require("includes/sort.php");
            $findItems = $db->select("SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ? ORDER BY $priority_order$price_order date_added DESC", [$wishlistID, $username]);
            if($findItems->num_rows > 0){ ?>
                <form class="filter-form" method="POST" action="">
                    <div class="filter-input">
                        <label for="sort-priority">Sort by Priority</label><br>
                        <select id="sort-priority" name="sort_priority">
                            <option value="">None</option>
                            <option value="1" <?php if($sort_priority == "1") echo "selected"; ?>>Highest to Lowest</option>
                            <option value="2" <?php if($sort_priority == "2") echo "selected"; ?>>Lowest to Highest</option>
                        </select>
                    </div>
                    <div class="filter-input">
                        <label for="sort-price">Sort by Price</label><br>
                        <select id="sort-price" name="sort_price">
                            <option value="">None</option>
                            <option value="1" <?php if($sort_price == "1") echo "selected"; ?>>Lowest to Highest</option>
                            <option value="2" <?php if($sort_price == "2") echo "selected"; ?>>Highest to Lowest</option>
                        </select>
                    </div>
                </form>
            <?php }
            paginate(type: "wisher", db: $db, query: "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ? ORDER BY $priority_order$price_order date_added DESC", itemsPerPage: 6, pageNumber: $pageno, wishlist_id: $wishlistID, username: $username);
            ?>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script src="includes/popup.js"></script>
<script src="includes/page-change.js"></script>
<script>
    // submit form on filter change
    for(const sel of document.querySelectorAll("select")){
        sel.addEventListener("change", function(){
            document.querySelector("form").submit();
        });
    }

    // copy link to share
    document.querySelector("#copy-link").addEventListener("click", function(e){
        e.preventDefault();
        navigator.clipboard.writeText("https://cadelawless.com/wishlist/buyer-view.php?key=<?php echo $secret_key; ?>");
        this.textContent = "Link Copied!";
        setTimeout(() => {
            this.textContent = "Copy Link to Wishlist";
        }, 1300);
    });
</script>