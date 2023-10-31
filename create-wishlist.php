<?php
session_start();
ini_set('display_errors', 'On');
date_default_timezone_set("America/Chicago");

// database connections
require("includes/classes.php");
$db = new DB();

// get username
$username = $_SESSION["username"] ?? "";

// function that generates random string
function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

// generate random key for wishlist
$unique = false;
while(!$unique){
    $secret_key = generateRandomString(10);
    // check to make sure that key doesn't exist for another wishlist in the database
    $checkKey = $db->select("SELECT secret_key FROM wishlists WHERE secret_key = ?", "s", [$secret_key]);
    if($checkKey->num_rows == 0) $unique = true;
}

// create new christmas wishlist for user
if($db->write("INSERT INTO wishlists(type, username, secret_key) VALUES(?, ?, ?)", "sss", ["Christmas", $username, $secret_key])){
    $wishlistID = $db->insert_id();
    $_SESSION["wishlist_id"] = $wishlistID;
    header("Location: view-wishlist.php?id=$wishlistID");
}