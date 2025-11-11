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
        <p style="margin: 0 0 20px;"><a class="button accent" href="/admin/backgrounds<?php echo isset($pageno) ? '?pageno=' . (int)$pageno : ''; ?>">Back to Backgrounds</a></p>
        
        <div class="form-container">
            <h2>Edit Background</h2>
            <form method="POST" action="/admin/backgrounds/update" enctype="multipart/form-data">
                <input type="hidden" name="theme_id" value="<?php echo htmlspecialchars($background['theme_id'] ?? ''); ?>">
                <input type="hidden" name="pageno" value="<?php echo isset($pageno) ? (int)$pageno : 1; ?>">
                
                <?php if(isset($error_msg)) echo $error_msg; ?>
                
                <div class="flex form-flex">
                    <div class="large-input">
                        <label for="theme_name">Name:<br></label>
                        <input required type="text" name="theme_name" id="theme_name" value="<?php echo htmlspecialchars($theme_name ?? $background['theme_name'] ?? ''); ?>" maxlength="100" />
                    </div>
                    
                    <div class="large-input">
                        <label for="theme_tag">Tag:<br></label>
                        <select required name="theme_tag" id="theme_tag">
                            <option value="" disabled>Select an option</option>
                            <option value="birthday" <?php echo (($theme_tag ?? $background['theme_tag'] ?? '') == 'birthday') ? 'selected' : ''; ?>>Birthday</option>
                            <option value="christmas" <?php echo (($theme_tag ?? $background['theme_tag'] ?? '') == 'christmas') ? 'selected' : ''; ?>>Christmas</option>
                        </select>
                    </div>
                    
                    <div class="large-input">
                        <label for="theme_image">Image Filename:<br></label>
                        <input required type="text" name="theme_image" id="theme_image" value="<?php echo htmlspecialchars($theme_image ?? $background['theme_image'] ?? ''); ?>" maxlength="255" />
                        <p style="margin-top: 5px; font-size: 0.9em; color: var(--text-secondary);">Filename without extension (e.g., "background1"). Upload new images below to replace existing ones.</p>
                    </div>
                    
                    <!-- Desktop Background -->
                    <div class="large-input">
                        <label for="desktop_background">Desktop Background:<br></label>
                        <a class="file-input">Choose Desktop Background</a>
                        <input type="file" name="desktop_background" class="hidden" id="desktop_background" accept=".png, .jpg, .jpeg, .webp">
                        <div class="<?php 
                            $imageName = $background['theme_image'] ?? '';
                            $desktopExists = false;
                            $desktopImgPath = '';
                            if (!empty($imageName)) {
                                // Check with .png extension first (most common)
                                $testPath = __DIR__ . '/../../../public/images/site-images/themes/desktop-backgrounds/' . $imageName;
                                if (file_exists($testPath)) {
                                    $desktopExists = true;
                                    $desktopImgPath = '/public/images/site-images/themes/desktop-backgrounds/' . $imageName;
                                } else {
                                    // Try adding .png if not present
                                    $testPathPng = __DIR__ . '/../../../public/images/site-images/themes/desktop-backgrounds/' . $imageName . '.png';
                                    if (file_exists($testPathPng)) {
                                        $desktopExists = true;
                                        $desktopImgPath = '/public/images/site-images/themes/desktop-backgrounds/' . $imageName . '.png';
                                    }
                                }
                            }
                            echo $desktopExists ? '' : 'hidden'; 
                        ?>" id="desktop_preview_container">
                            <?php if ($desktopExists): ?>
                                <img class="preview image-preview" src="<?php echo $desktopImgPath; ?>" id="desktop_preview">
                            <?php else: ?>
                                <img class="preview image-preview" style="display: none;" id="desktop_preview">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="desktop_background_temp" id="desktop_background_temp" value="">
                    </div>
                    
                    <!-- Desktop Thumbnail -->
                    <div class="large-input">
                        <label for="desktop_thumbnail">Desktop Thumbnail:<br></label>
                        <a class="file-input">Choose Desktop Thumbnail</a>
                        <input type="file" name="desktop_thumbnail" class="hidden" id="desktop_thumbnail" accept=".png, .jpg, .jpeg, .webp">
                        <p style="margin-top: 5px; font-size: 0.9em; color: var(--text-secondary);">Or leave empty to auto-generate from desktop background</p>
                        <div class="<?php 
                            $imageName = $background['theme_image'] ?? '';
                            $desktopThumbExists = false;
                            $desktopThumbPath = '';
                            if (!empty($imageName)) {
                                $testPath = __DIR__ . '/../../../public/images/site-images/themes/desktop-thumbnails/' . $imageName;
                                if (file_exists($testPath)) {
                                    $desktopThumbExists = true;
                                    $desktopThumbPath = '/public/images/site-images/themes/desktop-thumbnails/' . $imageName;
                                } else {
                                    $testPathPng = __DIR__ . '/../../../public/images/site-images/themes/desktop-thumbnails/' . $imageName . '.png';
                                    if (file_exists($testPathPng)) {
                                        $desktopThumbExists = true;
                                        $desktopThumbPath = '/public/images/site-images/themes/desktop-thumbnails/' . $imageName . '.png';
                                    }
                                }
                            }
                            echo $desktopThumbExists ? '' : 'hidden'; 
                        ?>" id="desktop_thumbnail_preview_container">
                            <?php if ($desktopThumbExists): ?>
                                <img class="preview image-preview" src="<?php echo $desktopThumbPath; ?>" id="desktop_thumbnail_preview" style="max-width: 200px; max-height: 200px;">
                            <?php else: ?>
                                <img class="preview image-preview" style="display: none;" id="desktop_thumbnail_preview">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="desktop_thumbnail_temp" id="desktop_thumbnail_temp" value="">
                    </div>
                    
                    <!-- Mobile Background -->
                    <div class="large-input">
                        <label for="mobile_background">Mobile Background:<br></label>
                        <a class="file-input">Choose Mobile Background</a>
                        <input type="file" name="mobile_background" class="hidden" id="mobile_background" accept=".png, .jpg, .jpeg, .webp">
                        <div class="<?php 
                            $imageName = $background['theme_image'] ?? '';
                            $mobileExists = false;
                            $mobileImgPath = '';
                            if (!empty($imageName)) {
                                $testPath = __DIR__ . '/../../../public/images/site-images/themes/mobile-backgrounds/' . $imageName;
                                if (file_exists($testPath)) {
                                    $mobileExists = true;
                                    $mobileImgPath = '/public/images/site-images/themes/mobile-backgrounds/' . $imageName;
                                } else {
                                    $testPathPng = __DIR__ . '/../../../public/images/site-images/themes/mobile-backgrounds/' . $imageName . '.png';
                                    if (file_exists($testPathPng)) {
                                        $mobileExists = true;
                                        $mobileImgPath = '/public/images/site-images/themes/mobile-backgrounds/' . $imageName . '.png';
                                    }
                                }
                            }
                            echo $mobileExists ? '' : 'hidden'; 
                        ?>" id="mobile_preview_container">
                            <?php if ($mobileExists): ?>
                                <img class="preview image-preview" src="<?php echo $mobileImgPath; ?>" id="mobile_preview">
                            <?php else: ?>
                                <img class="preview image-preview" style="display: none;" id="mobile_preview">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="mobile_background_temp" id="mobile_background_temp" value="">
                    </div>
                    
                    <!-- Mobile Thumbnail -->
                    <div class="large-input">
                        <label for="mobile_thumbnail">Mobile Thumbnail:<br></label>
                        <a class="file-input">Choose Mobile Thumbnail</a>
                        <input type="file" name="mobile_thumbnail" class="hidden" id="mobile_thumbnail" accept=".png, .jpg, .jpeg, .webp">
                        <p style="margin-top: 5px; font-size: 0.9em; color: var(--text-secondary);">Or leave empty to auto-generate from mobile background</p>
                        <div class="<?php 
                            $imageName = $background['theme_image'] ?? '';
                            $mobileThumbExists = false;
                            $mobileThumbPath = '';
                            if (!empty($imageName)) {
                                $testPath = __DIR__ . '/../../../public/images/site-images/themes/mobile-thumbnails/' . $imageName;
                                if (file_exists($testPath)) {
                                    $mobileThumbExists = true;
                                    $mobileThumbPath = '/public/images/site-images/themes/mobile-thumbnails/' . $imageName;
                                } else {
                                    $testPathPng = __DIR__ . '/../../../public/images/site-images/themes/mobile-thumbnails/' . $imageName . '.png';
                                    if (file_exists($testPathPng)) {
                                        $mobileThumbExists = true;
                                        $mobileThumbPath = '/public/images/site-images/themes/mobile-thumbnails/' . $imageName . '.png';
                                    }
                                }
                            }
                            echo $mobileThumbExists ? '' : 'hidden'; 
                        ?>" id="mobile_thumbnail_preview_container">
                            <?php if ($mobileThumbExists): ?>
                                <img class="preview image-preview" src="<?php echo $mobileThumbPath; ?>" id="mobile_thumbnail_preview" style="max-width: 200px; max-height: 200px;">
                            <?php else: ?>
                                <img class="preview image-preview" style="display: none;" id="mobile_thumbnail_preview">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="mobile_thumbnail_temp" id="mobile_thumbnail_temp" value="">
                    </div>
                    
                    <div class="large-input">
                        <label for="default_gift_wrap">Default Gift Wrap ID:<br></label>
                        <input type="number" name="default_gift_wrap" id="default_gift_wrap" value="<?php echo htmlspecialchars($default_gift_wrap ?? $background['default_gift_wrap'] ?? ''); ?>" min="0" />
                        <p style="margin-top: 5px; font-size: 0.9em; color: var(--text-secondary);">Theme ID of the default gift wrap (0 for none)</p>
                    </div>
                    
                    <div class="large-input center">
                        <input type="submit" class="button text" value="Update Background" />
                    </div>
                </div>
            </form>
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

<script src="/public/js/form-validation.js"></script>
<script>
$(document).ready(function() {
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
        },
        default_gift_wrap: {
            numeric: true
        }
    });
    
    // Handle desktop background image preview
    $('#desktop_background').on('change', function() {
        const file = this.files && this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const $preview = $('#desktop_preview');
                const $container = $('#desktop_preview_container');
                if ($preview.length && $container.length) {
                    $preview.attr('src', e.target.result).css('display', '');
                    $container.removeClass('hidden');
                }
            };
            reader.onerror = function() {
                console.error('Error reading desktop background file');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Handle desktop thumbnail image preview
    $('#desktop_thumbnail').on('change', function() {
        const file = this.files && this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const $preview = $('#desktop_thumbnail_preview');
                const $container = $('#desktop_thumbnail_preview_container');
                if ($preview.length && $container.length) {
                    $preview.attr('src', e.target.result).css('display', '');
                    $container.removeClass('hidden');
                }
            };
            reader.onerror = function() {
                console.error('Error reading desktop thumbnail file');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Handle mobile background image preview
    $('#mobile_background').on('change', function() {
        const file = this.files && this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const $preview = $('#mobile_preview');
                const $container = $('#mobile_preview_container');
                if ($preview.length && $container.length) {
                    $preview.attr('src', e.target.result).css('display', '');
                    $container.removeClass('hidden');
                }
            };
            reader.onerror = function() {
                console.error('Error reading mobile background file');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Handle mobile thumbnail image preview
    $('#mobile_thumbnail').on('change', function() {
        const file = this.files && this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const $preview = $('#mobile_thumbnail_preview');
                const $container = $('#mobile_thumbnail_preview_container');
                if ($preview.length && $container.length) {
                    $preview.attr('src', e.target.result).css('display', '');
                    $container.removeClass('hidden');
                }
            };
            reader.onerror = function() {
                console.error('Error reading mobile thumbnail file');
            };
            reader.readAsDataURL(file);
        }
    });
    
    // File input click handlers
    $('.file-input').on('click', function(e) {
        e.preventDefault();
        const $fileInput = $(this).next('input[type="file"]');
        if ($fileInput.length) {
            $fileInput.trigger('click');
        }
    });
});
</script>

