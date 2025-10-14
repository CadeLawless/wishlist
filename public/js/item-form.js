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
                    // Set the base64 data in the paste input
                    $("#paste-image").val(event.target.result);
                    
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
            //console.log($(this).next());
            $("#preview_container").find("img").attr("src", e.target.result);
        }

        reader.readAsDataURL(this.files[0]);
        document.querySelector("#preview_container").classList.remove("hidden");
        this.previousElementSibling.textContent = "Change Image";
    }else{
        document.querySelector("#preview_container").classList.add("hidden");
        $("#paste-image").val("");
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