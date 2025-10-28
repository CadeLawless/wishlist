// Function to initialize the form functionality
function initializeItemForm() {
    console.log("Item form script loaded"); // Debug log
    
    // autosize textareas
    for(const textarea of document.querySelectorAll("textarea")){
        autosize(textarea);
    }

    // on click of file input button, open file picker
    $(document).on("click", ".file-input", function(e){
        e.preventDefault();
        console.log("File input button clicked"); // Debug log
        $(this).next().click();
    });
    
    // Fallback with vanilla JavaScript
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('file-input')) {
            e.preventDefault();
            console.log("File input button clicked (vanilla JS)"); // Debug log
            const fileInput = e.target.nextElementSibling;
            if (fileInput && fileInput.type === 'file') {
                fileInput.click();
            }
        }
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
                    
                    reader.onload = function(e) {
                        const pasteImageHidden = $("#paste-image-hidden");
                        const previewContainer = $("#preview_container");
                        
                        // Store the image data
                        pasteImageHidden.val(e.target.result);
                        
                        // Show preview
                        previewContainer.find("img").attr("src", e.target.result);
                        previewContainer.removeClass("hidden");
                        
                        // Update button text
                        $(".file-input").text("Change Image");
                    };
                    
                    reader.readAsDataURL(file);
                    break;
                }
            }
        }
    });

    // Handle URL input in paste image field
    $("#paste-image").on("input", function() {
        const inputValue = $(this).val().trim();
        const pasteImageHidden = $("#paste-image-hidden");
        const previewContainer = $("#preview_container");
        
        if (inputValue && isValidUrl(inputValue)) {
            // It's a valid URL, store it and show preview
            pasteImageHidden.val(inputValue);
            
            // Show preview
            previewContainer.find("img").attr("src", inputValue);
            previewContainer.removeClass("hidden");
            
            // Update button text
            $(".file-input").text("Change Image");
        } else if (inputValue === '') {
            // Clear the preview if input is empty
            pasteImageHidden.val('');
            previewContainer.addClass("hidden");
            $(".file-input").text("Choose Item Image");
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
            this.previousElementSibling.textContent = "Choose Image";
        }
    });

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
    $("#url").on("paste", function(){
        setTimeout(() => {
            const urlValue = $(this).val().trim();
            if (urlValue && isValidUrl(urlValue)) {
                setTimeout(() => {
                    fetchUrlDetails();
                }, 500);
            }
        }, 100);
    });

    // Handle fetched image URL on page load
    const pasteImageInput = $("#paste-image");
    const pasteImageHidden = $("#paste-image-hidden");
    const previewContainer = $("#preview_container");
    
    // Check if there's a fetched image URL
    if (pasteImageInput.val() && pasteImageInput.val().trim() !== '') {
        const imageUrl = pasteImageInput.val().trim();
        
        // If it's a valid URL, show preview
        if (isValidUrl(imageUrl)) {
            // Update hidden field
            pasteImageHidden.val(imageUrl);
            
            // Clear the visible input field so user doesn't see the URL
            pasteImageInput.val("");
            
            // Show preview
            previewContainer.find("img").attr("src", imageUrl);
            previewContainer.removeClass("hidden");
            
            // Update button text
            $(".file-input").text("Change Image");
        }
    }
    
    // Check if there's already an existing image displayed (for edit forms)
    const existingImage = previewContainer.find("img");
    if (existingImage.length && existingImage.attr("src") && !previewContainer.hasClass("hidden")) {
        $(".file-input").text("Change Image");
    }
}

// Initialize immediately if DOM is ready, otherwise wait for it
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeItemForm);
} else {
    initializeItemForm();
}

// URL fetch function
function fetchUrlDetails() {
    const url = $("#url").val().trim();
    
    if (!url || !isValidUrl(url)) {
        alert("Please enter a valid URL");
        return;
    }
    
    // Show loading state
    $("#fetch-details-btn").text("Fetching...").prop("disabled", true);
    
    // Make AJAX request to fetch item details
    $.ajax({
        url: "/wishlist/item/fetch-details",
        method: "POST",
        data: { url: url },
        success: function(response) {
            if (response.success) {
                // Populate form fields
                if (response.data.title) {
                    $("#name").val(response.data.title);
                }
                if (response.data.price) {
                    $("#price").val(response.data.price);
                }
                if (response.data.image) {
                    $("#paste-image").val(response.data.image);
                    $("#paste-image-hidden").val(response.data.image);
                    $("#preview_container").find("img").attr("src", response.data.image);
                    $("#preview_container").removeClass("hidden");
                    $(".file-input").text("Change Image");
                }
                if (response.data.link) {
                    $("#url").val(response.data.link);
                }
                if (response.data.description) {
                    $("#notes").val(response.data.description);
                }
            } else {
                alert("Failed to fetch item details: " + (response.message || "Unknown error"));
            }
        },
        error: function() {
            alert("Error fetching item details. Please try again.");
        },
        complete: function() {
            // Reset button state
            $("#fetch-details-btn").text("Fetch Details").prop("disabled", false);
        }
    });
}

// Helper function to validate URLs
function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

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