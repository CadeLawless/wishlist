<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// gets wishlist id from session and wishlist info from database
require "includes/wishlist-setup.php";

// get item id from URL
$itemID = $_GET["id"] ?? "";
if($itemID == "") header("Location: index.php");
$background_image = $_SESSION["wisher_background_image"] ?? "";
if($background_image == "") header("Location: view-wishlist.php?id=$wishlistID");

// find item information
$findItemInformation = $db->select("SELECT * FROM items WHERE id = ?", [$itemID]);
if($findItemInformation->num_rows > 0){
    while($row = $findItemInformation->fetch_assoc()){
        $original_item_name = $row["name"];
        $item_name = htmlspecialchars($original_item_name);
        $notes = htmlspecialchars($row["notes"]);
        $price = htmlspecialchars($row["price"]);
        $quantity = htmlspecialchars($row["quantity"]);
        $unlimited = $row["unlimited"];
        $link = htmlspecialchars($row["link"]);
        $image_name = htmlspecialchars($row["image"]);
        $priority = htmlspecialchars($row["priority"]);
    }
}
$priority_options = ["1", "2", "3", "4"];

$pageno = $_GET["pageno"] ?? 1;

// if submit button is clicked
if(isset($_POST["submit_button"])){
    require("includes/item-form-submit-vars.php");
    $filename = $image_name;
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
                $target_dir = "images/item-images/$wishlistID/";
                $file = $_FILES['item_image']['name'];
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $ext = strtolower($ext);
                if(in_array($ext, $allowed)){
                    $filename = substr(preg_replace("/[^a-zA-Z0-9\-\s]/", "", $item_name), 0, 200) . ".$ext";
                    $temp_name = $_FILES['item_image']['tmp_name'];
                    $path_filename = $target_dir.$filename;
                    if(file_exists($path_filename)){
                        unlink($path_filename);
                    }
                    if(!move_uploaded_file($temp_name, $path_filename)){
                        $errors = true;
                        $errorList .= "<li>Item Image file upload failed: " . $phpFileUploadErrors[$_FILES["item_image"]["error"]] . "</li>";
                    }
                }else{
                    $errors = true;
                    $errorList .= "<li>Item Image file type must match: jpg, jpeg, png, webp</li>";
                }
            }
        }
    }
    $date_modified = date("Y-m-d H:i:s");
    if(!$errors){
        if($filename != $image_name){
            $target_dir = "images/item-images/$wishlistID/";
            $original_path = $target_dir.$image_name;
            if(file_exists($original_path)){
                unlink($original_path);
            }
        }
        if($db->write("UPDATE items SET name = ?, price = ?, quantity = ?, unlimited = ?, link = ?, image = ?, notes = ?, priority = ?, date_modified = '$date_modified' WHERE id = ?", [$item_name, $price, $quantity, $unlimited, $link, $filename, $notes, $priority, $itemID])){
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
    <title><?php echo $wishlistTitle; ?> | Edit Item</title>
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
        <?php require("includes/header.php"); ?>
        <div id="container">
            <img class='background-theme desktop-background' src="images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
            <img class='background-theme mobile-background' src="images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
            <p style="padding-top: 15px;"><a class="button accent" href="<?php echo $_SESSION["home"]; ?>">Back to List</a></p>
            <div class="center"><h1 class="transparent-background"><?php echo $wishlistTitle; ?></h1></div>
            <div class="form-container">
                <h2>Edit Item</h2>
                <?php if(isset($errorMsg)) echo $errorMsg?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="flex form-flex">
                        <?php
                        $add = false;
                        require("includes/item-form.php");
                        ?>
                        <p class="large-input center"><input type="submit" class="button text" id="submit_button" name="submit_button" value="Update Item"></p>
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