<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

require("includes/write-theme-popup.php");

// initialize form field variables
$wishlist_type = "";
$wishlist_type_options = ["Birthday", "Christmas"];
$theme_background_id = "";
$theme_gift_wrap_id = "";
$wishlist_name = "";

if(isset($_POST["submit_button"])){
    $errors = false;
    $errorTitle = "<strong>The wishlist could not be created due to the following errors:</strong>";
    $errorList = "";

    $wishlist_type = errorCheck("wishlist_type", "Type", "Yes", $errors, $errorList);
    validOptionCheck($wishlist_type, "Type", $wishlist_type_options, $errors, $errorList);

    $theme_background_id = $_POST["theme_background_id"];
    $theme_gift_wrap_id = $_POST["theme_gift_wrap_id"];

    $wishlist_name = errorCheck("wishlist_name", "Name", "Yes", $errors, $errorList);

    if(!$errors){
        // function that generates random string
        function generateRandomString($length) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[random_int(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        // generate random key for wishlist
        $unique = false;
        while(!$unique){
            $secret_key = generateRandomString(10);
            // check to make sure that key doesn't exist for another wishlist in the database
            $checkKey = $db->select("SELECT secret_key FROM wishlists WHERE secret_key = ?", [$secret_key]);
            if($checkKey->num_rows == 0) $unique = true;
        }

        // get year for wishlist
        $currentYear = date("Y");
        $year = date("m/d/Y") >= "12/25/$currentYear" ? $currentYear + 1 : $currentYear;

        // find if there is a duplicate type and year in database
        $findDuplicates = $db->select("SELECT id FROM wishlists WHERE type = ? AND wishlist_name = ? AND username = ?", [$wishlist_type, $wishlist_name, $username]);
        $duplicateValue = $findDuplicates->num_rows;

        $today = date("Y-m-d H:i:s");

        // create new christmas wishlist for user
        if($db->write("INSERT INTO wishlists(type, wishlist_name, theme_background_id, theme_gift_wrap_id, year, duplicate, username, secret_key, date_created) VALUES(?, ?, ?, ?, ?, ?, ?, ?, '$today')", [$wishlist_type, $wishlist_name, $theme_background_id, $theme_gift_wrap_id, $year, $duplicateValue, $username, $secret_key])){
            $wishlistID = $db->insert_id();
            header("Location: view-wishlist.php?id=$wishlistID");
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
    <title>Create a Wish List</title>
</head>
<?php require("includes/body-open-tag.php"); ?>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
            <div>
                <h1>New Wish List</h1>
                <form method="POST" action="">
                    <?php echo $errorMsg ?? ""; ?>
                    <div class="flex form-flex">
                        <div class="large-input">
                            <label for="wishlist_type">Type:<br/></label>
                            <select required name="wishlist_type" id="wishlist_type">
                                <option value="" disabled <?php if($wishlist_type == "") echo "selected"; ?>>Select an option</option>
                                <option value="Birthday" <?php if($wishlist_type == "Birthday") echo "selected"; ?>>Birthday</option>
                                <option value="Christmas" <?php if($wishlist_type == "Christmas") echo "selected"; ?>>Christmas</option>
                            </select>
                        </div>
                        <div class="large-input">
                            <label for="theme">Theme:</label><br />
                            <a style="margin-bottom: 10px;" class="choose-theme-button button primary popup-button<?php if($wishlist_type == "") echo " disabled"; ?>" href="#">Choose a theme...<span class="inline-popup<?php if($wishlist_type != "") echo " hidden"; ?>">Please select a type</span></a>
                            <?php
                            write_theme_popup(type: "birthday");
                            write_theme_popup(type: "christmas");
                            ?>
                            <div class="theme-results">
                                <div class="theme-background-display desktop-background-display"></div>
                                <div class="theme-background-display mobile-background-display"></div>
                                <div class="theme-gift-wrap-display"></div>
                            </div>
                            <input type="hidden" id="theme_background_id" name="theme_background_id" value="<?php echo $theme_background_id; ?>" />
                            <input type="hidden" id="theme_gift_wrap_id" name="theme_gift_wrap_id" value="<?php echo $theme_gift_wrap_id; ?>" />
                        </div>
                        <div class="large-input">
                            <label for="wishlist_name">Name:<br/></label>
                            <input required type="text" id="wishlist_name" autocapitalize="words" name="wishlist_name" value="<?php echo htmlspecialchars($wishlist_name); ?>" />
                        </div>
                        <div class="large-input">
                            <p class="center"><input type="submit" class="button text" name="submit_button" id="submitButton" value="Create" /></p>
                        </div>
                    </div>
                </form>
            </div>
            <?php include "includes/footer.php"; ?>
        </div>
    </div>
</body>
</html>
<script src="includes/popup.js"></script>
<script src="includes/choose-theme.js"></script>
<script>
    let name_input = document.querySelector("#wishlist_name");
    name_input.addEventListener("focus", function(){
        this.select();
    });

    let type_select = document.querySelector("#wishlist_type");
    type_select.addEventListener("change", function(){
        let current_year = new Date().getFullYear();
        if(this.value == "Birthday"){
            name_input.value = "<?php echo $name; ?>'s " + current_year + " Birthday Wish List";
            $(".popup-container.birthday").insertBefore(".popup-container.christmas");
        }else if(this.value == "Christmas"){
            name_input.value = "<?php echo $name; ?>'s " + current_year + " Christmas Wish List";
            $(".popup-container.christmas").insertBefore(".popup-container.birthday");
        }
        if(this.value != ""){
            document.querySelector(".choose-theme-button").classList.remove("disabled");
        }else{
            document.querySelector(".choose-theme-button").classList.add("disabled");
        }
    });

    let submit_button = document.querySelector("#submitButton");
    // on submit, disable submit so user cannot press submit twice
    document.querySelector("form").addEventListener("submit", function(e){
        setTimeout( () => {
            submit_button.setAttribute("disabled", "");
            submit_button.value = "Creating...";
            submit_button.style.cursor = "default";
        });
    });
</script>