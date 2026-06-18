$(document).ready(function() {
    const themeId = $('#theme_id').val();
    if (themeId == '') themeId = 0;
    const giftWrapFolder = $('#theme_image_folder').val();
    
    FormValidator.init('form', {
        theme_name: {
            required: true,
            minLength: 1,
            maxLength: 100
        },
        theme_tag: {
            required: true
        },
        theme_image: {
            required: true,
            minLength: 1,
            maxLength: 255
        }
    });
    
    // Handle new image preview
    /* $('#new_gift_wrap_image').on('change', function() {
        const file = this.files && this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const $preview = $('#new_image_preview');
                const $container = $('#new_image_preview_container');
                const $uploadBtn = $('#upload-gift-wrap-image');
                
                if ($preview.length && $container.length) {
                    $preview.attr('src', e.target.result);
                    $container.removeClass('hidden');
                    $uploadBtn.show();
                }
            };
            reader.onerror = function() {
                console.error('Error reading image file');
            };
            reader.readAsDataURL(file);
        }
    }); */
    
    // File input click handler
    $('.file-input').on('click', function(e) {
        e.preventDefault();
        const $fileInput = $(this).next('input[type="file"]');
        if ($fileInput.length) {
            $fileInput.trigger('click');
        }
    });

    // On file select, upload file immediately
    $('#new_gift_wrap_image').on('change', function() {
        // get multiple files
        const files = this.files;
        if (files && files.length > 0) {
            // Automatically upload the selected file
            $('#upload-gift-wrap-image').show();
            $('#upload-gift-wrap-image').click();
        }
    });

    // Upload new gift wrap image
    $('#upload-gift-wrap-image').on('click', function() {
        const fileInput = document.getElementById('new_gift_wrap_image');
        const files = fileInput.files;
        
        if (!files || files.length === 0) {
            showErrorPopup('Please select an image first.');
            return;
        }
        
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('filenames[]', files[i]);
        }
        formData.append('theme_id', themeId);
        formData.append('gift_wrap_folder', giftWrapFolder);
        
        const $btn = $(this);
        $btn.prop('disabled', true).text('Uploading...');
        
        $.ajax({
            url: '/admin/gift-wraps/add-image',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Reload the page to show the new image
                    window.location.reload();
                } else {
                    showErrorPopup('Error uploading image: ' + (response.error || 'Unknown error'));
                    $btn.prop('disabled', false).hide();
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.error 
                    ? xhr.responseJSON.error 
                    : 'Error uploading image. Please try again.';
                showErrorPopup(errorMsg);
                $btn.prop('disabled', false).hide();
            }
        });
    });
    
    // Delete gift wrap image - popup is handled by popups.js via .popup-button class
    // We just need to handle the confirmation action
    
    // Confirm delete image
    $(document).on('click', '.confirm-delete-image', function(e) {
        e.preventDefault();
        const filename = $(this).data('filename');
        const $item = $(this).closest('.gift-wrap-image-item');
        const $popup = $(this).closest('.popup-container');
        
        // Close popup first
        $popup.removeClass('active').addClass('hidden');
        $('body').removeClass('fixed');
        
        $.ajax({
            url: '/admin/gift-wraps/remove-image',
            type: 'POST',
            data: {
                theme_id: themeId,
                gift_wrap_folder: giftWrapFolder,
                filename: filename
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $item.fadeOut(300, function() {
                        $(this).remove();
                        // Check if no images left
                        if ($('.gift-wrap-image-item').length === 0) {
                            $('#gift-wrap-images-list').html('<p style="grid-column: 1 / -1; color: var(--text-secondary);">No images in this gift wrap set. Upload your first image above.</p>');
                        }
                        // Reload to update image numbers
                        setTimeout(function() {
                            window.location.reload();
                        }, 500);
                    });
                } else {
                    showErrorPopup('Error removing image: ' + (response.error || 'Unknown error'));
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.error 
                    ? xhr.responseJSON.error 
                    : 'Error removing image. Please try again.';
                showErrorPopup(errorMsg);
            }
        });
    });
    
    // Function to show error popup - uses same structure as app popups
    function showErrorPopup(message) {
        // Remove any existing error popup
        $('#error-popup-container').remove();
        
        // Get close icon from an existing popup if available
        let closeIconHtml = '';
        const $existingCloseBtn = $('.popup-container .close-button').first();
        if ($existingCloseBtn.length && $existingCloseBtn.html()) {
            // Clone the SVG from existing popup
            closeIconHtml = $existingCloseBtn.html();
        } else {
            // Fallback: create close icon SVG (same as menu-close.php)
            closeIconHtml = '<svg class="close-menu hidden" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="60" zoomAndPan="magnify" viewBox="0 0 810 809.999993" height="60" preserveAspectRatio="xMidYMid meet" version="1.0"><path fill="#292c2e" d="M 487.058594 400.304688 L 729.8125 157.550781 C 752.152344 135.214844 752.199219 98.933594 729.851562 76.585938 C 707.546875 54.285156 671.253906 54.253906 648.882812 76.621094 L 406.128906 319.375 L 163.375 76.621094 C 141.039062 54.285156 104.757812 54.238281 82.410156 76.585938 C 60.109375 98.890625 60.078125 135.183594 82.445312 157.550781 L 325.199219 400.304688 L 82.445312 643.058594 C 60.109375 665.398438 60.0625 701.675781 82.410156 724.023438 C 104.714844 746.328125 141.007812 746.359375 163.375 723.988281 L 406.128906 481.234375 L 648.882812 723.988281 C 671.222656 746.328125 707.5 746.375 729.851562 724.023438 C 752.152344 701.722656 752.183594 665.429688 729.8125 643.058594 Z M 487.058594 400.304688 " fill-opacity="1" fill-rule="evenodd"/></svg>';
        }
        
        // Escape message for HTML
        const escapedMessage = $('<div>').text(message).html();
        
        // Create popup HTML (close button is handled by popups.js automatically)
        const popupHtml = '<div id="error-popup-container" class="popup-container first active">' +
            '<div class="popup">' +
                '<div class="close-container">' +
                    '<a href="#" class="close-button">' + closeIconHtml + '</a>' +
                '</div>' +
                '<div class="popup-content">' +
                    '<label style="color: #e74c3c;">Error</label>' +
                    '<p>' + escapedMessage + '</p>' +
                    '<div style="margin: 16px 0;" class="center">' +
                        '<a class="button primary close-error-popup" href="#">OK</a>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
        
        $('body').append(popupHtml);
        $('body').addClass('fixed');
        
        // Close button is handled automatically by popups.js
        // We only need to handle the OK button
    }
    
    // Close error popup when OK button is clicked (close button is handled by popups.js)
    $(document).on('click', '.close-error-popup', function(e) {
        e.preventDefault();
        const $popup = $('#error-popup-container');
        if ($popup.length) {
            $popup.removeClass('active').addClass('hidden');
            $('body').removeClass('fixed');
            setTimeout(function() {
                $popup.remove();
            }, 300);
        }
    });
    
    // Initialize drag-and-drop reordering
    const imagesList = document.getElementById('gift-wrap-images-list');
    if (imagesList && $('.gift-wrap-image-item').length > 1) {
        const sortable = new Sortable(imagesList, {
            animation: 150,
            handle: '.gift-wrap-image-item',
            onEnd: function(evt) {
                // Get new order of filenames
                const newOrder = [];
                $('.gift-wrap-image-item').each(function() {
                    newOrder.push($(this).data('filename'));
                });
                
                // Save new order via AJAX
                $.ajax({
                    url: '/admin/gift-wraps/reorder-images',
                    type: 'POST',
                    data: {
                        theme_id: themeId,
                        gift_wrap_folder: giftWrapFolder,
                        new_order: newOrder
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Reload to update image numbers
                            window.location.reload();
                        } else {
                            showErrorPopup('Error reordering images: ' + (response.error || 'Unknown error'));
                            window.location.reload(); // Reload to restore original order
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON && xhr.responseJSON.error 
                            ? xhr.responseJSON.error 
                            : 'Error reordering images. Please try again.';
                        showErrorPopup(errorMsg);
                        window.location.reload(); // Reload to restore original order
                    }
                });
            }
        });
    }
});