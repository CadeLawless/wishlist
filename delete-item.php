<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// gets wishlist id from session and wishlist info from database
require "includes/wishlist-setup.php";

// get item id from URL
$itemID = $_GET["id"] ?? "";

// get item image name
$findItemImage = $db->select("SELECT image FROM items WHERE id = ?", "i", [$itemID]);
if($findItemImage->num_rows > 0){
    while($row = $findItemImage->fetch_assoc()){
        $image = $row["image"];
    }
}

// delete item from list
if($db->write("DELETE FROM items WHERE id = ? AND wishlist_id = ?", "ii", [$itemID, $wishlistID])){
    if(file_exists("images/item-images/$wishlistID/$image")){
        if(unlink("images/item-images/$wishlistID/$image")){
            header("Location: view-wishlist.php?id=$wishlistID");
        }else{
            echo "<script>alert('Something went wrong while trying to delete this item from your wishlist')</script>";
        }
    }else{
        header("Location: view-wishlist.php?id=$wishlistID");
    }
}else{
    echo "<script>alert('Something went wrong while trying to delete this item from your wishlist')</script>";
    // echo $db->error();
}
?>