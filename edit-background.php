<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

if(!$admin) header("Location: index.php");

// get item id from URL
$backgroundID = $_GET["id"] ?? "";
if($backgroundID == "") header("Location: index.php");
$pageno = $_GET["pageno"] ?? 1;

// find item information
$findBackground = $db->select("SELECT * FROM themes WHERE theme_id = ?", [$backgroundID]);
if($findBackground->num_rows > 0){
    while($row = $findBackground->fetch_assoc()){
        $tag = $row["tag"];
        $name = $row["theme_name"];
        $image_name = $row["theme_image"];
        $default_gift_wrap_id = $row["default_gift_wrap"];
    }
}
$tag_options = ["Birthday", "Christmas"];

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
        $purchased = $unlimited == "Yes" ? "No" : $purchased;
        if($db->write("UPDATE items SET name = ?, price = ?, quantity = ?, unlimited = ?, link = ?, image = ?, notes = ?, priority = ?, date_modified = '$date_modified', purchased = ? WHERE id = ?", [$item_name, $price, $quantity, $unlimited, $link, $filename, $notes, $priority, $purchased, $backgroundID])){
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
    <title>Admin Center | Edit Background</title>
    <style>
        #body {
            padding-top: 84px;
        }
        h1 {
            display: inline-block;
            margin-top: 0;
        }
        .form-container {
            margin: clamp(20px, 4vw, 60px) auto 30px;
            background-color: var(--background-darker);
            max-width: 500px;
        }
    </style>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
        <p style="padding-top: 15px;"><a class="button accent" href="backgrounds.php?pageno=<?php echo $pageno; ?>">Back to All Backgrounds</a></p>
            <div class="form-container">
                <h2>Edit Background</h2>
                <?php if(isset($errorMsg)) echo $errorMsg?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="flex form-flex">
                        <?php
                        $add = false;
                        require("includes/background-form.php");
                        ?>
                        <p class="large-input center"><input type="submit" class="button text" id="submit_button" name="submit_button" value="Update Background"></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script src="includes/item-form.js"></script>