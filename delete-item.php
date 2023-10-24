<?php
session_start();
$_SESSION["password_entered"] = $_SESSION["password_entered"] ?? false;
$passwordEntered = $_SESSION["password_entered"];
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

// check to see if already logged in
require("includes/autologin.php");
if(!$passwordEntered) header("Location: index.php");

// get item id from URL
$itemID = $_GET["id"] ?? "";

// delete item from list
if($db->write("DELETE FROM items WHERE id = ?", "i", [$itemID])){
    header("Location: index.php");
}else{
    echo "<script>alert('Something went wrong while trying to delete this item from your wishlist')</script>";
    // echo $db->error();
}
?>