<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// get wishlist id from SESSION/URL
$wishlistID = $_GET["id"] ?? false;
if(!$wishlistID) header("Location: index.php");
$_SESSION["wishlist_id"] = $wishlistID;

// find wishlist year and type
$findWishlistInfo = $db->select("SELECT id, type, year, duplicate FROM wishlists WHERE username = ? AND id = ?", "si", [$username, $wishlistID]);
if($findWishlistInfo->num_rows > 0){
    while($row = $findWishlistInfo->fetch_assoc()){
        $year = $row["year"];
        $type = $row["type"];
        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
        $wishlistTitle = "$name's $year $type Wishlist$duplicate";
    }
}else{
    header("Location: index.php");
}

// initialize filter variables
$valid_options = ["", "1", "2"];
$sort_priority = $_SESSION["sort_priority"] ?? "1";
$sort_price = $_SESSION["sort_price"] ?? "";

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
    header("Location: ?id=$wishlistID");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title><?php echo $wishlistTitle; ?></title>
</head>
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <h1 class="center"><?php echo $wishlistTitle; ?></h1>
        <p><a class="logout-button" href="logout.php">Log out</a></p>
        <p><a id="back-home" href="index.php">Back to Home</a></p>
        <div id="container">
            <p class="center"><a id="add-item" href='add-item.php'>Add Item to Wishlist</a></p>
            <p class="center"><a id="back-home" class="delete-wishlist">Delete Wishlist</a></p>
            <div class='popup-container delete-wishlist-popup flex hidden'>
                <div class='popup flex'>
                    <div class='close-container'>
                        <img src='images/site-images/close.png' class='close-button'>
                    </div>
                    <div class='center'>
                        <label>Are you sure you want to delete this wishlist?</label>
                        <p><?php echo $wishlistTitle; ?></p>
                        <p class='center'><a class='red_button no-button'>No</a><a class='green_button' href='delete-wishlist.php'>Yes</a></p>
                    </div>
                </div>
            </div>
            <h2 class='center'>All Items</h2>
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
            <?php
            if(isset($_SESSION["pageno"])){
                $pageno = $_SESSION["pageno"];
            }else{
                $pageno = 1;
            }
            $priority_order = match ($sort_priority) {
                "" => "",
                "1" => "priority ASC, ",
                "2" => "priority DESC, ",
            };
            $price_order = match ($sort_price) {
                "" => "",
                "1" => "price * 1 ASC, ",
                "2" => "price * 1 DESC, ",
            };
            paginate("wisher", $db, "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ? ORDER BY $priority_order$price_order date_added DESC", 12, $pageno);
            ?>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script>
    // open delete popup for specified item on click of delete button
    for(const del of document.querySelectorAll(".delete-button")){
        del.addEventListener("click", function(){
            document.querySelector(".delete-popup-" + del.id).classList.remove("hidden");
        });
    }
    document.querySelector(".delete-wishlist").addEventListener("click", function(){
        document.querySelector(".delete-wishlist-popup").classList.remove("hidden");
    });

    // close popup on click of x or no button
    for(const x of document.querySelectorAll(".close-button")){
        x.addEventListener("click", function(){
            x.parentElement.parentElement.parentElement.classList.add("hidden");
        })
    }
    for(const x of document.querySelectorAll(".no-button")){
        x.addEventListener("click", function(){
            x.parentElement.parentElement.parentElement.parentElement.classList.add("hidden");
        })
    }

    // submit form on filter change
    for(const sel of document.querySelectorAll("select")){
        sel.addEventListener("change", function(){
            document.querySelector("form").submit();
        });
    }
</script>