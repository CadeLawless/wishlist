<?php
session_start();
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

$_SESSION["type"] = "buyer";

// get wishlist key from URL
$wishlistKey = $_GET["key"] ?? "";
if($wishlistKey == "") header("Location: no-wishlist-found.php");

$item_purchased = isset($_SESSION["purchased"]) ? true : false;
if($item_purchased) unset($_SESSION["purchased"]);

// find wishlist based off of key
$findWishlistInfo = $db->select("SELECT id, username, year, type, duplicate, wishlist_name, theme_background_id, theme_gift_wrap_id FROM wishlists WHERE secret_key = ?", [$wishlistKey]);
if($findWishlistInfo->num_rows > 0){
    while($row = $findWishlistInfo->fetch_assoc()){
        $wishlistID = $row["id"];
        $_SESSION["buyer_wishlist_id"] = $wishlistID;
        $username = $row["username"];
        $year = $row["year"];
        $type = $row["type"];
        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
        $wishlistTitle = htmlspecialchars($row["wishlist_name"].$duplicate);
        $theme_background_id = $row["theme_background_id"];
        $theme_gift_wrap_id = $row["theme_gift_wrap_id"];
        $findBackground = $db->select("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_background_id]);
        if($findBackground->num_rows > 0){
            while($bg_row = $findBackground->fetch_assoc()){
                $background_image = $bg_row["theme_image"];
                $_SESSION["buyer_background_image"] = $background_image;
            }
        }
        $findGiftWrap = $db->select("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_gift_wrap_id]);
        if($findGiftWrap->num_rows > 0){
            while($gw_row = $findGiftWrap->fetch_assoc()){
                $wrap_image = $gw_row["theme_image"];
                $_SESSION["buyer_wrap_image"] = $wrap_image;
            }
        }
    }
}else{
    header("Location: no-wishlist-found.php");
}

$pageno = $_GET["pageno"] ?? 1;

$_SESSION["home"] = "buyer-view.php?key=$wishlistKey&pageno=$pageno#paginate-top";

// find name based off of username
$findName = $db->select("SELECT name FROM wishlist_users WHERE username = ?", [$username]);
if($findName->num_rows > 0){
    while($row = $findName->fetch_assoc()){
        $name = htmlspecialchars($row["name"]);
        $_SESSION["name"] = $name;
    }
}

// initialize filter variables
require("includes/filter-options.php");
$sort_priority = $_SESSION["buyer_sort_priority"] ?? "1";
$sort_price = $_SESSION["buyer_sort_price"] ?? "";
$_SESSION["buyer_sort_priority"] = $sort_priority;
$_SESSION["buyer_sort_price"] = $sort_price;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <script src="https://cdn.jsdelivr.net/npm/tsparticles-confetti@2.10.0/tsparticles.confetti.bundle.min.js"></script>
    <title><?php echo $wishlistTitle; ?></title>
    <style>
        #body {
            padding-top: 100px;
        }
        h1 {
            display: inline-block;
        }
        h2.items-list-title {
            position: relative;
        }
        .header .title {
            flex-basis: 100%;
        }
        .menu-links, .hamburger-menu, .close-menu {
            display: none !important;
        }
        .popup.fullscreen .gift-wrap-content .popup-content {
            max-height: calc(100% - 184px);
        }
        #container .background-theme.mobile-background {
            display: none;
        }
        @media (max-width: 600px){
            #container .background-theme.mobile-background {
                display: block;
            }
            #container .background-theme.desktop-background {
                display: none;
            }
        }
        @media (max-width: 460px){
            .popup.fullscreen .gift-wrap-content .popup-content {
                max-height: calc(100% - 223px);
            }
        }
        @media (max-width: 284px){
            .popup.fullscreen .gift-wrap-content .popup-content {
                max-height: calc(100% - 254px);
            }
        }
    </style>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
            <?php
            if($item_purchased){
                echo "
                <div class='popup-container'>
                    <div class='popup active'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require("images/site-images/menu-close.php");
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <p><label>Thank you for purchasing an item off $name's wish list! It has been wrapped and placed ";
                            echo $type == "Christmas" ? "under the tree (or at the end of the list)" : "at the end of the list";
                            echo ".</label></p>
                        </div>
                    </div>
                </div>";
            }
            ?>
            <img class='background-theme desktop-background' src="images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
            <img class='background-theme mobile-background' src="images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
            <div class="center"><h1 class="center transparent-background"><?php echo $wishlistTitle; ?></h1></div>
            <div class='items-list-container'>
                <h2 class="transparent-background items-list-title" id='paginate-top' class='center'>All Items</h2>
                <?php
                require("includes/sort.php");
                $findItems = $db->select("SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? ORDER BY purchased ASC, $priority_order$price_order date_added DESC", [$wishlistID]);
                if($findItems->num_rows > 0){
                    require("includes/write-filters.php");
                }
                echo "<div class='items-list-sub-container'>";
                paginate(type: "buyer", db: $db, query: "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? ORDER BY purchased ASC, $priority_order$price_order date_added DESC", itemsPerPage: 12, pageNumber: $pageno, values: [$wishlistID], wishlist_id: $wishlistID, wishlist_key: $wishlistKey);
                echo "</div>";
                ?>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script src="includes/popup.js"></script>
<script>$type = "buyer"; $key_url = "<?php echo "key=$wishlistKey&"; ?>";</script>
<script src="includes/page-change.js"></script>
<script src="includes/filter-change.js"></script>
<script>
    $(document).ready(function(){
        // confetti on mark as purchased
        $(document.body).on("click", ".purchase-button", function(e){
            e.preventDefault();
            let button = this;
            let item_id = button.id.split("-")[1];
            setTimeout(function(){
                let pageno_url = (document.querySelector(".page-number")) ? "&pageno="+document.querySelector(".page-number").textContent : "";
                window.location = "purchase-item.php?id=" + item_id+pageno_url;
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
            button.style.backgroundColor = "var(--accent)";
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
                    image: [
                        <?php if($type == "Christmas"){
                            for($i=1; $i<=6; $i++){
                                echo "
                                {
                                    src: 'images/site-images/confetti/christmas-confetti-$i.png',
                                    width: 100,
                                    height: 100,
                                },";
                            }
                    }elseif($type == "Birthday"){
                        for($i=1; $i<=9; $i++){
                            echo "
                            {
                                src: 'images/site-images/confetti/birthday-confetti-$i.png',
                                width: 125,
                                height: 125,
                            },";
                        }
                    } ?>
                    ],
                },
                scalar: 3,
                zIndex: 1002,
                disableForReducedMotion: true,
            });
        });
    });
</script>
