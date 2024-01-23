<?php
ini_set("allow_url_fopen", 1);
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// gets wishlist id from session and wishlist info from database
require "includes/wishlist-setup.php";

$pageno = $_GET["pageno"] ?? 1;

// intialize form field variables
$item_name = "";
$price = "";
$link = "";
$filename = "";
$notes = "";
$priority = "";
$priority_options = ["1", "2", "3", "4"];

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
    $filename = "";
    if(!$errors){
        if(isset($_FILES["item_image"]["name"])){
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
                        $filename = substr($item_name, 0, 10) . ".$ext";
                        $temp_name = $_FILES['item_image']['tmp_name'];
                        $path_filename = $target_dir.$filename;
                        if(file_exists($path_filename)){
                            $errors = true;
                            $errorList .= "<li>You already have an item with the name ". htmlspecialchars($filename) ." in this wishlist. Please choose a different name.</li>";
                        }
                        if(!$errors) move_uploaded_file($temp_name, $path_filename);
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
        if($db->write("INSERT INTO items(wishlist_id, name, price, link, image, notes, priority, purchased, date_added) VALUES(?, ?, ?, ?, ?, ?, ?, 'No', '$date_added')", "issssss", [$wishlistID, $item_name, $price, $link, $filename, $notes, $priority])){
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
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title><?php echo $wishlistTitle; ?></title>
</head>
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <h1 class="center"><?php echo $wishlistTitle; ?></h1>
        <a id="back-home" href="view-wishlist.php?id=<?php echo $wishlistID; ?>">Back to List</a>
        <div id="container">
            <div class="form-container">
                <h2>Add Item</h2>
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
                            <a class="file-input">Choose Item Image</a>
                            <input type="file" name="item_image" class="hidden" id="image" accept=".png, .jpg, .jpeg, .webp">
                            <div class="<?php if($filename == "") echo "hidden"; ?>" id="preview_container">
                                <img id="preview" src="">
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
                        <p class="large-input center"><input type="submit" name="submit_button" value="Add Item"></p>
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
    // autosize textareas
    for(const textarea of document.querySelectorAll("textarea")){
        autosize(textarea);
    }

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
            document.querySelector("#preview_container").classList.remove("hidden");
            document.querySelector(".file-input").textContent = "Change Item Image";
        }else{
            document.querySelector("#preview_container").classList.add("hidden");
            document.querySelector(".file-input").textContent = "Choose Item Image";
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
</script>