<?php
session_start();
$logged_in = $_SESSION["logged_in"] ?? false;
ini_set("display_errors", 1);
require("../classes.php");
// database connection
$db = new DB();
$type = $_SESSION["type"];
$host = match($type){
    "wisher" => "view-wishlist",
    "buyer" => "buyer-view",
    default => "",
};

//Getting value of "search" variable from "script.js".
if(isset($_POST["new_page"])) {
    require("../ajax-find-sql.php");
    require("../write-items-list.php");
}
?>