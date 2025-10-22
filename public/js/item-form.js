// autosize textareas
for(const textarea of document.querySelectorAll("textarea")){
    autosize(textarea);
}

// on click of file input button, open file picker
$(".file-input").on("click", function(e){
    e.preventDefault();
    $(this).next().click();
});

$("#paste-image").on("paste", function(e){
    e.preventDefault();
    const clipboardData = e.originalEvent.clipboardData;
    
    if (clipboardData && clipboardData.items) {
        for (let i = 0; i < clipboardData.items.length; i++) {
            const item = clipboardData.items[i];
            
            if (item.type.indexOf('image') !== -1) {
                const file = item.getAsFile();
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    // Store the base64 data in the hidden input field
                    $("#paste-image-hidden").val(event.target.result);
                    
                    // Clear the visible input field so user doesn't see base64 data
                    $("#paste-image").val("");
                    
                    // Show preview
                    $("#preview_container").find("img").attr("src", event.target.result);
                    $("#preview_container").removeClass("hidden");
                    
                    // Update button text
                    $(".file-input").text("Change Image");
                };
                
                reader.readAsDataURL(file);
                break;
            }
        }
    }
});
// show image preview on change
$("#image, .file-input + input").on("change", function(){
    $input = $(this);
    if (this.files && this.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $("#preview_container").find("img").attr("src", e.target.result);
        }

        reader.readAsDataURL(this.files[0]);
        document.querySelector("#preview_container").classList.remove("hidden");
        this.previousElementSibling.textContent = "Change Image";
    }else{
        document.querySelector("#preview_container").classList.add("hidden");
        $("#paste-image").val("");
        $("#paste-image-hidden").val(""); // Clear hidden paste data too
        this.previousElementSibling.textContent = "Choose Image";
    }
});
function readURL(input) {
    if (input.files && input.files[0]) {
        $('#preview_container').show();
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#preview').attr('src', e.target.result);
        }

        reader.readAsDataURL(input.files[0]);
    }else{
        $('#preview_container').hide();
    }
}
$(document).ready(function(){
    $("input").on("input", function(){
        if(this.validity.patternMismatch){
            setTimeout(() => {
                if(this.validity.patternMismatch){
                    if($(this).hasClass("price-input")){
                        $(this).parent().addClass("invalid");
                    }else{
                        $(this).addClass("invalid");
                    }
                }
            }, 1000);
        }else{
            if($(this).hasClass("price-input")){
                $(this).parent().removeClass("invalid");
            }else{
                $(this).removeClass("invalid");
            }
        }
    });

    $("#unlimited").on("change", function(){
        if(this.checked){
            $("#quantity").addClass("hidden");
            $("#quantity").removeAttr("required");
        }else{
            $("#quantity").removeClass("hidden");
            $("#quantity").attr("required", "");
        }
    });
});