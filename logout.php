<?php
session_start();
unset($_COOKIE['wishlist_session_id']);
setcookie('wishlist_session_id', "", 1); // empty value and old timestamp
session_unset();
session_destroy();
setcookie("PHPSESSID", "", 1);
header("Location: login.php");
?>