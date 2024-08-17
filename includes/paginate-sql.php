<?php
$offset = ($pageNumber - 1) * $itemsPerPage;
if($type == "wisher"){
    $selectQuery = $db->select("$query LIMIT $itemsPerPage OFFSET $offset", [$wishlist_id, $username]);
    $findPriceTotal = $db->select("SELECT SUM(items.price) AS total_price FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ?", [$wishlist_id, $username]);
    if($findPriceTotal->num_rows > 0){
        while($row = $findPriceTotal->fetch_assoc()){
            $total_price = $row["total_price"] != "" ? round($row["total_price"], 2) : "";
        }
    }
}else if($type == "buyer"){
    $selectQuery = $db->select("$query LIMIT $itemsPerPage OFFSET $offset", [$wishlist_id]);
}
?>