<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// gets wishlist id from session and wishlist info from database
require "includes/wishlist-setup.php";

// get item id from URL
$itemID = $_GET["id"] ?? "";
$deleteAll = $_GET["deleteAll"] ?? "";

$pageno = $_GET["pageno"] ?? 1;

// get item image name
$findItemImage = $db->select("SELECT image, copy_id FROM items WHERE id = ?", [$itemID]);
if($findItemImage->num_rows > 0){
    while($row = $findItemImage->fetch_assoc()){
        $image = $row["image"];
        $copy_id = $row["copy_id"];
    }
}

if($deleteAll == "yes" && $copy_id != ""){
    $where_sql = "copy_id = ?";
    $values = [$copy_id];
}else{
    $where_sql = "id = ? AND wishlist_id = ?";
    $values = [$itemID, $wishlistID];
}

// delete item from list
if($db->write("DELETE FROM items WHERE $where_sql", $values)){
    if(file_exists("images/item-images/$wishlistID/$image")){
        if(unlink("images/item-images/$wishlistID/$image")){
            header("Location: view-wishlist.php?id=$wishlistID&pageno=$pageno");
        }else{
            echo "<script>alert('Something went wrong while trying to delete this item from your wishlist')</script>";
        }
    }else{
        header("Location: view-wishlist.php?id=$wishlistID&pageno=$pageno");
    }
}else{
    echo "<script>alert('Something went wrong while trying to delete this item from your wishlist')</script>";
    // echo $db->error();
}
?>