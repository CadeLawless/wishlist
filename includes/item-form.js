// autosize textareas
for(const textarea of document.querySelectorAll("textarea")){
    autosize(textarea);
}

// on click of file input button, open file picker
$(".file-input").on("click", function(e){
    e.preventDefault();
    $(this).next().click();
});

// show image preview on change
$("#image, .file-input + input").on("change", function(){
    $input = $(this);
    if (this.files && this.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            console.log($(this).next());
            $input.next().find("img").attr("src", e.target.result);
        }

        reader.readAsDataURL(this.files[0]);
        this.nextElementSibling.classList.remove("hidden");
        this.previousElementSibling.textContent = "Change Image";
    }else{
        this.nextElementSibling.classList.add("hidden");
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