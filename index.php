<?php
session_start();
$_SESSION["password_entered"] = $_SESSION["password_entered"] ?? false;
$passwordEntered = $_SESSION["password_entered"];
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

// check to see if already logged in
require("includes/autologin.php");

// find correct password
$findCorrectPassword = $db->query("SELECT wisher_password FROM passwords");
if($findCorrectPassword->num_rows > 0){
    while($row = $findCorrectPassword->fetch_assoc()){
        $correctPassword = $row["wisher_password"];
    }
}

// initialize filter variables
$valid_options = ["", "1", "2"];
$sort_priority = $_SESSION["sort_priority"] ?? "1";
$sort_price = $_SESSION["sort_price"] ?? "";

// if form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // if add item password submit button is clicked
    if(isset($_POST["add_submit"])){
        $errors = false;
        $errorTitle = "<b>The form could not be submitted due to the following errors:</b>";
        $errorList = "";
        $password = errorCheck("password", "Password", "Yes", $errors, $errorList);
        if(!$errors){
            if($password == $correctPassword){
                $expire_date = date("Y-m-d H:i:s", strtotime("+1 year"));
                if($db->write("UPDATE passwords SET session = ?, session_expiration = ?", "ss", [session_id(), $expire_date])){
                    $cookie_time = (3600 * 24 * 365); // 1 year
                    setcookie("session_id", session_id(), time() + $cookie_time);
                    $_SESSION["password_entered"] = true;
                    $_SESSION["home"] = "index.php";
                    header("Location: add-item.php");
                }
            }else{
                $errors = true;
                $errorList .= "<li>The password you entered was incorrect. Please try again.";
                $addErrorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
            }
        }else{
            $addErrorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
        }
    }
    // if view list password submit button is clicked
    if(isset($_POST["view_submit"])){
        $errors = false;
        $errorTitle = "<b>The form could not be submitted due to the following errors:</b>";
        $errorList = "";
        $password = errorCheck("password", "Password", "Yes", $errors, $errorList);
        if(!$errors){
            if($password == $correctPassword){
                $expire_date = date("Y-m-d H:i:s", strtotime("+1 year"));
                if($db->write("UPDATE passwords SET session = ?, session_expiration = ?", "ss", [session_id(), $expire_date])){
                    $cookie_time = (3600 * 24 * 365); // 1 year
                    setcookie("session_id", session_id(), time() + $cookie_time);
                    $_SESSION["password_entered"] = true;
                    $_SESSION["home"] = "index.php";
                    header("Location: index.php");
                }
            }else{
                $errors = true;
                $errorList .= "<li>The password you entered was incorrect. Please try again.";
                $viewErrorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
            }
        }else{
            $viewErrorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
        }
    }

    if($passwordEntered){
        // if filter value is changed, change session value
        $sort_priority = $_POST["sort_priority"];
        $sort_price = $_POST["sort_price"];
        if(in_array($sort_priority, $valid_options)){
            $_SESSION["sort_priority"] = $sort_priority;
        }
        if(in_array($sort_price, $valid_options)){
            $_SESSION["sort_price"] = $sort_price;
        }
        header("Location: index.php");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <link rel="stylesheet" type="text/css" href="css/snow.css" />
    <title>Cade's Christmas Wishlist</title>
</head>
<body>
    <div id="body">
        <?php require "includes/background.php"; ?>
        <h1 class="center">Cade's Christmas Wishlist</h1>
        <?php if($passwordEntered){ ?>
            <a class="logout-button" href="logout.php">Log out</a>
        <?php } ?>
        <div id="container">
            <p class="center">
                <a id="add-item" <?php if($passwordEntered) echo "href='add-item.php'"?>>Add Item to Wishlist</a>
            </p>
            <?php if(!$passwordEntered){?>
                <p class="center">
                    <a id="view-list">View Wishlist</a>
                </p>
                <div id="add-popup" class="popup-container flex <?php if(!isset($addErrorMsg)) echo "hidden"; ?>">
                    <div class="popup flex">
                        <img src="images/site-images/close.png" class="close-button">
                        <form method="POST" action="">
                            <div class="center">
                                <?php if(isset($addErrorMsg)) echo $addErrorMsg; ?>
                                <label for="add-password">Enter Password:<br></label>
                                <input type="password" name="password" id="add-password">
                            </div>
                            <p class="center"><input type="submit" name="add_submit" class="submit_button" value="Submit"></p>
                        </form>
                    </div>
                </div>
                <div id="view-popup" class="popup-container flex <?php if(!isset($viewErrorMsg)) echo "hidden"; ?>">
                    <div class="popup flex">
                        <img src="images/site-images/close.png" class="close-button">
                        <form method="POST" action="">
                            <div class="center">
                                <?php if(isset($viewErrorMsg)) echo $viewErrorMsg; ?>
                                <label for="view-password">Enter Password:<br></label>
                                <input type="password" name="password" id="view-password">
                            </div>
                            <p class="center"><input type="submit" name="view_submit" class="submit_button" value="Submit"></p>
                        </form>
                    </div>
                </div>
            <?php 
            }else{ ?>
                <h2 class='center'>All Items</h2>
                <form class="filter-form" method="POST" action="">
                    <div class="filter-input">
                        <label for="sort-priority">Sort by Priority</label><br>
                        <select id="sort-priority" name="sort_priority">
                            <option value="">None</option>
                            <option value="1" <?php if($sort_priority == "1") echo "selected"; ?>>Highest to Lowest</option>
                            <option value="2" <?php if($sort_priority == "2") echo "selected"; ?>>Lowest to Highest</option>
                        </select>
                    </div>
                    <div class="filter-input">
                        <label for="sort-price">Sort by Price</label><br>
                        <select id="sort-price" name="sort_price">
                            <option value="">None</option>
                            <option value="1" <?php if($sort_price == "1") echo "selected"; ?>>Lowest to Highest</option>
                            <option value="2" <?php if($sort_price == "2") echo "selected"; ?>>Highest to Lowest</option>
                        </select>
                    </div>
                </form>
                <h2 class='center'>All Items</h2>
                <?php
                $findPriceTotal = $db->query("SELECT SUM(price) AS total_price FROM items");
                if($findPriceTotal->num_rows > 0){
                    while($row = $findPriceTotal->fetch_assoc()){
                        $total_price = round($row["total_price"], 2);
                    }
                }
                echo "<h3 class='center'>Current Wishlist Total: $$total_price</h3>";
                if(isset($_SESSION["pageno"])){
                    $pageno = $_SESSION["pageno"];
                }else{
                    $pageno = 1;
                }
                $priority_order = match ($sort_priority) {
                    "" => "",
                    "1" => "priority ASC, ",
                    "2" => "priority DESC, ",
                };
                $price_order = match ($sort_price) {
                    "" => "",
                    "1" => "price * 1 ASC, ",
                    "2" => "price * 1 DESC, ",
                };
                paginate("wisher", $db, "SELECT * FROM items ORDER BY $priority_order$price_order date_added DESC", 12, $pageno);
            }
            ?>
        </div>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
<script>
    <?php if(!$passwordEntered){?>
    // password pop up on click of buttons
    document.querySelector("#add-item").addEventListener("click", function(){
        document.querySelector("#add-popup").classList.remove("hidden");
        document.querySelector("#add-password").focus();

    });
    document.querySelector("#view-list").addEventListener("click", function(){
        document.querySelector("#view-popup").classList.remove("hidden");
        document.querySelector("#view-password").focus();

    });

    // close popup on click of x button
    for(const x of document.querySelectorAll(".close-button")){
        x.addEventListener("click", function(){
            x.parentElement.parentElement.classList.add("hidden");
            document.querySelector("#add-password").value = "";
            document.querySelector("#view-password").value = "";
        })
    }
    <?php }else{ ?>
        // open delete popup for specified item on click of delete button
        for(const del of document.querySelectorAll(".delete-button")){
            del.addEventListener("click", function(){
                document.querySelector(".delete-popup-" + del.id).classList.remove("hidden");
            });
        }

        // close popup on click of x or no button
        for(const x of document.querySelectorAll(".close-button")){
            x.addEventListener("click", function(){
                x.parentElement.parentElement.classList.add("hidden");
            })
        }
        for(const x of document.querySelectorAll(".no-button")){
            x.addEventListener("click", function(){
                x.parentElement.parentElement.parentElement.parentElement.classList.add("hidden");
            })
        }
    <?php } ?>
    // submit form on filter change
    for(const sel of document.querySelectorAll("select")){
        sel.addEventListener("change", function(){
            document.querySelector("form").submit();
        });
    }

</script>