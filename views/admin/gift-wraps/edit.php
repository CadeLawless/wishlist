<?php
// Display flash messages
if (isset($flash['success'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['success']) . "</label></p>
            </div>
        </div>
    </div>";
}

if (isset($flash['error'])) {
    echo "
    <div class='popup-container'>
        <div class='popup active'>
            <div class='close-container'>
                <a href='#' class='close-button'>";
                require(__DIR__ . '/../../../public/images/site-images/menu-close.php');
                echo "</a>
            </div>
            <div class='popup-content'>
                <p><label>" . htmlspecialchars($flash['error']) . "</label></p>
            </div>
        </div>
    </div>";
}
?>

<h1 class="center">Admin Center</h1>
<div class="sidebar-main">
    <?php include __DIR__ . '/../../components/sidebar.php'; ?>
    <div class="content">
        <p style="margin: 0 0 20px;"><a class="button accent" href="/admin/gift-wraps<?php echo isset($pageno) ? '?pageno=' . (int)$pageno : ''; ?>">Back to Gift Wraps</a></p>
        
        <div class="form-container">
            <h2>Edit Gift Wrap</h2>
            <form method="POST" action="/admin/gift-wraps/update">
                <input type="hidden" name="theme_id" value="<?php echo htmlspecialchars($giftWrap['theme_id'] ?? ''); ?>">
                <input type="hidden" name="pageno" value="<?php echo isset($pageno) ? (int)$pageno : 1; ?>">
                
                <?php if(isset($error_msg)) echo $error_msg; ?>
                
                <div class="flex form-flex">
                    <div class="large-input">
                        <label for="theme_name">Name:<br></label>
                        <input required type="text" name="theme_name" id="theme_name" value="<?php echo htmlspecialchars($theme_name ?? $giftWrap['theme_name'] ?? ''); ?>" maxlength="100" />
                    </div>
                    
                    <div class="large-input">
                        <label for="theme_tag">Tag:<br></label>
                        <select required name="theme_tag" id="theme_tag">
                            <option value="" disabled>Select an option</option>
                            <option value="birthday" <?php echo (($theme_tag ?? $giftWrap['theme_tag'] ?? '') == 'birthday') ? 'selected' : ''; ?>>Birthday</option>
                            <option value="christmas" <?php echo (($theme_tag ?? $giftWrap['theme_tag'] ?? '') == 'christmas') ? 'selected' : ''; ?>>Christmas</option>
                        </select>
                    </div>
                    
                    <div class="large-input">
                        <label for="theme_image">Image Folder Name:<br></label>
                        <input required type="text" name="theme_image" id="theme_image" value="<?php echo htmlspecialchars($theme_image ?? $giftWrap['theme_image'] ?? ''); ?>" maxlength="255" />
                        <p style="margin-top: 5px; font-size: 0.9em; color: var(--text-secondary);">Folder name in gift-wraps directory (e.g., "wrap1")</p>
                    </div>
                    
                    <div class="large-input center">
                        <input type="submit" class="button text" value="Update Gift Wrap" />
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Gift Wrap Images Management Section -->
        <div class="form-container" style="margin-top: 30px;">
            <div class="gift-wrap-images-section">
                <h3 style="margin-bottom: 20px;">Gift Wrap Images</h3>
                
                <!-- Add New Image -->
                <div class="large-input" style="margin-bottom: 30px;">
                    <label for="new_gift_wrap_image">Add New Image:<br></label>
                    <a class="file-input">Choose Image</a>
                    <input type="file" name="new_gift_wrap_image" class="hidden" id="new_gift_wrap_image" accept=".png, .jpg, .jpeg, .webp">
                    <button type="button" id="upload-gift-wrap-image" class="button text" style="margin-top: 10px; display: none;">Upload Image</button>
                    <div id="new_image_preview_container" class="hidden" style="margin-top: 10px;">
                        <img class="preview image-preview" style="max-width: 200px; max-height: 200px;" id="new_image_preview">
                    </div>
                </div>
                
                <!-- Images List -->
                <div id="gift-wrap-images-list" class="gift-wrap-images-list" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 20px; margin-top: 20px;">
                    <?php 
                    $giftWrapFolder = $giftWrap['theme_image'] ?? '';
                    if (!empty($giftWrapImages) && is_array($giftWrapImages)): 
                        foreach ($giftWrapImages as $index => $imageFilename): 
                            $imageUrl = '/public/images/site-images/themes/gift-wraps/' . htmlspecialchars($giftWrapFolder) . '/' . htmlspecialchars($imageFilename);
                            $imageNumber = (int)pathinfo($imageFilename, PATHINFO_FILENAME);
                    ?>
                        <div class="gift-wrap-image-item" data-filename="<?php echo htmlspecialchars($imageFilename); ?>" data-number="<?php echo $imageNumber; ?>" style="position: relative; border: 2px solid var(--border-color); border-radius: 8px; padding: 10px; background: var(--background); cursor: move;">
                            <div style="position: absolute; top: 5px; right: 5px; font-size: 0.8em; background: var(--background-darker); padding: 2px 6px; border-radius: 4px;">#<?php echo $imageNumber; ?></div>
                            <img src="<?php echo $imageUrl; ?>" alt="Gift wrap <?php echo $imageNumber; ?>" style="width: 100%; height: auto; border-radius: 4px; display: block;">
                            <button type="button" class="button secondary delete-gift-wrap-image popup-button" data-filename="<?php echo htmlspecialchars($imageFilename); ?>" style="width: 100%; margin-top: 10px; padding: 8px;">Remove</button>
                            <div class='popup-container first hidden delete-image-popup-<?php echo $imageNumber; ?>'>
                                <div class='popup'>
                                    <div class='close-container'>
                                        <a href='#' class='close-button'>
                                            <?php require(__DIR__ . '/../../../public/images/site-images/menu-close.php'); ?>
                                        </a>
                                    </div>
                                    <div class='popup-content'>
                                        <label>Are you sure you want to remove this image?</label>
                                        <p>This action cannot be undone.</p>
                                        <div style='margin: 16px 0;' class='center'>
                                            <a class='button secondary no-button' href='#'>No</a>
                                            <a class='button primary confirm-delete-image' data-filename="<?php echo htmlspecialchars($imageFilename); ?>" href='#'>Yes</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endforeach; 
                    else: 
                    ?>
                        <p style="grid-column: 1 / -1; color: var(--text-secondary);">No images in this gift wrap set. Upload your first image above.</p>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($giftWrapImages) && count($giftWrapImages) > 1): ?>
                    <p style="margin-top: 20px; font-size: 0.9em; color: var(--text-secondary);">💡 Drag images to reorder them. The order will be saved automatically.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .form-container {
        margin: 20px auto 30px;
        background-color: var(--background-darker);
        max-width: 500px;
        padding: 20px;
    }
</style>

<script src="/public/js/form-validation.js?v=2.5"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
$(document).ready(function() {
    const themeId = <?php echo (int)($giftWrap['theme_id'] ?? 0); ?>;
    const giftWrapFolder = '<?php echo htmlspecialchars($giftWrap['theme_image'] ?? '', ENT_QUOTES); ?>';
    
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
    $('#new_gift_wrap_image').on('change', function() {
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
    });
    
    // File input click handler
    $('.file-input').on('click', function(e) {
        e.preventDefault();
        const $fileInput = $(this).next('input[type="file"]');
        if ($fileInput.length) {
            $fileInput.trigger('click');
        }
    });
    
    // Upload new gift wrap image
    $('#upload-gift-wrap-image').on('click', function() {
        const fileInput = document.getElementById('new_gift_wrap_image');
        const file = fileInput.files && fileInput.files[0];
        
        if (!file) {
            showErrorPopup('Please select an image first.');
            return;
        }
        
        const formData = new FormData();
        formData.append('gift_wrap_image', file);
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
                    $btn.prop('disabled', false).text('Upload Image');
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.error 
                    ? xhr.responseJSON.error 
                    : 'Error uploading image. Please try again.';
                showErrorPopup(errorMsg);
                $btn.prop('disabled', false).text('Upload Image');
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
</script>

