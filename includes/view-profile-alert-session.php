<?php
$password_changed = isset($_SESSION["password_changed"]) ? true : false;
if($password_changed) unset($_SESSION["password_changed"]);

$name_updated = isset($_SESSION["name_updated"]) ? true : false;
if($name_updated) unset($_SESSION["name_updated"]);

$role_updated = isset($_SESSION["role_updated"]) ? true : false;
if($role_updated) unset($_SESSION["role_updated"]);

$email_needs_verified = isset($_SESSION["email_needs_verified"]) ? true : false;
if($email_needs_verified) unset($_SESSION["email_needs_verified"]);

$reset_email_sent = isset($_SESSION["reset_email_sent"]) ? true : false;
if($reset_email_sent) unset($_SESSION["reset_email_sent"]);
?>