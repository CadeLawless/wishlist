<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

if(!$admin) header("Location: index.php");

$pageno = $_GET["pageno"] ?? 1;
if(!is_int($pageno) && !ctype_digit($pageno)) $pageno = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <title>Wish List | Admin Center</title>
    <style>
        #container {
            padding: 0 10px 110px;
        }
    </style>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
            <h1 class="center">Admin Center</h1>
            <div class="sidebar-main">
                <?php require("includes/sidebar.php"); ?>
                <div class="content">
                <h2 style="margin: 0;" class="items-list-title">All Users</h2>
                    <?php
                    paginate(type: "users", db: $db, query: "SELECT name, username, email, role FROM wishlist_users", itemsPerPage: 10, pageNumber: $pageno, values: []);
                    ?>
                </div>
            </div>
            <?php include "includes/footer.php"; ?>
        </div>
    </div>
</body>
</html>
<script src="includes/popup.js"></script>
<script>$type = "users"; $key_url = "<?php echo ""; ?>";</script>
<script src="includes/page-change.js"></script>