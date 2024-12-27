<?php
session_start();
$logged_in = $_SESSION["logged_in"] ?? false;
ini_set("display_errors", 1);
require("../classes.php");
// database connection
$db = new DB();
$ajax = true;

//Getting value of "search" variable from "script.js".
if(isset($_POST["wishlist_id"], $_POST["copy_from"])) {
    $copy_from = $_POST["copy_from"] == "Yes" ? true : false;
    $other_wishlist_id = $_POST["wishlist_id"];
    if($copy_from){
        $copy_from_select_all = "Yes";
    }else{
        $copy_to_select_all = "Yes";
    }
    require("../find-other-items.php");
}
?>