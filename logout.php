<?php
session_start();
unset($_COOKIE['session_id']);
setcookie('session_id', "", 1); // empty value and old timestamp
session_unset();
session_destroy();
setcookie("PHPSESSID", "", 1);
header("Location: index.php");
?>