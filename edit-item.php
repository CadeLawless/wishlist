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
        $link = htmlspecialchars($row["link"]);
        $image = htmlspecialchars($row["image"]);
        $priority = htmlspecialchars($row["priority"]);
    }
}
$priority_options = ["1", "2", "3", "4"];

$pageno = $_GET["pageno"] ?? 1;

// if submit button is clicked
if(isset($_POST["submit_button"])){
    $errors = false;
    $errorTitle = "<b>The form could not be submitted due to the following errors:</b>";
    $errorList = "";
    $item_name = errorCheck("name", "Item Name", "Yes", $errors, $errorList);
    $price = errorCheck("price", "Item Price", "Yes", $errors, $errorList);
    patternCheck("/(?=.*?\d)^(([1-9]\d{0,2}(,\d{3})*)|\d+)?(\.\d{1,2})?$/", $price, $errors, $errorList, "Item Price must match U.S. currency format: 9,999.00");
    $link = errorCheck("link", "Item URL", "Yes", $errors, $errorList);
    $notes = errorCheck("notes", "Not Required");
    $priority = errorCheck("priority", "How much do you want this item", "Yes", $errors, $errorList);
    validOptionCheck($priority, "How much do you want this item", $priority_options, $errors, $errorList);
    $filename = $image;
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
                        echo $filename;
                        $temp_name = $_FILES['item_image']['tmp_name'];
                        $path_filename = $target_dir.$filename;
                        if(file_exists($path_filename)){
                            unlink($path_filename);
                        }
                        if(!$errors){
                            if(!move_uploaded_file($temp_name, $path_filename)){
                                $errors = true;
                                $errorList .= "<li>Item Image file upload failed: " . $phpFileUploadErrors[$_FILES["item_image"]["error"]] . "</li>";
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
        if($filename != $image){
            $target_dir = "images/item-images/$wishlistID/";
            $original_path = $target_dir.$image;
            if(file_exists($original_path)){
                unlink($original_path);
            }
        }
        if($db->write("UPDATE items SET name = ?, price = ?, link = ?, image = ?, notes = ?, priority = ?, date_modified = '$date_modified' WHERE id = ?", [$item_name, $price, $link, $filename, $notes, $priority, $itemID])){
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
        #container {
            background-image: url("images/site-images/themes/desktop-backgrounds/<?php echo $background_image; ?>");
        }
        @media (max-width: 600px){
            #container {
                background-image: url("images/site-images/themes/mobile-backgrounds/<?php echo $background_image; ?>");
            }
        }
    </style>
</head>
<body>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
        <p style="padding-top: 15px;"><a class="button accent" href="<?php echo $_SESSION["home"]; ?>">Back to List</a></p>
        <h1 class="center"><?php echo $wishlistTitle; ?></h1>
            <div class="form-container">
                <h2>Edit Item</h2>
                <?php if(isset($errorMsg)) echo $errorMsg?>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="flex form-flex">
                        <div class="large-input">
                            <label for="name">Item Name:<br></label>
                            <textarea required name="name" id="name" rows="1" placeholder="New Gaming PC"><?php echo $item_name?></textarea>
                        </div>
                        <div class="large-input">
                            <label for="price">Item Price:<br></label>
                            <div id="price-input-container">
                                <span class="dollar-sign-input flex">
                                    <label for="price"><span class="dollar-sign">$</span></label>
                                    <input type="text" inputmode="decimal" name="price" pattern="(?=.*?\d)^(([1-9]\d{0,2}(,\d{3})*)|\d+)?(\.\d{1,2})?$" value="<?php echo $price?>" id="price" class="price-input" required>
                                </span>
                                <span class="error-msg hidden">Item Price must match U.S. currency format: 9,999.00</span>
                            </div>
                        </div>
                        <div class="large-input">
                            <label for="link">Item URL:<br></label>
                            <input required type="text" name="link" id="link" value="<?php echo $link?>" placeholder="https://example.com">
                        </div>
                        <div class="large-input">
                            <label for="image">Item Image:<br></label>
                            <a class="file-input">Change Item Image</a>
                            <input type="file" name="item_image" class="hidden" id="image" accept=".png, .jpg, .jpeg, .webp">
                            <div id="preview_container">
                                <img id="preview" src="images/item-images/<?php echo "$wishlistID/$image"; ?>">
                            </div>
                        </div>
                        <div class="large-input">
                            <label for="notes">Item Notes:<br></label>
                            <textarea name="notes" placeholder="Needs to have 16GB RAM" id="notes" rows="4"><?php echo $notes?></textarea>
                        </div>
                        <div class="large-input">
                            <label for="priority">How much do you want this item?</label><br>
                            <select id="priority" name="priority">
                                <option value="1" <?php if($priority == "1") echo "selected"; ?>>(1) I absolutely need this item</option>
                                <option value="2" <?php if($priority == "2") echo "selected"; ?>>(2) I really want this item</option>
                                <option value="3" <?php if($priority == "3") echo "selected"; ?>>(3) It would be cool if I had this item</option>
                                <option value="4" <?php if($priority == "4") echo "selected"; ?>>(4) Eh, I could do without this item</option>
                            </select>
                        </div>
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
<script>
    // on click of file input button, open file picker
    document.querySelector(".file-input").addEventListener("click", function(e){
        e.preventDefault();
        document.querySelector("#image").click();
    });

    // show image preview on change
    document.querySelector("#image").addEventListener("change", function(){
        if (this.files && this.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                document.querySelector("#preview").setAttribute('src', e.target.result);
            }

            reader.readAsDataURL(this.files[0]);
        }else{
            document.querySelector("#preview_container").classList.add("hidden");
        }
    });
    function readURL(input) {
        if (input.files && input.files[0]) {
            $('#preview_container').show();
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#preview').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }else{
            $('#preview_container').hide();
        }
    }
    // autosize textareas
    for(const textarea of document.querySelectorAll("textarea")){
        autosize(textarea);
    }
</script>