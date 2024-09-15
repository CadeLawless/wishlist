<?php
$pageNumber = $_POST["new_page"];
$itemsPerPage = 12;
$wishlist_id = match($type){
    "wisher" => $_SESSION["wisher_wishlist_id"],
    "buyer" => $_SESSION["buyer_wishlist_id"],
    default => "",
};
if($wishlist_id == "") header("Location: index.php");
$_SESSION["home"] = "$host.php?id=$wishlist_id&pageno=$pageNumber#paginate-top";
$username = $_SESSION["username"];
$sort_priority = $_SESSION["sort_priority"];
$sort_price = $_SESSION["sort_price"];
require("../sort.php");
if($type == "wisher"){
    $query = "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ? ORDER BY $priority_order$price_order date_added DESC";
}else{
    $query = "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? ORDER BY purchased ASC, $priority_order$price_order date_added DESC";
}
require("../paginate-sql.php");
?>