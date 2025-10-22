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

    // URL fetch functionality
    $("#fetch-details-btn").on("click", function(e){
        e.preventDefault();
        fetchUrlDetails();
    });

    // Auto-fetch when URL is pasted (optional)
    $("#link").on("paste", function(){
        setTimeout(() => {
            const url = $(this).val();
            if (url && isValidUrl(url)) {
                // Auto-fetch after a short delay to allow paste to complete
                setTimeout(() => {
                    fetchUrlDetails();
                }, 500);
            }
        }, 100);
    });
});

// URL fetch function
function fetchUrlDetails() {
    const url = $("#link").val().trim();
    
    if (!url) {
        showStatusMessage("Please enter a URL first", "error");
        return;
    }
    
    if (!isValidUrl(url)) {
        showStatusMessage("Please enter a valid URL", "error");
        return;
    }
    
    // Show loading state
    showLoadingState(true);
    showStatusMessage("Fetching product details...", "info");
    
    // Make AJAX request
    $.ajax({
        url: '/api/fetch-url-metadata',
        method: 'POST',
        data: {
            url: url
        },
        dataType: 'json',
        success: function(response) {
            showLoadingState(false);
            
            if (response.success) {
                // Populate form fields with fetched data
                if (response.title && !$("#name").val()) {
                    $("#name").val(response.title);
                }
                
                if (response.price && !$("#price").val()) {
                    $("#price").val(response.price);
                }
                
                if (response.image && !$("#paste-image-hidden").val()) {
                    // If we got an image URL, we could potentially fetch and display it
                    // For now, just show success message
                }
                
                showStatusMessage("Product details fetched successfully!", "success");
                
                // Trigger autosize for textarea
                if (response.title) {
                    autosize.update($("#name")[0]);
                }
            } else {
                showStatusMessage(response.error || "Could not fetch product details", "error");
            }
        },
        error: function(xhr, status, error) {
            showLoadingState(false);
            
            let errorMessage = "An error occurred while fetching product details";
            
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMessage = xhr.responseJSON.error;
            } else if (xhr.status === 401) {
                errorMessage = "Please log in to use this feature";
            } else if (xhr.status === 0) {
                errorMessage = "Network error. Please check your connection";
            }
            
            showStatusMessage(errorMessage, "error");
        }
    });
}

// Show/hide loading state
function showLoadingState(loading) {
    const $btn = $("#fetch-details-btn");
    const $spinner = $("#fetch-spinner");
    
    if (loading) {
        $btn.prop("disabled", true).text("Fetching...");
        $spinner.removeClass("hidden");
    } else {
        $btn.prop("disabled", false).text("Fetch Details");
        $spinner.addClass("hidden");
    }
}

// Show status message
function showStatusMessage(message, type) {
    const $status = $("#fetch-status");
    
    $status.removeClass("success error info").addClass(type);
    $status.text(message).removeClass("hidden");
    
    // Auto-hide success messages after 3 seconds
    if (type === "success") {
        setTimeout(() => {
            $status.addClass("hidden");
        }, 3000);
    }
}

// Validate URL format
function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}