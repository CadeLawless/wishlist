<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// gets wishlist id from session and wishlist info from database
require "includes/wishlist-setup.php";

// get item id from URL
$itemID = $_GET["id"] ?? "";
if($itemID == "") header("Location: index.php");
$background_image = $_SESSION["wisher_background_image"] ?? "";

// find item information
$findItemInformation = $db->select("SELECT * FROM items WHERE id = ?", [$itemID]);
if($findItemInformation->num_rows > 0){
    while($row = $findItemInformation->fetch_assoc()){
        $copy_id = $row["copy_id"];
        $original_item_name = $row["name"];
        $item_name = $original_item_name;
        $notes = $row["notes"];
        $price = $row["price"];
        $quantity = $row["quantity"];
        $original_quantity = $quantity;
        $unlimited = $row["unlimited"];
        $link = $row["link"];
        $image_name = $row["image"];
        $priority = $row["priority"];
        $purchased = $row["purchased"];
    }
}
$priority_options = ["1", "2", "3", "4"];

// check for other items with same copy id
if($copy_id != ""){
    $findOtherCopies = $db->select("SELECT wishlist_id FROM items WHERE copy_id = ?", [$copy_id]);
    $numberOfOtherCopies = $findOtherCopies->num_rows - 1;
    $otherCopies = $numberOfOtherCopies > 0 ? true : false;
    $otherWishlists = [];
    if($numberOfOtherCopies > 0){
        while($row = $findOtherCopies->fetch_assoc()){
            $other_wishlist_id = $row["wishlist_id"];
            array_push($otherWishlists, $other_wishlist_id);
        }
    }else{
        $otherWishlists = [$wishlistID];
    }
}else{
    $otherCopies = false;
    $otherWishlists = [$wishlistID];
}

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
                $file = $_FILES['item_image']['name'];
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $ext = strtolower($ext);
                if(in_array($ext, $allowed)){
                    $filename = substr(preg_replace("/[^a-zA-Z0-9\-\s]/", "", $item_name), 0, 200) . ".$ext";
                    $temp_name = $_FILES['item_image']['tmp_name'];
                    $target_dir = "images/item-images/$wishlistID/";
                    $path_filename = $target_dir.$filename;
                    if(file_exists($path_filename)){
                        unlink($path_filename);
                    }
                    if(!move_uploaded_file($temp_name, $path_filename)){
                        $errors = true;
                        $errorList .= "<li>Item Image file upload failed: " . $phpFileUploadErrors[$_FILES["item_image"]["error"]] . "</li>";
                    }else{
                        if(count($otherWishlists) > 0){
                            foreach($otherWishlists as $other){
                                if($other != $wishlistID){
                                    $other_target_dir = "images/item-images/$other/";
                                    $other_path_filename = $other_target_dir.$filename;
                                    if(file_exists($other_path_filename)){
                                        unlink($other_path_filename);
                                    }
                                    if(!copy($path_filename, $other_path_filename)){
                                        $errors = true;
                                        $file_error = error_get_last();
                                        $error_type = $file_error["type"];
                                        $error_message = $file_error["message"];
                                        $errorList .= "<li>New item image ($item_name) file upload failed.<ul><li>Error Type: $error_type</li><li>Error Message: $error_message</li></ul></li>";
                                    }
        
                                }
                            }
                        }
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
            if(count($otherWishlists) > 0){
                foreach($otherWishlists as $other){
                    $target_dir = "images/item-images/$other/";
                    $original_path = $target_dir.$image_name;
                    if(file_exists($original_path)){
                        unlink($original_path);
                    }
                }
            }
        }
        if($unlimited == "Yes"){
            $purchased = "No";
        }else{
            $purchased = $quantity > $original_quantity ? "No" : $purchased;
        }
        $where_column = $otherCopies ? "copy_id" : "id";
        $item_id_sql = $otherCopies ? $copy_id : $itemID;
        if($db->write("UPDATE items SET name = ?, price = ?, quantity = ?, unlimited = ?, link = ?, image = ?, notes = ?, priority = ?, date_modified = '$date_modified', purchased = ? WHERE $where_column = ?", [$item_name, $price, $quantity, $unlimited, $link, $filename, $notes, $priority, $purchased, $item_id_sql])){
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
            <?php if($background_image != ""){ ?>
                <img class='background-theme desktop-background' src="images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>" />
                <img class='background-theme mobile-background' src="images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>" />
            <?php } ?>
            <p style="padding-top: 15px;"><a class="button accent" href="<?php echo $_SESSION["home"]; ?>">Back to List</a></p>
            <div class="center"><h1 class="transparent-background"><?php echo $wishlistTitle; ?></h1></div>
            <div class="form-container">
                <h2>Edit Item</h2>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="flex form-flex">
                        <?php
                        if(isset($errorMsg)) echo $errorMsg;
                        if($otherCopies){
                            echo "
                            <div class='alert warning'>
                                <div class='alert-content'>Note: This item has been copied to or from $numberOfOtherCopies other wish list(s). Any changes made here will be made for the item on these wish lists as well.</div>
                            </div>";
                        }
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