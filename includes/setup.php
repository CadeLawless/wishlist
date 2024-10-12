<?php
session_start();
$file_path = isset($ajax_file) && $ajax_file == true ? ".." : "includes";
$logged_in = $_SESSION["wishlist_logged_in"] ?? false;
ini_set("display_errors", 1);
require("$file_path/classes.php");
require("$file_path/error-functions.php");
// database connection
$db = new DB();

// check to see if already logged in
require("$file_path/autologin.php");

// if not logged in redirect to login.php
if(!$logged_in) header("Location: login.php");

// get username
$username = $_SESSION["username"] ?? "";

// find name based off of username
$findName = $db->select("SELECT name, dark FROM wishlist_users WHERE username = ?", [$username]);
if($findName->num_rows > 0){
    while($row = $findName->fetch_assoc()){
        $name = htmlspecialchars($row["name"]);
        $_SESSION["name"] = $name;
        $dark = $row["dark"] == "Yes" ? true : false;
    }
}
?>