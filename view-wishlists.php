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

        // create new christmas wishlist for user
        if($db->write("INSERT INTO wishlists(type, wishlist_name, theme_background_id, theme_gift_wrap_id, year, duplicate, username, secret_key) VALUES(?, ?, ?, ?, ?, ?, ?, ?)", [$wishlist_type, $wishlist_name, $theme_background_id, $theme_gift_wrap_id, $year, $duplicateValue, $username, $secret_key])){
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
    <title><?php echo $name; ?>'s Wish Lists</title>
</head>
<body>
    <div id="body">
        <?php require("includes/header.php"); ?>
        <div id="container">
            <h1 class="center"><?php echo $name; ?>'s Wish Lists</h1>
            <div class="wishlist-grid">
                <?php
                $findWishlists = $db->select("SELECT id, type, wishlist_name, duplicate, theme_background_id, theme_gift_wrap_id FROM wishlists WHERE username = ?", [$username]);
                if($findWishlists->num_rows > 0){
                    while($row = $findWishlists->fetch_assoc()){
                        $id = $row["id"];
                        $type = $row["type"];
                        $list_name = $row["wishlist_name"];
                        $duplicate = $row["duplicate"] == 0 ? "" : " ({$row["duplicate"]})";
                        $theme_background_id = $row["theme_background_id"];
                        $theme_gift_wrap_id = $row["theme_gift_wrap_id"];
                        $findBackground = $db->select("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_background_id]);
                        if($findBackground->num_rows > 0){
                            while($bg_row = $findBackground->fetch_assoc()){
                                $background_image = $bg_row["theme_image"];
                            }
                        }
                        $findGiftWrap = $db->select("SELECT theme_image FROM themes WHERE theme_id = ?", [$theme_gift_wrap_id]);
                        if($findGiftWrap->num_rows > 0){
                            while($gw_row = $findGiftWrap->fetch_assoc()){
                                $wrap_image = $gw_row["theme_image"];
                            }
                        }
                        echo "
                        <a class='wishlist-grid-item' href='view-wishlist.php?id=$id'>
                            <div class='items-list preview' style='background-image: url(images/site-images/themes/desktop-backgrounds/$background_image);'>
                                <div class='item-container'>
                                    <img src='images/site-images/themes/gift-wraps/$wrap_image/1.png' class='gift-wrap' alt='gift wrap'>
                                    <div class='item-description'>
                                        <div class='bar title'></div>
                                        <div class='bar'></div>
                                        <div class='bar'></div>
                                        <div class='bar'></div>
                                        <div class='bar'></div>
                                    </div>
                                </div>
                            </div>
                            <div class='wishlist-overlay'></div>
                            <div class='wishlist-name'><span>$list_name$duplicate</span></div>
                        </a>";
                    }
                }
                ?>
            </div>
            <?php include "includes/footer.php"; ?>
        </div>
    </div>
</body>
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
            $(".popup-container.birthday").insertBefore(".popup-container.christmas");
        }else if(this.value == "Christmas"){
            name_input.value = "<?php echo $name; ?>'s " + current_year + " Christmas Wishlist";
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
    $(document).ready(function() {
        $(".theme-nav a").on("click", function(e){
            e.preventDefault();
            $(".theme-nav a").removeClass("active");
            $(".theme-picture img").addClass("hidden");
            if($(this).hasClass("desktop")){
                $(".theme-nav a.desktop").addClass("active");
                $(".theme-picture img.desktop").removeClass("hidden");
            }else{
                $(".theme-nav a.mobile").addClass("active");
                $(".theme-picture img.mobile").removeClass("hidden");
            }
        });

        $selected_desktop_theme = "";
        $selected_mobile_theme = "";
        $(".select-theme").on("click", function(e){
            e.preventDefault();
            $type = $("#wishlist_type").val().toLowerCase();
            $popup_container = ".popup-container."+$type + " ";
            $background_image = $(this).data("background-image");
            $background_id = $(this).data("background-id");
            $default_gift_wrap = $(this).data("default-gift-wrap");
            $($popup_container+".theme-content").addClass("hidden");
            $($popup_container+".gift-wrap-content").removeClass("hidden");
            $(this).closest(".popup-container").addClass("hidden");
            $($popup_container+".image-dropdown.gift-wrap .options .option").removeClass("recommended");
            $($popup_container+".image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$default_gift_wrap+"]").parent().click();
            $($popup_container+".image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$default_gift_wrap+"]").parent().addClass("recommended");
            $($popup_container+".image-dropdown.background .options .option .value[data-background-id="+$background_id+"]").parent().click();
        });

        $(".image-dropdown .selected-option").on("click", function(e){
            e.preventDefault();
            if($(this).closest(".image-dropdown").find(".options").hasClass("hidden")){
                $(".image-dropdown .options").addClass("hidden");
                $(this).closest(".image-dropdown").find(".options").removeClass("hidden");
                $(this).closest(".popup-content").addClass("fixed");
            }else{
                $(this).closest(".image-dropdown").find(".options").addClass("hidden");
                $(this).closest(".popup-content").removeClass("fixed");
            }
            if($(this).closest(".image-dropdown").find(".options .option.selected")[0] != null){
                $(this).closest(".image-dropdown").find(".options .option.selected")[0].scrollIntoView({ block: "end" });
            }
        });

        $(window).on("click", function(e){
            $open_dropdowns = $(".image-dropdown .options:not(.hidden)");
            if(!e.target.classList.contains("image-dropdown") && e.target.closest(".image-dropdown") == null){
                $open_dropdowns.addClass("hidden");
                $open_dropdowns.first().closest(".popup-content").removeClass("fixed");
            }
        });

        $(".options .option").on("click", function(e){
            e.preventDefault();
            $type = $("#wishlist_type").val().toLowerCase();
            $popup_container = ".popup-container."+$type + " ";
            if($(this).closest(".image-dropdown").hasClass("gift-wrap")){
                $($popup_container+".image-dropdown.gift-wrap .options .option").removeClass("selected");
                $(this).addClass("selected");
                $wrap_id = $(this).find(".value").data("wrap-id");
                $wrap_image = $(this).find(".value").data("wrap-image");
                $number_of_files = parseInt($(this).find(".value").data("number-of-files"));
                $selected_option = $($popup_container+".image-dropdown.gift-wrap .selected-option");
                $selected_option.find(".value").text($(this).find(".value").text());
                $selected_option.find(".value").data("wrap-id", $wrap_id);
                $selected_option.find(".value").data("wrap-image", $wrap_image);
                $selected_option.find(".preview-image").html("<img src='images/site-images/themes/gift-wraps/"+$wrap_image+"/1.png' />");
                $file_count = 1;
                $($popup_container+"img.gift-wrap").each(function(){
                    if($file_count > $number_of_files) $file_count = 1;
                    $(this).attr("src", "images/site-images/themes/gift-wraps/"+$wrap_image+"/"+$file_count+".png")
                    $file_count++;
                });
                $(this).closest(".options").addClass("hidden");
                $(this).closest(".popup-content").removeClass("fixed");
            }else if($(this).closest(".image-dropdown").hasClass("background")){
                $($popup_container+".image-dropdown.background .options .option").removeClass("selected");
                $(this).addClass("selected");
                $background_id = $(this).find(".value").data("background-id");
                $background_image = $(this).find(".value").data("background-image");
                $default_gift_wrap = $(this).find(".value").data("default-gift-wrap");
                $selected_option = $($popup_container+".image-dropdown.background .selected-option");
                $selected_option.find(".value").text($(this).find(".value").text());
                $selected_option.find(".value").data("background-id", $background_id);
                $selected_option.find(".value").data("background-image", $background_image);
                $selected_option.find(".preview-image").html("<img src='images/site-images/themes/desktop-backgrounds/"+$background_image+"' />");
                $(this).closest(".popup").addClass("theme-background");
                if($(this).closest(".popup").outerWidth() <= 600){
                    $(this).closest(".popup").css("background-image", "url('images/site-images/themes/mobile-backgrounds/"+$background_image+"')");
                }else{
                    $(this).closest(".popup").css("background-image", "url('images/site-images/themes/desktop-backgrounds/"+$background_image+"')");
                }
                $($popup_container+".image-dropdown.gift-wrap .options .option").removeClass("recommended");
                $($popup_container+".image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$default_gift_wrap+"]").parent().click();
                $($popup_container+".image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$default_gift_wrap+"]").parent().addClass("recommended");
                $(this).closest(".options").addClass("hidden");
                $(this).closest(".popup-content").removeClass("fixed");
            }
        });

        $(window).on("resize", function(e){
            if($("#wishlist_type").val() != null){
                $type = $("#wishlist_type").val().toLowerCase();
                $popup_container = ".popup-container."+$type + " ";
                $current_background = $($popup_container+".popup").css("background-image");
                if($($popup_container+".popup").outerWidth() <= 600){
                    if($($popup_container+".popup").css("background-image") != ""){
                        $($popup_container+".popup").css("background-image", $current_background.replace("desktop", "mobile"));
                    }
                }else{
                    if($($popup_container+".popup").css("background-image") != ""){
                        $($popup_container+".popup").css("background-image", $current_background.replace("mobile", "desktop"));
                    }
                }
            }
        });

        $(".back-to").on("click", function(e){
            e.preventDefault();
            $type = $("#wishlist_type").val().toLowerCase();
            $popup_container = ".popup-container."+$type + " ";
            $($popup_container+".theme-content").removeClass("hidden");
            $($popup_container+".gift-wrap-content").addClass("hidden");
            $(this).closest(".popup").removeClass("theme-background");
            $(this).closest(".popup").css("background-image", "");
        });
        $(".continue-button").on("click", function(e){
            e.preventDefault();
            $type = $("#wishlist_type").val().toLowerCase();
            $popup_container = ".popup-container."+$type + " ";
            $selected_background = $($popup_container+".image-dropdown.background .selected-option");
            $background_id = $selected_background.find(".value").data("background-id");
            $("#theme_background_id").val($background_id);
            $background_image = $selected_background.find(".value").data("background-image");
            $(".theme-background-display").html("<label>Background:</label><img src='images/site-images/themes/desktop-backgrounds/"+$background_image+"' />");
            $selected_gift_wrap = $($popup_container+".image-dropdown.gift-wrap .selected-option");
            $gift_wrap_id = $selected_gift_wrap.find(".value").data("wrap-id");
            $("#theme_gift_wrap_id").val($gift_wrap_id);
            $gift_wrap_clone = $($popup_container+".image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$gift_wrap_id+"]").parent().clone(true);
            $gift_wrap_clone.find(".value").remove();
            $gift_wrap_clone.find(".recommended").remove();
            $(".theme-gift-wrap-display").html("<label>Gift Wrap:</label>"+$gift_wrap_clone.html());
            $($popup_container).addClass("hidden");
            $(".choose-theme-button").text("Change Theme");
        });
    });
</script>