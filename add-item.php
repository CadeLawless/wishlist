<?php
session_start();
$_SESSION["password_entered"] = $_SESSION["password_entered"] ?? false;
$passwordEntered = $_SESSION["password_entered"];
if(!$passwordEntered) header("Location: index.php");
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

// intialize form field variables
$name = "";
$price = "";
$link = "";
$image = "";
$notes = "";

// if submit button is clicked
if(isset($_POST["submit_button"])){
    $errors = false;
    $errorTitle = "<b>The form could not be submitted due to the following errors:</b>";
    $errorList = "";
    $name = errorCheck("name", "Item Name", "Yes", $errors, $errorList);
    $price = errorCheck("price", "Item Price", "Yes", $errors, $errorList);
    patternCheck("/(?=.*?\d)^(([1-9]\d{0,2}(,\d{3})*)|\d+)?(\.\d{1,2})?$/", $price, $errors, $errorList, "Item Price must match U.S. currency format: 9,999.00");
    $link = errorCheck("link", "Item URL", "Yes", $errors, $errorList);
    $notes = errorCheck("notes", "Not Required");
    $filename = "";
    if(!$errors){
        if(isset($_FILES["item_image"]["name"])){
            $allowed = ["jpg", "jpeg", "png"];
            if($_FILES["item_image"]["name"] != ""){
                    $target_dir = "images/";
                    $file = $_FILES['item_image']['name'];
                    $ext = pathinfo($file, PATHINFO_EXTENSION);
                    $ext = strtolower($ext);
                    if(in_array($ext, $allowed)){
                        $filename = substr($name, 0, 10) . ".$ext";
                        $temp_name = $_FILES['item_image']['tmp_name'];
                        $path_filename = $target_dir.$filename;
                        if(file_exists($path_filename)){
                            unlink($path_filename);
                        }
                        move_uploaded_file($temp_name, $path_filename);
                    }else{
                        $errors = true;
                        $error_list .= "<li>Item Image file type must match: jpg, jpeg, png</li>";
                    }
            }
        }
    }
    if($filename == ""){
        $errors = true;
        $errorList .= "<li>Item Image is a required field. Please select a file.</li>";
    }
    $date_added = date("Y-m-d H:i:s");
    if(!$errors){
        if($db->write("INSERT INTO items(name, price, link, image, notes, purchased, date_added) VALUES(?, ?, ?, ?, ?, 'No', '$date_added')", "sssss", [$name, $price, $link, $filename, $notes])){
            header("Location: index.php");
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
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <title>Cade's Christmas Wishlist | Add Item</title>
</head>
<body>
    <div id="container">
        <h1 class="center">Cade's Christmas Wishlist</h1>
        <h2>Add Item</h2>
        <?php if(isset($errorMsg)) echo $errorMsg?>
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="flex form-flex">
                <div class="large-input">
                    <label for="name">Item Name:<br></label>
                    <textarea name="name" id="name" rows="1" placeholder="New Gaming PC"><?php echo $name?></textarea>
                </div>
                <div class="large-input">
                    <label for="price">Item Price:<br></label>
                    <div id="price-input-container">
                        <span class="dollar-sign-input flex">
                            <label for="price"><span class="dollar-sign">$</span></label>
                            <input type="text" name="price" pattern="(?=.*?\d)^(([1-9]\d{0,2}(,\d{3})*)|\d+)?(\.\d{1,2})?$" value="<?php echo $price?>" id="price" class="price-input" required>
                        </span>
                        <span class="error-msg hidden">Item Price must match U.S. currency format: 9,999.00</span>
                    </div>
                </div>
                <div class="large-input">
                    <label for="link">Item URL:<br></label>
                    <input type="text" name="link" id="link" value="<?php echo $link?>" placeholder="https://example.com">
                </div>
                <div class="large-input">
                    <label for="image">Item Image:<br></label>
                    <input type="file" name="item_image" id="image" accept=".png, .jpg, .jpeg">
                </div>
                <div class="large-input">
                    <label for="notes">Item Notes:<br></label>
                    <textarea name="notes" placeholder="Needs to have 16GB RAM" id="notes" rows="4"><?php echo $notes?></textarea>
                </div>
                <p class="large-input center"><input type="submit" name="submit_button" value="Add Item"></p>
            </div>
        </form>
    </div>
</body>
</html>
<script src="scripts/autosize-master/autosize-master/dist/autosize.js"></script>
<script>
    <?php if(!$passwordEntered){?>
    // password pop up on click of buttons
    document.querySelector("#add-item").addEventListener("click", function(){
        document.querySelector("#add-popup").classList.remove("hidden");
    });
    document.querySelector("#view-list").addEventListener("click", function(){
        document.querySelector("#view-popup").classList.remove("hidden");
    });

    // close popup on click of x button
    for(const x of document.querySelectorAll(".close-button")){
        x.addEventListener("click", function(){
            x.parentElement.parentElement.classList.add("hidden");
        })
    }
    <?php }?>

    // autosize textareas
    for(const textarea of document.querySelectorAll("textarea")){
        autosize(textarea);
    }
</script>