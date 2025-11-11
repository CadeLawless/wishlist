// Wait for DOM and jQuery to be ready
$(document).ready(function() {
    // Wait for autosize to be available, then autosize textareas
    function initializeAutosize() {
        if (typeof autosize !== 'undefined') {
            for(const textarea of document.querySelectorAll("textarea")){
                autosize(textarea);
            }
        } else {
            // If autosize isn't ready yet, wait a bit and try again
            setTimeout(initializeAutosize, 100);
        }
    }
    initializeAutosize();

    // Initialize form validation with custom error placement for special fields
    // Check if this is an edit form (has existing image)
    const isEditForm = $("input[name='existing_image']").length > 0 && $("input[name='existing_image']").val() !== '';
    const hasExistingImage = isEditForm && $("#preview_container img").length > 0 && !$("#preview_container").hasClass("hidden");
    
    const validationRules = {
        name: {
            required: true,
            maxLength: 255
        },
        price: {
            required: true,
            currency: true,
            // Custom error placement: after the price-input-container
            errorContainer: '#price-input-container',
            // Custom invalid target: the dollar-sign-input span
            invalidTarget: '.dollar-sign-input'
        },
        quantity: {
            required: function() {
                // Only required if unlimited is NOT checked
                return !$("#unlimited").is(":checked");
            },
            numeric: true,
            // Custom error placement: after the quantity-container
            errorContainer: '.quantity-container',
            custom: function(value, field) {
                // Skip validation if unlimited is checked
                if ($("#unlimited").is(":checked")) {
                    return null;
                }
                // Ensure quantity is at least 1
                const numValue = parseInt(value, 10);
                if (numValue < 1) {
                    return 'Quantity must be at least 1.';
                }
                return null;
            }
        },
        link: {
            required: true,
            url: true
        },
        notes: {
            required: false,
            maxLength: 1000
        },
        priority: {
            required: true
        },
        item_image: {
            required: function() {
                // For edit forms: only required if there's no existing image and no new image selected
                if (isEditForm && hasExistingImage) {
                    // Check if user has selected a new image or pasted one
                    const hasFile = $("#image")[0].files && $("#image")[0].files.length > 0;
                    const hasPasteImage = $("#paste-image-hidden").val() && $("#paste-image-hidden").val().trim() !== '';
                    const hasTempFilename = $("input[name='temp_filename']").length > 0 && $("input[name='temp_filename']").val() !== '';
                    
                    // If existing image and no new image, not required
                    if (!hasFile && !hasPasteImage && !hasTempFilename) {
                        return false;
                    }
                }
                // For create forms or edit forms without existing image: always required
                return true;
            },
            custom: function(value, field) {
                // Check if image is provided via file, paste, or temp
                const hasFile = $("#image")[0].files && $("#image")[0].files.length > 0;
                const hasPasteImage = $("#paste-image-hidden").val() && $("#paste-image-hidden").val().trim() !== '';
                const hasTempFilename = $("input[name='temp_filename']").length > 0 && $("input[name='temp_filename']").val() !== '';
                
                // For edit forms: check if existing image is present
                if (isEditForm && hasExistingImage) {
                    // If no new image provided, existing image is sufficient
                    if (!hasFile && !hasPasteImage && !hasTempFilename) {
                        return null; // Valid - using existing image
                    }
                }
                
                // Must have at least one image source
                if (!hasFile && !hasPasteImage && !hasTempFilename) {
                    return 'Item image is required.';
                }
                
                return null;
            },
            // Custom error placement: after the image input container
            errorContainer: '#preview_container',
            invalidTarget: '#preview_container'
        }
    };

    FormValidator.init('form[method="POST"]', validationRules);

    // Custom validation for image field (file inputs need special handling)
    function validateImageField() {
        const $imageField = $("#image");
        const hasFile = $imageField[0].files && $imageField[0].files.length > 0;
        const hasPasteImage = $("#paste-image-hidden").val() && $("#paste-image-hidden").val().trim() !== '';
        const hasTempFilename = $("input[name='temp_filename']").length > 0 && $("input[name='temp_filename']").val() !== '';
        
        // Check if this is an edit form with an existing image (check dynamically)
        const $existingImageInput = $("input[name='existing_image']");
        const hasExistingImageInput = $existingImageInput.length > 0 && $existingImageInput.val() !== '';
        const $previewImg = $("#preview_container img.preview");
        const hasVisibleExistingImage = hasExistingImageInput && $previewImg.length > 0 && 
                                       !$("#preview_container").hasClass("hidden") &&
                                       ($previewImg.css("display") !== "none" && $previewImg.attr("src"));
        
        // For edit forms: check if existing image is present
        if (hasExistingImageInput && hasVisibleExistingImage) {
            // If no new image provided, existing image is sufficient
            if (!hasFile && !hasPasteImage && !hasTempFilename) {
                FormValidator.clearErrors($imageField, validationRules.item_image);
                return;
            }
        }
        
        // Must have at least one image source
        if (!hasFile && !hasPasteImage && !hasTempFilename) {
            FormValidator.displayErrors($imageField, ['Item image is required.'], validationRules.item_image);
        } else {
            FormValidator.clearErrors($imageField, validationRules.item_image);
        }
    }

    // Validate image when file is selected
    $("#image").on("change", function() {
        validateImageField();
    });

    // Validate image when paste image changes
    $("#paste-image, #paste-image-hidden").on("input paste", function() {
        setTimeout(validateImageField, 100); // Small delay to allow paste to complete
    });

    // Update quantity validation when unlimited checkbox changes
    $("#unlimited").on("change", function(){
        const $quantity = $("#quantity");
        if(this.checked){
            // Clear any validation errors when unlimited is checked
            FormValidator.clearErrors($quantity, validationRules.quantity);
            // Make sure to clear invalid class from dollar-sign-input if present
            $(".quantity-container").removeClass("invalid");
        } else {
            // Re-validate quantity when unlimited is unchecked
            FormValidator.validateField($quantity, 'quantity', validationRules.quantity);
        }
    });

    // on click of file input button, open file picker
    $(".file-input").on("click", function(e){
        e.preventDefault();
        $(this).next().click();
    });
    $("#paste-image").on("paste", function(e){
    e.preventDefault();
    const clipboardData = e.originalEvent.clipboardData;
    const $previewContainer = $("#preview_container");
    const $previewImg = $previewContainer.find("img");
    
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
                    $previewImg.attr("src", event.target.result);
                    // Remove inline display:none style if present
                    $previewImg.css("display", "");
                    $previewContainer.removeClass("hidden");
                    
                    // Clear any temp filename hidden field since we're using a new image
                    $("input[name='temp_filename']").remove();
                    
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
        let previewImg = previewContainer.find("img.preview");
        
        // Create img element if it doesn't exist
        if (!previewImg.length) {
            previewImg = $("<img>").addClass("preview image-preview");
            previewContainer.append(previewImg);
        }
        
        if (inputValue && isValidUrl(inputValue)) {
            // It's a valid URL, store it and show preview
            pasteImageHidden.val(inputValue);
            
            // Show preview
            previewImg.attr("src", inputValue);
            // Remove inline display:none style if present
            previewImg.css("display", "");
            previewContainer.removeClass("hidden");
            
            // Clear any temp filename hidden field since we're using a new image
            $("input[name='temp_filename']").remove();
            
            // Update button text
            $(".file-input").text("Change Image");
            
            // Validate the image field
            validateImageField();
        } else if (inputValue === '') {
            // Clear the preview if input is empty
            pasteImageHidden.val('');
            previewContainer.addClass("hidden");
            $(".file-input").text("Choose Item Image");
        }
    });
    // show image preview on change
    $("#image, input[type='file']").on("change", function(){
        $input = $(this);
        const $previewContainer = $("#preview_container");
        let $previewImg = $previewContainer.find("img.preview");
        
        // Create img element if it doesn't exist
        if (!$previewImg.length) {
            $previewImg = $("<img>").addClass("preview image-preview");
            $previewContainer.append($previewImg);
        }
        
        if (this.files && this.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                // Update the image source
                $previewImg.attr("src", e.target.result);
                // Remove inline display:none style if present
                $previewImg.css("display", "");
                // Show the preview container
                $previewContainer.removeClass("hidden");
                // Clear any paste image data since we're using a file now
                $("#paste-image").val("");
                $("#paste-image-hidden").val("");
                // Clear any temp filename hidden field since we're using a new file
                $("input[name='temp_filename']").remove();
                
                // Validate the image field
                validateImageField();
            }

            reader.readAsDataURL(this.files[0]);
            this.previousElementSibling.textContent = "Change Image";
        } else {
            // No file selected
            $previewContainer.addClass("hidden");
            $("#paste-image").val("");
            $("#paste-image-hidden").val("");
            this.previousElementSibling.textContent = "Choose Image";
        }
    });
    
    // Handle unlimited checkbox to show/hide quantity field
    $("#unlimited").on("change", function(){
        const $quantity = $("#quantity");
        if(this.checked){
            $quantity.addClass("hidden");
            $quantity.removeAttr("required");
        }else{
            $quantity.removeClass("hidden");
            $quantity.attr("required", "");
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
        
        // Get wishlist ID from form data attribute
        const wishlistId = $('form[data-wishlist-id]').data('wishlist-id');
        const apiUrl = wishlistId ? `/${wishlistId}/api/fetch-url-metadata` : '/api/fetch-url-metadata';
        
        // Make AJAX request
        $.ajax({
            url: apiUrl,
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
                        // Set the image URL in hidden input field
                        $("#paste-image-hidden").val(response.image);
                        
                        // Get preview container and image
                        const $previewContainer = $("#preview_container");
                        let $previewImg = $previewContainer.find("img.preview");
                        
                        // Create img element if it doesn't exist
                        if (!$previewImg.length) {
                            $previewImg = $("<img>").addClass("preview image-preview");
                            $previewContainer.append($previewImg);
                        }
                        
                        // Set image source and ensure it's visible
                        $previewImg.attr("src", response.image);
                        $previewImg.css("display", ""); // Remove inline display:none if present
                        $previewContainer.removeClass("hidden");
                        
                        // Clear any temp filename hidden field since we're using a new image
                        $("input[name='temp_filename']").remove();
                        
                        // Update button text
                        $(".file-input").text("Change Image");
                        
                        // Validate the image field
                        validateImageField();
                    }
                    
                    showStatusMessage("Product details fetched successfully!", "success");
                    
                    // Trigger autosize for textarea
                    if (response.title && typeof autosize !== 'undefined') {
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
            
            // Get or create preview image
            let $previewImg = previewContainer.find("img.preview");
            if (!$previewImg.length) {
                $previewImg = $("<img>").addClass("preview image-preview");
                previewContainer.append($previewImg);
            }
            
            // Show preview
            $previewImg.attr("src", imageUrl);
            $previewImg.css("display", ""); // Remove inline display:none if present
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
});