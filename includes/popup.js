// show popup if approve button is clicked
for(const button of document.querySelectorAll(".popup-button")){
    button.addEventListener("click", function(e){
        e.preventDefault();
        if(button.tagName == "INPUT"){
            button.nextElementSibling.nextElementSibling.firstElementChild.classList.add("active");
            button.nextElementSibling.nextElementSibling.classList.remove("hidden");
        }else{
            button.nextElementSibling.firstElementChild.classList.add("active");
            button.nextElementSibling.classList.remove("hidden");
        }
    });
}

// hide popup if x button or no button is clicked
for(const x of document.querySelectorAll(".close-button")){
    x.addEventListener("click", function(){
        x.closest(".popup-container").classList.add("hidden");
        for(const popup of x.closest(".popup-container").querySelectorAll(".popup")){
            popup.classList.remove("slide-in-left", "slide-out-left", "slide-in-right", "slide-out-right", "hidden");
            if(popup.className.includes("yes")){
                popup.classList.add("hidden");
            }
        }
    });
}
for(const no of document.querySelectorAll(".no-button")){
    no.addEventListener("click", function(){
        no.closest(".popup-container").classList.add("hidden");
    });
}