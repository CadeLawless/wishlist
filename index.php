<?php
session_start();
$_SESSION["password_entered"] = $_SESSION["password_entered"] ?? false;
$passwordEntered = $_SESSION["password_entered"];
ini_set("display_errors", 1);
require("includes/classes.php");
require("includes/error-functions.php");
// database connection
$db = new DB();

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
            $_SESSION["password_entered"] = true;
            header("Location: add-item.php");
        }else{
            $errors = true;
            $errorList .= "<li>The password you entered was incorrect. Please try again.";
            $errorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
        }
    }else{
        $errorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
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
            $_SESSION["password_entered"] = true;
            header("Location: index.php");
        }else{
            $errors = true;
            $errorList .= "<li>The password you entered was incorrect. Please try again.";
            $errorMsg = "<div class='submit-error'>$errorTitle<ul>$errorList</ul></div>";
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
    <title>Cade's Christmas Wishlist</title>
</head>
<body>
    <div id="container">
        <h1 class="center">Cade's Christmas Wishlist</h1>
        <div id="add-item-container">
            <a id="add-item" <?php if($passwordEntered) echo "href='add-item.php'"?>>Add Item to Wishlist</a>
        </div>
        <?php if(!$passwordEntered){?>
        <div id="view-list-wisher-container">
            <a id="view-list">View Wishlist</a>
        </div>
        <div id="add-popup" class="password-popup flex hidden">
            <div class="form-container flex">
                <img src="images/close.png" class="close-button">
                <form method="POST" action="">
                    <div class="center">
                        <label for="password">Enter Password:<br></label>
                        <input type="password" name="password" id="password">
                    </div>
                    <p class="center"><input type="submit" name="add_submit" class="submit_button" value="Submit"></p>
                </form>
            </div>
        </div>
        <div id="view-popup" class="password-popup flex hidden">
            <div class="form-container flex">
                <img src="images/close.png" class="close-button">
                <form method="POST" action="">
                    <div class="center">
                        <label for="password">Enter Password:<br></label>
                        <input type="password" name="password" id="password">
                    </div>
                    <p class="center"><input type="submit" name="view_submit" class="submit_button" value="Submit"></p>
                </form>
            </div>
        </div>
        <?php 
        }else{
            echo "<h2>All Items</h2>";
            if(isset($_GET["pageno"])){
                $pageno = $_GET["pageno"];
            }else{
                $pageno = 1;
            }
            paginate("wisher", $db, "SELECT * FROM items", 16, $pageno);
        }
        ?>
    </div>
</body>
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
    <?php }?>
</script>