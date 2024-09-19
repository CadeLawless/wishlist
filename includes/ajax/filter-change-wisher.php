<?php
// includes db and paginate class and checks if logged in
$ajax_file = true;
require "../setup.php";
$wishlistID = $_SESSION["wisher_wishlist_id"];
$type = "wisher";

require("../filter-change-ajax.php");
?>