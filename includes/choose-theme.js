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
        $(this).closest($popup_container+"> .popup").find(".close-container").addClass("transparent-background");
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
            $("#theme_gift_wrap_id").val($wrap_id);
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
            $("#theme_background_id").val($background_id);
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
                if($($popup_container+"> .popup").css("background-image") != ""){
                    $($popup_container+"> .popup").css("background-image", $current_background.replace("desktop", "mobile"));
                }
            }else{
                if($($popup_container+"> .popup").css("background-image") != ""){
                    $($popup_container+"> .popup").css("background-image", $current_background.replace("mobile", "desktop"));
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
        $(this).closest($popup_container+"> .popup").find(".close-container").removeClass("transparent-background");
    });
    $("a.continue-button").on("click", function(e){
        e.preventDefault();
        $type = $("#wishlist_type").val().toLowerCase();
        $popup_container = ".popup-container."+$type + " ";
        $selected_background = $($popup_container+".image-dropdown.background .selected-option");
        $background_image = $selected_background.find(".value").data("background-image");
        $(".theme-background-display").html("<label>Background:</label><img src='images/site-images/themes/desktop-backgrounds/"+$background_image+"' />");
        $selected_gift_wrap = $($popup_container+".image-dropdown.gift-wrap .selected-option");
        $gift_wrap_id = $selected_gift_wrap.find(".value").data("wrap-id");
        $gift_wrap_clone = $($popup_container+".image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$gift_wrap_id+"]").parent().clone(true);
        $gift_wrap_clone.find(".value").remove();
        $gift_wrap_clone.find(".recommended").remove();
        $(".theme-gift-wrap-display").html("<label>Gift Wrap:</label>"+$gift_wrap_clone.html());
        $($popup_container).addClass("hidden");
        $(".choose-theme-button").text("Change Theme");
        $("body").removeClass("fixed");
    });
});