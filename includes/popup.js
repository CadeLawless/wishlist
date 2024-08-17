$(document).ready(function() {
    // show popup if approve button is clicked
    $(document.body).on("click", ".popup-button", function(e) {
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
    $(document.body).on("click", ".close-button", function() {
        this.closest(".popup-container").classList.add("hidden");
        for(const popup of this.closest(".popup-container").querySelectorAll(".popup")){
            popup.classList.remove("slide-in-left", "slide-out-left", "slide-in-right", "slide-out-right", "hidden");
            if(popup.className.includes("yes")){
                popup.classList.add("hidden");
            }
        }
    });
    $(document.body).on("click", ".no-button", function() {
        this.closest(".popup-container").classList.add("hidden");
    });
});