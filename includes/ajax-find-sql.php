<?php
$pageNumber = $_POST["new_page"];
$itemsPerPage = match($type){
    "wisher", "buyer" => 12,
    default => 10,
};

if($type == "buyer"){
    $wishlist_key = $_GET["key"] ?? "";
    if($wishlist_key == "") header("Location: no-wishlist-found.php");
    $findWishlistInfo = $db->select("SELECT id, username FROM wishlists WHERE secret_key = ?", [$wishlist_key]);
    if($findWishlistInfo->num_rows > 0){
        while($row = $findWishlistInfo->fetch_assoc()){
            $_SESSION["buyer_wishlist_id"] = $row["id"];
            $wisher_username = $row["username"];
        }
    }else{
        header("Location: no-wishlist-found.php");
    }

    $findName = $db->select("SELECT name FROM wishlist_users WHERE username = ?", [$wisher_username]);
    if($findName->num_rows > 0){
        while($row = $findName->fetch_assoc()){
            $name = htmlspecialchars($row["name"]);
            $_SESSION["name"] = $name;
        }
    }

}

$wishlist_id = match($type){
    "wisher" => $_SESSION["wisher_wishlist_id"],
    "buyer" => $_SESSION["buyer_wishlist_id"],
    default => "",
};
if(in_array($type, ["wisher", "buyer"]) && $wishlist_id == "") header("Location: index.php");
$_SESSION["home"] = match($type){
    "wisher" => "view-wishlist.php?id=$wishlist_id&pageno=$pageNumber#paginate-top",
    "buyer" => "buyer-view.php?key=$wishlist_key&pageno=$pageNumber#paginate-top",
    "users" => "admin-center.php?pageno=$pageNumber",
    "backgrounds" => "backgrounds.php?pageno=$pageNumber",
};
$username = $_SESSION["username"];
if($type == "wisher"){
    $sort_priority = $_SESSION["wisher_sort_priority"];
    $sort_price = $_SESSION["wisher_sort_price"];
}elseif($type == "buyer"){
    $sort_priority = $_SESSION["buyer_sort_priority"];
    $sort_price = $_SESSION["buyer_sort_price"];
}
if(in_array($type, ["wisher", "buyer"])) require("../sort.php");
if($type == "wisher"){
    $values = [$wishlist_id, $username];
    $query = "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ? ORDER BY $priority_order$price_order date_added DESC";
}elseif($type == "buyer"){
    $values = [$wishlist_id];
    $query = "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? ORDER BY purchased ASC, $priority_order$price_order date_added DESC";
}elseif($type == "users"){
    $values = [];
    $query = "SELECT name, username, email, role FROM wishlist_users";
}elseif($type == "backgrounds"){
    $values = [];
    $query = "SELECT * FROM themes WHERE theme_type = 'Background' ORDER BY theme_name";
}
require("../paginate-sql.php");
?>