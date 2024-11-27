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
    <title>Wish List | Backgrounds</title>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
            <h1 class="center">Admin Center</h1>
            <div class="sidebar-main">
                <?php require("includes/sidebar.php"); ?>
                <div class="content">
                    <h2 style="margin: 0;" class="items-list-title">All Backgrounds<a href="add-background.php?pageno=<?php echo $pageno; ?>" class="icon-container add-item">
                    <?php require("images/site-images/icons/plus.php"); ?>
                    <div class='inline-label'>Add</div></a></h2>
                    <?php
                    paginate(type: "backgrounds", db: $db, query: "SELECT * FROM themes WHERE theme_type = 'Background' ORDER BY theme_name", itemsPerPage: 10, pageNumber: $pageno, values: []);
                    ?>
                </div>
            </div>
            <?php include "includes/footer.php"; ?>
        </div>
    </div>
</body>
</html>
<script src="includes/popup.js"></script>
<script>$type = "backgrounds"; $key_url = "<?php echo ""; ?>";</script>
<script src="includes/page-change.js"></script>