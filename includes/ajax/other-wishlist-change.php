<?php
session_start();
$logged_in = $_SESSION["logged_in"] ?? false;
ini_set("display_errors", 1);
require("../classes.php");
// database connection
$db = new DB();
$ajax = true;

//Getting value of "search" variable from "script.js".
if(isset($_POST["wishlist_id"])) {
    $other_wishlist_copy_from = $_POST["wishlist_id"];
    $copy_from_select_all = "Yes";
    $copy_from = true;
    require("../find-other-items.php");
}
?>