$(document).ready(function() {
    $siteImageFolderPath = "/wishlist1/public/assets/images/site-images";

    $backgroundAjaxRequests = [];
    function showThemeBackgrounds() {
        $type = $("#wishlist_type").val();
        $backgroundAjaxRequests.forEach((req) => req.abort());
        $backgroundAjaxRequests.push(
            $.ajax({
                type: "POST",
                url: "/wishlist1/show-theme-backgrounds",
                data: {
                    type: $type,
                },
                success: function(html) {
                    $(".theme-list").html(html);
                },
                error: function(error) {
                    console.error("AJAX Error:", error);
                    $(".theme-list").html("Error fetching backgrounds");
                }
            })    
        );
    }

    $backgroundDropdownAjaxRequests = [];
    function showThemeBackgroundDropdownOptions() {
        $type = $("#wishlist_type").val();
        $backgroundDropdownAjaxRequests.forEach((req) => req.abort());
        $backgroundDropdownAjaxRequests.push(
            $.ajax({
                type: "POST",
                url: "/wishlist1/show-theme-background-options",
                data: {
                    type: $type,
                },
                success: function(html) {
                    $(".image-dropdown.background .options-content").html(html);
                },
                error: function(error) {
                    console.error("AJAX Error:", error);
                    $(".image-dropdown.background .options-content").html("Error fetching backgrounds");
                }
            })    
        );
    }

    $giftWrapDropdownAjaxRequests = [];
    function showThemeGiftWrapDropdownOptions() {
        $type = $("#wishlist_type").val();
        $giftWrapDropdownAjaxRequests.forEach((req) => req.abort());
        $giftWrapDropdownAjaxRequests.push(
            $.ajax({
                type: "POST",
                url: "/wishlist1/show-theme-gift-wrap-options",
                data: {
                    type: $type,
                },
                success: function(html) {
                    $(".image-dropdown.gift-wrap .options-content").html(html);
                },
                error: function(error) {
                    console.error("AJAX Error:", error);
                    $(".image-dropdown.gift-wrap .options-content").html("Error fetching gift wraps");
                }
            })    
        );
    }

    $("#wishlist_type").on("change", function(){
        $(".theme-list").html = "Loading...";
        $(".image-dropdown.background .options-content").html = "Loading...";
        $(".image-dropdown.gift-wrap .options-content").html = "Loading...";
        showThemeBackgrounds();
        showThemeBackgroundDropdownOptions();
        showThemeGiftWrapDropdownOptions();
    });

    $(document.body).on("click", ".theme-nav a", function(e){
        e.preventDefault();
        $(".theme-nav a").removeClass("active");
        $(".theme-picture img, .theme-picture > div").addClass("hidden");
        if($(this).hasClass("desktop")){
            $(".theme-nav a.desktop").addClass("active");
            $(".theme-picture img.desktop, .theme-picture > div.desktop").removeClass("hidden");
        }else{
            $(".theme-nav a.mobile").addClass("active");
            $(".theme-picture img.mobile, .theme-picture > div.mobile").removeClass("hidden");
        }
    });

    $(document.body).on("click", ".select-theme", function(e){
        e.preventDefault();
        $background_image = $(this).data("background-image");
        $background_id = $(this).data("background-id");
        $default_gift_wrap = $(this).data("default-gift-wrap");
        $(".popup-container .theme-content").addClass("hidden");
        $(".popup-container .gift-wrap-content").removeClass("hidden");
        $(this).closest(".popup-container").addClass("hidden");
        $(".popup-container .image-dropdown.gift-wrap .options .option").removeClass("recommended");
        $(".popup-container .image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$default_gift_wrap+"]").parent().click();
        $(".popup-container .image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$default_gift_wrap+"]").parent().addClass("recommended");
        $(".popup-container .image-dropdown.background .options .option .value[data-background-id="+$background_id+"]").parent().click();
        $(this).closest(".popup-container > .popup").find(".close-container").first().addClass("transparent-background");
    });

    $(document.body).on("click", ".image-dropdown .selected-option", function(e){
        e.preventDefault();
        if($(this).closest(".image-dropdown").find(".options").hasClass("hidden")){
            $(".image-dropdown .options").addClass("hidden");
            $(this).closest(".image-dropdown").find(".options").removeClass("hidden");
            $(this).closest(".popup-content").addClass("fixed static");
        }else{
            $(this).closest(".image-dropdown").find(".options").addClass("hidden");
            $(this).closest(".popup-content").removeClass("fixed static");
        }
        if($(this).closest(".image-dropdown").find(".options .option.selected")[0] != null){
            $(this).closest(".image-dropdown").find(".options .option.selected")[0].scrollIntoView({ block: "end" });
        }
    });

    $(window).on("click", function(e){
        $open_dropdowns = $(".image-dropdown .options:not(.hidden)");
        if(!e.target.classList.contains("image-dropdown") && e.target.closest(".image-dropdown") == null){
            $open_dropdowns.addClass("hidden");
            $open_dropdowns.first().closest(".popup-content").removeClass("fixed static");
        }
    });

    $(document.body).on("click", ".options .option", function(e){
        e.preventDefault();
        if($(this).closest(".image-dropdown").hasClass("gift-wrap")){
            $(".popup-container .image-dropdown.gift-wrap .options .option").removeClass("selected");
            $(this).addClass("selected");
            $wrap_id = $(this).find(".value").data("wrap-id");
            $("#theme_gift_wrap_id").val($wrap_id);
            $wrap_image = $(this).find(".value").data("wrap-image");
            $number_of_files = parseInt($(this).find(".value").data("number-of-files"));
            $selected_option = $(".popup-container .image-dropdown.gift-wrap .selected-option");
            $selected_option.find(".value").text($(this).find(".value").text());
            $selected_option.find(".value").data("wrap-id", $wrap_id);
            $selected_option.find(".value").data("wrap-image", $wrap_image);
            $selected_option.find(".preview-image").html("<img src='" + $siteImageFolderPath + "/themes/gift-wraps/"+$wrap_image+"/1.png' />");
            $file_count = 1;
            $(".popup-container img.gift-wrap").each(function(){
                if($file_count > $number_of_files) $file_count = 1;
                $(this).attr("src", "" + $siteImageFolderPath + "/themes/gift-wraps/"+$wrap_image+"/"+$file_count+".png")
                $file_count++;
            });
            $(this).closest(".options").addClass("hidden");
            $(this).closest(".popup-content").removeClass("fixed static");
        }else if($(this).closest(".image-dropdown").hasClass("background")){
            $(".popup-container .image-dropdown.background .options .option").removeClass("selected");
            $(this).addClass("selected");
            $background_id = $(this).find(".value").data("background-id");
            $("#theme_background_id").val($background_id);
            $background_image = $(this).find(".value").data("background-image");
            $default_gift_wrap = $(this).find(".value").data("default-gift-wrap");
            $selected_option = $(".popup-container .image-dropdown.background .selected-option");
            $selected_option.find(".value").text($(this).find(".value").text());
            $selected_option.find(".value").data("background-id", $background_id);
            $selected_option.find(".value").data("background-image", $background_image);
            if($background_id != "0"){
                $selected_option.find(".preview-image.desktop-image").html("<img src='" + $siteImageFolderPath + "/themes/desktop-thumbnails/"+$background_image+"' />");
                $selected_option.find(".preview-image.mobile-image").html("<img src='" + $siteImageFolderPath + "/themes/mobile-thumbnails/"+$background_image+"' />");
                $(this).closest(".popup").find(".background-theme.desktop-background").attr("src", "" + $siteImageFolderPath + "/themes/desktop-backgrounds/"+$background_image);
                $(this).closest(".popup").find(".background-theme.mobile-background").attr("src", "" + $siteImageFolderPath + "/themes/mobile-backgrounds/"+$background_image);
            }else{
                $selected_option.find(".preview-image.desktop-image").html("<span class='default-background'></span>");
                $selected_option.find(".preview-image.mobile-image").html("<span class='default-background'></span>");
                $(this).closest(".popup").find(".background-theme.desktop-background").attr("src", "");
                $(this).closest(".popup").find(".background-theme.mobile-background").attr("src", "");
            }
            $(this).closest(".popup").find(".background-theme.desktop-background").removeClass("hidden");
            $(this).closest(".popup").find(".background-theme.mobile-background").removeClass("hidden");
            $(".popup-container .image-dropdown.gift-wrap .options .option").removeClass("recommended");
            $(".popup-container .image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$default_gift_wrap+"]").parent().click();
            $(".popup-container .image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$default_gift_wrap+"]").parent().addClass("recommended");
            $(this).closest(".options").addClass("hidden");
            $(this).closest(".popup-content").removeClass("fixed static");
        }
    });

    $(document.body).on("click", ".back-to", function(e){
        e.preventDefault();
        $(".popup-container .theme-content").removeClass("hidden");
        $(".popup-container .gift-wrap-content").addClass("hidden");
        $(this).closest(".popup").removeClass("theme-background");
        $(this).closest(".popup").find(".background-theme.desktop-background").addClass("hidden");
        $(this).closest(".popup").find(".background-theme.mobile-background").addClass("hidden");
        $(this).closest(".popup-container > .popup").find(".close-container").first().removeClass("transparent-background");
    });
    $(document.body).on("click", "a.continue-button", function(e){
        e.preventDefault();
        $selected_background = $(".popup-container .image-dropdown.background .selected-option");
        $background_id = $selected_background.find(".value").data("background-id");
        $background_image = $selected_background.find(".value").data("background-image");
        if($background_id != "0"){
            $(".theme-background-display.desktop-background-display").html("<label>Background:</label><img src='" + $siteImageFolderPath + "/themes/desktop-thumbnails/"+$background_image+"' />");
            $(".theme-background-display.mobile-background-display").html("<label>Background:</label><img src='" + $siteImageFolderPath + "/themes/mobile-thumbnails/"+$background_image+"' />");
        }else{
            $(".theme-background-display.desktop-background-display").html("<label>Background:</label><div class='default-background'></div>");
            $(".theme-background-display.mobile-background-display").html("<label>Background:</label><div class='default-background'></div>");
        }
        $selected_gift_wrap = $(".popup-container .image-dropdown.gift-wrap .selected-option");
        $gift_wrap_id = $selected_gift_wrap.find(".value").data("wrap-id");
        $gift_wrap_clone = $(".popup-container .image-dropdown.gift-wrap .options .option .value[data-wrap-id="+$gift_wrap_id+"]").parent().clone(true);
        $gift_wrap_clone.find(".value").remove();
        $gift_wrap_clone.find(".recommended").remove();
        $(".theme-gift-wrap-display").html("<label>Gift Wrap:</label>"+$gift_wrap_clone.html());
        $($popup_container).addClass("hidden");
        $(".choose-theme-button").text("Change Theme");
        $("body").removeClass("fixed");
    });

    $(document.body).on("click", ".close-container.options-close", function(e){
        e.preventDefault();
        $(this).closest(".options").addClass("hidden");
        $(this).closest(".popup-content").removeClass("fixed static");
    });
});