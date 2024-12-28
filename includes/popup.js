$(document).ready(function() {
    // show popup if approve button is clicked
    $(document.body).on("click", ".popup-button:not(.disabled)", function(e) {
        $("body").addClass("fixed");
        let button = this;
        e.preventDefault();
        if(button.tagName == "INPUT"){
            button.nextElementSibling.nextElementSibling.firstElementChild.classList.add("active");
            button.nextElementSibling.nextElementSibling.classList.remove("hidden");
        }else{
            button.nextElementSibling.firstElementChild.classList.add("active");
            button.nextElementSibling.classList.remove("hidden");
        }
    });

    // hide popup if x button or no button is clicked
    $(document.body).on("click", ".close-container:not(.options-close)", function(e) {
        e.preventDefault();
        if(!$(this).closest(".popup-container").hasClass("first") && !$(this).closest(".popup-container").hasClass("second")){
            $("body").removeClass("fixed");
        }
        this.closest(".popup-container").classList.add("hidden");
        for(const popup of this.closest(".popup-container").querySelectorAll(".popup:not(.first, .second)")){
            popup.classList.remove("slide-in-left", "slide-out-left", "slide-in-right", "slide-out-right", "hidden");
            if(popup.className.includes("yes")){
                popup.classList.add("hidden");
            }
        }
    });
    $(document.body).on("click", ".no-button", function(e) {
        e.preventDefault();
        if(!$(this).closest(".popup-container").hasClass("first") && !$(this).closest(".popup-container").hasClass("second")){
            $("body").removeClass("fixed");
        }
        this.closest(".popup-container").classList.add("hidden");
        if($(this).hasClass("double-no")){
            $(this).closest(".popup-container").prev().closest(".popup-container").addClass("hidden");
        }
    });

    $(window).on("click", function(e){
        $openPopups = $(".popup-container:not(.hidden)");
        $open_dropdowns = $(".image-dropdown .options:not(.hidden)");
        if($openPopups.length > 0){
            if(!e.target.classList.contains("popup-button") && !e.target.classList.contains("image-popup-button") && (e.target.closest(".popup-container") == null || e.target.classList.contains("popup-container"))){
                if($open_dropdowns.length > 0){
                    $open_dropdowns.addClass("hidden");
                    $open_dropdowns.first().closest(".popup-content").removeClass("fixed static");
                }else{
                    $popupSecond = $(".popup-container.second:not(.hidden)");
                    $popupFirst = $(".popup-container.first:not(.hidden)");
                    if($popupSecond.length > 0){
                        $popupSecond.addClass("hidden");
                    }else if($popupFirst.length > 0){
                        $popupFirst.addClass("hidden");
                    }else{
                        $(".popup-container").addClass("hidden");
                        document.body.classList.remove("fixed");
                    }
                }
            }
        }
    });

    $(document.body).on("click", ".image-popup-button", function(e){
        e.preventDefault();
        $new_src = $(this).find(".item-image").attr("src");
        $popup = $(".image-popup-container").first();
        $popup.removeClass("hidden");
        $popup.find(".image-popup").addClass("active");
        $popup.find(".popup-image").attr("src", $new_src);
    });
});