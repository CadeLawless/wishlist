<?php
session_start();
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

// get wishlist key from URL
$wishlistKey = $_GET["key"] ?? "";
if($wishlistKey == "") header("Location: wishlist-search.php");

// find wishlist based off of key
$findWishlistInfo = $db->select("SELECT id, username, year, type, duplicate FROM wishlists WHERE secret_key = ?", "s", [$wishlistKey]);
if($findWishlistInfo->num_rows > 0){
    while($row = $findWishlistInfo->fetch_assoc()){
        $wishlistID = $row["id"];
        $_SESSION["wishlist_id"] = $wishlistID;
        $username = $row["username"];
        $year = $row["year"];
        $type = $row["type"];
        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
    }
}else{
    header("Location: wishlist-search.php");
}

$_SESSION["home"] = "buyer-view.php?key=$wishlistKey";

// find name based off of username
$findName = $db->select("SELECT name FROM wishlist_users WHERE username = ?", "s", [$username]);
if($findName->num_rows > 0){
    while($row = $findName->fetch_assoc()){
        $name = htmlspecialchars($row["name"]);
        $_SESSION["name"] = $name;
    }
}
$wishlistTitle = "$name's $year $type Wishlist$duplicate";

// initialize filter variables
$valid_options = ["", "1", "2"];
$sort_priority = $_SESSION["sort_priority"] ?? "1";
$sort_price = $_SESSION["sort_price"] ?? "";

// if filter value is changed, change session value
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $sort_priority = $_POST["sort_priority"];
    $sort_price = $_POST["sort_price"];
    if(in_array($sort_priority, $valid_options)){
        $_SESSION["sort_priority"] = $sort_priority;
    }
    echo $_SESSION["sort_priority"];
    if(in_array($sort_price, $valid_options)){
        $_SESSION["sort_price"] = $sort_price;
    }
    header("Location: buyer-view.php");
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
    <script src="https://cdn.jsdelivr.net/npm/tsparticles-confetti@2.10.0/tsparticles.confetti.bundle.min.js"></script>
    <title><?php echo $wishlistTitle; ?></title>
</head>
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <h1 class="center"><?php echo $wishlistTitle; ?></h1>
        <div id="container">
            <h2 id='paginate-top' class='center'>All Items</h2>
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
                "1" => ", priority ASC",
                "2" => ", priority DESC",
            };
            $price_order = match ($sort_price) {
                "" => "",
                "1" => ", price * 1 ASC",
                "2" => ", price * 1 DESC",
            };
            paginate("buyer", $db, "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? ORDER BY purchased ASC$priority_order$price_order, date_added DESC", 12, $pageno);
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

    // confetti on mark as purchased
    for(const button of document.querySelectorAll(".purchase-button")){
        button.addEventListener("click", function(){
            let item_id = button.id.split("-")[1];
            setTimeout(function(){
                window.location = "purchase-item.php?id=" + item_id;
            }, 3000);
            button.style.pointerEvents = "none";
            var windowWidth = window.innerWidth;
            var windowHeight = window.innerHeight;
            let position = button.getBoundingClientRect();
            let left = position.left;
            let top = position.top;
            let centerX = left + button.offsetWidth / 2;
            centerX = centerX / windowWidth * 100;
            let centerY = top + button.offsetHeight / 2;
            centerY = centerY / windowHeight * 100;
            console.log(centerX, centerY);
            button.style.backgroundColor = "#ff7300";
            button.style.boxShadow = "0 0 0 4px dodgerblue";
            button.style.border = "2px solid white";
            confetti("tsparticles", {
                angle: 90,
                count: 200,
                position: {
                    x: centerX,
                    y: centerY,
                },
                spread: 60,
                startVelocity: 45,
                decay: 0.9,
                gravity: 1,
                drift: 0,
                ticks: 200,
                shapes: ["image"],
                shapeOptions: {
                    image: [{
                        src: "images/1.png",
                        width: 75,
                        height: 75,
                    },
                    {
                        src: "images/2.png",
                        width: 75,
                        height: 75,
                    },
                    {
                        src: "images/3.png",
                        width: 75,
                        height: 75,
                    },
                    ],
                },
                scalar: 3,
                zIndex: 100,
                disableForReducedMotion: true,
            });
        });
    }
</script>