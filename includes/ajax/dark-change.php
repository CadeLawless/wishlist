<?php
session_start();
ini_set("display_errors", 1);
require("../classes.php");
require("../error-functions.php");
// database connection
$db = new DB();
if(isset($_SESSION["username"])){
    $username = $_SESSION["username"];
    if(isset($_POST["dark"])){
        $db->write("UPDATE wishlist_users SET dark = ? WHERE username = ?", [$_POST["dark"], $username]);
    }
}
?>