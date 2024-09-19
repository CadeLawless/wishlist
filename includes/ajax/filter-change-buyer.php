<?php
session_start();
ini_set("display_errors", 1);
require("includes/classes.php");
require("../error-functions.php");
// database connection
$db = new DB();
$wishlistID = $_SESSION["buyer_wishlist_id"];
$type = "buyer";
// get wishlist key from URL
$wishlistKey = $_GET["key"] ?? "";
if($wishlistKey != ""){
    require("../filter-change-ajax.php");
}else{
    echo "<strong>Invalid wishlist key</strong>";
}
?>