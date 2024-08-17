<?php
// includes db and paginate class and checks if logged in
require "includes/setup.php";

// initialize form field variables
$wishlist_type = "";
$wishlist_type_options = ["Birthday", "Christmas"];
$wishlist_name = "";

if(isset($_POST["submit_button"])){
    $errors = false;
    $errorTitle = "<strong>The wishlist could not be created due to the following errors:</strong>";
    $errorList = "";

    $wishlist_type = errorCheck("wishlist_type", "Type", "Yes", $errors, $errorList);
    validOptionCheck($wishlist_type, "Type", $wishlist_type_options, $errors, $errorList);

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
            $checkKey = $db->select("SELECT secret_key FROM wishlists WHERE secret_key = ?", "s", [$secret_key]);
            if($checkKey->num_rows == 0) $unique = true;
        }

        // get year for wishlist
        $currentYear = date("Y");
        $year = date("m/d/Y") >= "12/25/$currentYear" ? $currentYear + 1 : $currentYear;

        // find if there is a duplicate type and year in database
        $findDuplicates = $db->select("SELECT id FROM wishlists WHERE type = ? AND wishlist_name = ? AND username = ?", "sss", [$wishlist_type, $wishlist_name, $username]);
        $duplicateValue = $findDuplicates->num_rows;

        // create new christmas wishlist for user
        if($db->write("INSERT INTO wishlists(type, wishlist_name, year, duplicate, username, secret_key) VALUES(?, ?, ?, ?, ?, ?)", "ssssss", [$wishlist_type, $wishlist_name, $year, $duplicateValue, $username, $secret_key])){
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
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title><?php echo $name; ?>'s Wishlists</title>
</head>
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <h1 class="center"><?php echo $name; ?>'s Wishlists</h1>
        <a class="logout-button" href="logout.php">Log out</a>
        <div id="container">
            <div class="center">
                <a class="create-wishlist-button popup-button" href="#">Create New Wishlist</a>
                <div class='popup-container<?php if(!isset($errorMsg)) echo " hidden"; ?>'>
                    <div class='popup'>
                        <div class='close-container'>
                            <img src='images/site-images/close.png' class='close-button' />
                        </div>
                        <div class='popup-content'>
                            <h2 style="margin-top: 0;">New Wishlist</h2>
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
                                        <label for="wishlist_name">Name:<br/></label>
                                        <input required type="text" id="wishlist_name" name="wishlist_name" value="<?php echo htmlspecialchars($wishlist_name); ?>" />
                                    </div>
                                    <div class="large-input">
                                        <p class="center"><input type="submit" name="submit_button" id="submitButton" value="Create" /></p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>       
                <h2 class="center">All Wishlists</h2>
                <?php
                $findWishlists = $db->select("SELECT id, type, wishlist_name, duplicate FROM wishlists WHERE username = ?", [$username]);
                if($findWishlists->num_rows > 0){
                    while($row = $findWishlists->fetch_assoc()){
                        $id = $row["id"];
                        $type = $row["type"];
                        $list_name = $row["wishlist_name"];
                        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
                        echo "<p><a class='view-list' href='view-wishlist.php?id=$id'>$list_name$duplicate</a></p>";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script src="includes/popup.js"></script>
<script>
    let name_input = document.querySelector("#wishlist_name");
    name_input.addEventListener("focus", function(){
        this.select();
    });

    let type_select = document.querySelector("#wishlist_type");
    type_select.addEventListener("change", function(){
        let current_year = new Date().getFullYear();
        if(this.value == "Birthday"){
            name_input.value = "<?php echo $name; ?>'s " + current_year + " Birthday Wishlist";
        }else if(this.value == "Christmas"){
            name_input.value = "<?php echo $name; ?>'s " + current_year + " Christmas Wishlist";
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