<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/wishlist1/public/assets/images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="/wishlist1/public/assets/css/styles.css" />
    <title><?= htmlspecialchars($title) ?? "Wish List"; ?></title>
</head>
<body class="<?php if(isset($user) && $user->dark_theme) echo "dark"; ?>">
    <div id="body">
