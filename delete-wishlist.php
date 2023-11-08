<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// gets wishlist id from session and wishlist info from database
require "includes/wishlist-setup.php";

// delete list database
$sql_errors = false;
if($db->write("DELETE FROM wishlists WHERE id = ?", "i", [$wishlistID])){
    if($db->write("DELETE FROM items WHERE wishlist_id = ?", "i", [$wishlistID])){
        header("Location: index.php");
    }else{
        $sql_errors = true;
    }
}else{
    $sql_errors = true;
}
if($sql_errors){
    echo "<script>alert('Something went wrong while trying to delete this wishlist')</script>";
    // echo $db->error();
}
?>