<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// gets wishlist id from session and wishlist info from database
require "includes/wishlist-setup.php";

// get item id from URL
$itemID = $_GET["id"] ?? "";

// delete item from list
if($db->write("DELETE FROM items WHERE id = ? AND wishlist_id = ?", "ii", [$itemID, $wishlistID])){
    header("Location: view-wishlist.php?id=$wishlistID");
}else{
    echo "<script>alert('Something went wrong while trying to delete this item from your wishlist')</script>";
    // echo $db->error();
}
?>