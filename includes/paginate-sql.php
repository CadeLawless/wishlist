<?php
$image_folder = match($ajax){
    true => "../../images",
    default => "images",
};

$offset = ($pageNumber - 1) * $itemsPerPage;
$selectQuery = $db->select("$query LIMIT $itemsPerPage OFFSET $offset", $values);
if($type == "wisher"){
    $findPriceTotal = $db->select("SELECT SUM(items.price * items.quantity) AS total_price FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ?", $values);
    if($findPriceTotal->num_rows > 0){
        while($row = $findPriceTotal->fetch_assoc()){
            $total_price = $row["total_price"] != "" ? round($row["total_price"], 2) : "";
        }
    }
}
?>