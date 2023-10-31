<?php
session_start();
$logged_in = $_SESSION["logged_in"] ?? false;
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

// check to see if already logged in
require("includes/autologin.php");

// if not logged in redirect to login.php
if(!$logged_in) header("Location: login.php");

// get username
$username = $_SESSION["username"] ?? "";
?>