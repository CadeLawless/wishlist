<?php
// get wishlist id from SESSION
$wishlistID = $_SESSION["wishlist_id"] ?? false;
if(!$wishlistID) header("Location: index.php");

// find wishlist year and type
$findWishlistInfo = $db->select("SELECT id, type, wishlist_name, year, duplicate, theme FROM wishlists WHERE username = ? AND id = ?", "si", [$username, $wishlistID]);
if($findWishlistInfo->num_rows > 0){
    while($row = $findWishlistInfo->fetch_assoc()){
        $year = $row["year"];
        $type = $row["type"];
        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
        $wishlistTitle = htmlspecialchars($row["wishlist_name"]);
    }
}else{
    header("Location: index.php");
}
?>