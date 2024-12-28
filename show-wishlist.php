<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// gets wishlist id from session and wishlist info from database
$wishlistID = $_SESSION["wisher_wishlist_id"] ?? false;
require "includes/wishlist-setup.php";

$pageno = $_GET["pageno"] ?? 1;

// delete list database
if($db->write("UPDATE wishlists SET visibility = 'Public' WHERE username = ? AND id = ? AND complete = 'No'", [$username, $wishlistID])){
    $_SESSION["wishlist_public"] = true;
    header("Location: view-wishlist.php?id=$wishlistID&pageno=$pageno");
}else{
    echo "<script>alert('Something went wrong while trying to update the visibility for this wishlist')</script>";
    // echo $db->error();
}
?>