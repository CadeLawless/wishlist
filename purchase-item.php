<?php
session_start();
ini_set("display_errors", 1);
require("includes/classes.php");
// database connection
$db = new DB();

// get item id from URL
$itemID = $_GET["id"] ?? "";

// delete item from list
if($db->write("UPDATE items SET purchased = 'Yes' WHERE id = ?", "i", [$itemID])){
    header("Location: buyer-view.php");
}else{
    echo "<script>alert('Something went wrong while trying to delete this item from your wishlist')</script>";
    // echo $db->error();
}
?>