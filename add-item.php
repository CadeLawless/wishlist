<?php
ini_set("allow_url_fopen", 1);
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// gets wishlist id from session and wishlist info from database
$wishlistID = $_SESSION["wisher_wishlist_id"] ?? false;
require "includes/wishlist-setup.php";

$pageno = $_GET["pageno"] ?? 1;
$background_image = $_SESSION["wisher_background_image"] ?? "";

// intialize form field variables
$item_name = "";
$price = "";
$quantity = "1";
$unlimited = "No";
$link = "";
$filename = "";
$notes = "";
$priority = "";
$priority_options = ["1", "2", "3", "4"];

// if submit button is clicked
if(isset($_POST["submit_button"])){
    require("includes/item-form-submit-vars.php");
    $filename = "";
    if(!$errors){
        if(isset($_FILES["item_image"]["name"])){
            $phpFileUploadErrors = array(
                0 => 'There is no error, the file uploaded with success',
                1 => 'The uploaded file exceeds the max file size allowed',
                2 => 'The uploaded file exceeds the max file size allowed',
                3 => 'The uploaded file was only partially uploaded',
                4 => 'No file was uploaded',
                6 => 'Missing a temporary folder',
                7 => 'Failed to write file to disk.',
                8 => 'A PHP extension stopped the file upload.',
            );
            $allowed = ["jpg", "jpeg", "png", "webp"];
            if($_FILES["item_image"]["name"] != ""){
                if(!is_dir("images/item-images/$wishlistID")){
                    mkdir("images/item-images/$wishlistID");
                }
                $target_dir = "images/item-images/$wishlistID/";
                $file = $_FILES['item_image']['name'];
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $ext = strtolower($ext);
                if(in_array($ext, $allowed)){
                    $filename = substr(preg_replace("/[^a-zA-Z0-9\-\s]/", "", $item_name), 0, 200) . ".$ext";
                    $temp_name = $_FILES['item_image']['tmp_name'];
                    $path_filename = $target_dir.$filename;
                    if(file_exists($path_filename)){
                        $errors = true;
                        $errorList .= "<li>You already have an item with the name ". htmlspecialchars($filename) ." in this wishlist. Please choose a different name.</li>";
                    }
                    if(!move_uploaded_file($temp_name, $path_filename)){
                        $errors = true;
                        $errorList .= "<li>Item Image file upload failed: " . $phpFileUploadErrors[$_FILES["item_image"]["error"]] . "</li>";
                    }
                }else{
                    $errors = true;
                    $errorList .= "<li>Item Image file type must match: jpg, jpeg, png</li>";
                }
            }
        }
        if($errors){
            if(isset($path_filename) && file_exists($path_filename)){
                unlink($path_filename);
            }
        }
    }
    if($filename == ""){
        $errors = true;
        $errorList .= "<li>Item Image is a required field. Please select a file.</li>";
    }
    $date_added = date("Y-m-d H:i:s");
    if(!$errors){
        if($db->write("INSERT INTO items(wishlist_id, name, price, quantity, unlimited, link, image, notes, priority, purchased, date_added) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, 'No', '$date_added')", [$wishlistID, $item_name, $price, $quantity, $unlimited, $link, $filename, $notes, $priority])){
            header("Location: view-wishlist.php?id=$wishlistID&pageno=$pageno");
        }else{
            echo "<script>alert('Something went wrong while trying to add this item')</script>";
            // echo $db->error();
        }
    }else{
        $errorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="images/site-images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title><?php echo $wishlistTitle; ?> | Add Item</title>
    <style>
        #body {
            padding-top: 84px;
        }
        h1 {
            display: inline-block;
            margin-top: 0;
        }
        #container .background-theme.mobile-background {
            display: none;
        }
        @media (max-width: 600px){
            #container .background-theme.mobile-background {
                display: block;
            }
            #container .background-theme.desktop-background {
                display: none;
            }
        }
    </style>
    </head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require "includes/header.php"; ?>
        <div id="container">
            <?php if($background_image != ""){ ?>
                <img class='background-theme desktop-background' src="images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
                <img class='background-theme mobile-background' src="images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
            <?php } ?>
            <p style="padding-top: 15px;"><a class="button accent" href="<?php echo $_SESSION["home"]; ?>">Back to List</a></p>
            <div class="center">
                <div class="wishlist-header center transparent-background">
                    <h1><?php echo $wishlistTitle; ?></h1>
                </div>
            </div>
            <div class="form-container">
                <h2>Add Item</h2>
                <?php if(isset($errorMsg)) echo $errorMsg?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="flex form-flex">
                        <?php
                        $add = true;
                        require("includes/item-form.php");
                        ?>
                        <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Add Item"></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script src="scripts/autosize-master/autosize-master/dist/autosize.js"></script>
<script src="includes/item-form.js"></script>