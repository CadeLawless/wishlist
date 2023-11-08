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

// get year for wishlist
$currentYear = date("Y");
$year = date("m/d/Y") >= "12/25/$currentYear" ? $currentYear + 1 : $currentYear;

// find if there is a duplicate type and year in database
$findDuplicates = $db->select("SELECT id FROM wishlists WHERE type = ? AND year = ? AND username = ?", "sss", ["Christmas", $year, $username]);
$duplicateValue = $findDuplicates->num_rows;

// create new christmas wishlist for user
if($db->write("INSERT INTO wishlists(type, year, duplicate, username, secret_key) VALUES(?, ?, ?, ?, ?)", "sssss", ["Christmas", $year, $duplicateValue, $username, $secret_key])){
    $wishlistID = $db->insert_id();
    header("Location: view-wishlist.php?id=$wishlistID");
}