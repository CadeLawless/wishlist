<?php
session_start();
ini_set("display_errors", 1);
require("includes/classes.php");
// database connection
$db = new DB();

$home = $_SESSION["home"] ?? "";

// get item id from URL
$itemID = $_GET["id"] ?? "";
$pageno = $_GET["pageno"] ?? 1;

// find quantity and quantity purchased so far
$findQuantityInfo = $db->select("SELECT wishlist_id, quantity, unlimited, quantity_purchased FROM items WHERE id = ?", [$itemID]);
if($findQuantityInfo->num_rows > 0){
    while($row = $findQuantityInfo->fetch_assoc()){
        $wishlist_id = $row["wishlist_id"];
        $findKey = $db->select("SELECT secret_key FROM wishlists WHERE id = ?", [$wishlist_id])->fetch_all(MYSQLI_BOTH);
        if(!empty($findKey)){
            $wishlist_key = $findKey[0]["secret_key"];
        }
        $quantity = $row["quantity"];
        $unlimited = $row["unlimited"];
        $quanity_purchased = $row["quantity_purchased"];
    }

    $new_quantity_purchased = $quanity_purchased + 1;

    $purchased = ($new_quantity_purchased == $quantity && $unlimited == "No") ? "Yes" : "No";

    // delete item from list
    if($db->write("UPDATE items SET quantity_purchased = ?, purchased = ? WHERE id = ?", [$new_quantity_purchased, $purchased, $itemID])){
        header("Location: buyer-view.php?key=$wishlist_key&pageno=$pageno#paginate-top");
    }else{
        echo "<script>alert('Something went wrong while trying to delete this item from your wish list')</script>";
        // echo $db->error();
    }
}else{
    header("Location: $home");
}
?>