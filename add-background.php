<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

if(!$admin) header("Location: index.php");

$pageno = $_GET["pageno"] ?? 1;

// intialize form field variables
$tag = "Birthday";
$tag_options = ["Birthday", "Christmas"];
$name = "";
$image_name = "";
$default_gift_wrap_id = "";

// if submit button is clicked
if(isset($_POST["submit_button"])){
    require("includes/background-form-submit-vars.php");
    if(!$errors){
        $files_array = ["desktop_image", "desktop_shortcut", "mobile_image", "mobile_shortcut"];
        foreach($files_array as $file_name){
            $filename = "";
            if(isset($_FILES[$file_name]["name"])){
                $folder = match($file_name){
                    "desktop_image" => "desktop-backgrounds",
                    "desktop_thumbnail" => "desktop-thumbnails",
                    "mobile_image" => "mobile-backgrounds",
                    "mobile_thumbnail" => "mobile-thumbnails",
                    default => "",
                };
                $allowed = ["png"];
                if($_FILES[$file_name]["name"] != ""){
                    $target_dir = "images/site-images/themes/$folder/";
                    $file = $_FILES[$file_name]["name"];
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    $ext = strtolower($ext);
                    if(in_array($ext, $allowed)){
                        $filename = $image_name;
                        $temp_name = $_FILES[$file_name]["tmp_name"];
                        $path_filename = $target_dir.$filename;
                        if(file_exists($path_filename)){
                            $errors = true;
                            $errorList .= "<li>You already have a background with the filename ". htmlspecialchars($filename) .". Please choose a different name.</li>";
                        }
                        if(!move_uploaded_file($temp_name, $path_filename)){
                            $errors = true;
                            $errorList .= "<li>$file_name file upload failed: " . $phpFileUploadErrors[$_FILES[$file_name]["error"]] . "</li>";
                        }
                    }else{
                        $errors = true;
                        $errorList .= "<li>$file_name file type must match: png</li>";
                    }
                }
            }
            if($filename == ""){
                $errors = true;
                $errorList .= "<li>$file_name is a required field. Please select a file.</li>";
            }
        }
        if($errors){
            foreach($files_array as $f){
                $folder = match($file_name){
                    "desktop_image" => "desktop-backgrounds",
                    "desktop_thumbnail" => "desktop-thumbnails",
                    "mobile_image" => "mobile-backgrounds",
                    "mobile_thumbnail" => "mobile-thumbnails",
                    default => "",
                };
                $file = $_FILES[$file_name]["name"];
                $target_dir = "images/site-images/themes/$folder/";
                $path_filename = $target_dir.$image_name;
                if(file_exists($path_filename)){
                    unlink($path_filename);
                }
            }
        }
    }
    if(!$errors){
        if($db->write("INSERT INTO themes(theme_type, theme_tag, theme_name, theme_image, default_gift_wrap) VALUES('Background', ?, ?, ?, ?)", [$tag, $name, $image_name, $default_gift_wrap_id])){
            $_SESSION["background_added"] = true;
            header("Location: backgrounds.php?pageno=$pageno");
        }else{
            echo "<script>alert('Something went wrong while trying to add this background')</script>";
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
    <title>Admin Center | Add Background</title>
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
        <?php require "includes/header.php"; ?>
        <div id="container">
            <p style="padding-top: 15px;"><a class="button accent" href="backgrounds.php?pageno=<?php echo $pageno; ?>">Back to All Backgrounds</a></p>
            <div class="form-container">
                <h2>Add Background</h2>
                <?php if(isset($errorMsg)) echo $errorMsg?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="flex form-flex">
                        <?php
                        $add = true;
                        require("includes/background-form.php");
                        ?>
                        <p class="large-input center"><input type="submit" class="button text" name="submit_button" value="Add Background"></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script src="includes/item-form.js"></script>