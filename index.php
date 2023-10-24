<?php
session_start();
$_SESSION["home"] = "index.php";
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
                //header("Location: index.php");
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="css/styles.css" />
    <title>Cade's Christmas Wishlist</title>
</head>
<body>
    <div id="container">
        <?php if($passwordEntered){ ?>
            <a href="logout.php">Log out</a>
        <?php } ?>
        <h1 class="center">Cade's Christmas Wishlist</h1>
        <div id="add-item-container" class="center">
            <a id="add-item" <?php if($passwordEntered) echo "href='add-item.php'"?>>Add Item to Wishlist</a>
        </div>
        <?php if(!$passwordEntered){?>
            <div id="view-list-wisher-container">
                <a id="view-list">View Wishlist</a>
            </div>
            <div id="add-popup" class="popup-container flex <?php if(!isset($addErrorMsg)) echo "hidden"; ?>">
                <div class="popup flex">
                    <img src="images/close.png" class="close-button">
                    <form method="POST" action="">
                        <div class="center">
                            <?php if(isset($addErrorMsg)) echo $addErrorMsg; ?>
                            <label for="password">Enter Password:<br></label>
                            <input type="password" name="password" id="password">
                        </div>
                        <p class="center"><input type="submit" name="add_submit" class="submit_button" value="Submit"></p>
                    </form>
                </div>
            </div>
            <div id="view-popup" class="popup-container flex <?php if(!isset($viewErrorMsg)) echo "hidden"; ?>">
                <div class="popup flex">
                    <img src="images/close.png" class="close-button">
                    <form method="POST" action="">
                        <div class="center">
                            <?php if(isset($viewErrorMsg)) echo $viewErrorMsg; ?>
                            <label for="password">Enter Password:<br></label>
                            <input type="password" name="password" id="password">
                        </div>
                        <p class="center"><input type="submit" name="view_submit" class="submit_button" value="Submit"></p>
                    </form>
                </div>
            </div>
        <?php 
        }else{
            echo "<h2 class='center'>All Items</h2>";
            $findPriceTotal = $db->query("SELECT SUM(price) AS total_price FROM items");
            if($findPriceTotal->num_rows > 0){
                while($row = $findPriceTotal->fetch_assoc()){
                    $total_price = round($row["total_price"], 2);
                }
            }
            echo "<h3 class='center'>Current Wishlist Total: $$total_price</h3>";
            if(isset($_GET["pageno"])){
                $pageno = $_GET["pageno"];
            }else{
                $pageno = 1;
            }
            paginate("wisher", $db, "SELECT * FROM items", 12, $pageno);
        }
        ?>
    </div>
</body>
<?php include "includes/footer.php"; ?>
</html>
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
</script>