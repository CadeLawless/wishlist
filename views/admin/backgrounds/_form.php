<?php
/**
 * Variables available in this view:
 * @var array $background - The background being edited (empty for add form)
 * @var array $giftWrapOptions - List of available gift wraps for the dropdown
 */
?>

<style>
    .form-container {
        margin: 20px auto 30px;
        background-color: var(--background-darker);
        max-width: 500px;
        padding: 20px;
    }
</style>

<h2><?= $add ? 'Add New' : 'Edit' ?> Background</h2>
<form method="POST" action="/admin/backgrounds/<?= $add ? 'create' : 'update' ?>" enctype="multipart/form-data">
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
            <p style="margin-top: 5px; font-size: 0.9em; color: var(--text-secondary);">Filename with extension (e.g., "background1.png"). Upload new images below to replace existing ones.</p>
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
                    <img class="preview image-preview" src="<?php echo $desktopImgPath . '?t=' . time(); ?>" id="desktop_preview">
                <?php else: ?>
                    <img class="preview image-preview" style="display: none;" id="desktop_preview">
                <?php endif; ?>
            </div>
            <input type="hidden" name="desktop_background_temp" id="desktop_background_temp" value="">
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
                    <img class="preview image-preview" src="<?php echo $mobileImgPath . '?t=' . time(); ?>" id="mobile_preview">
                <?php else: ?>
                    <img class="preview image-preview" style="display: none;" id="mobile_preview">
                <?php endif; ?>
            </div>
            <input type="hidden" name="mobile_background_temp" id="mobile_background_temp" value="">
        </div>
                
        <div class="large-input">
            <label for="default_gift_wrap">Default Gift Wrap:<br></label>
            <select name="default_gift_wrap" id="default_gift_wrap">
                <option value="0" <?php echo (($default_gift_wrap ?? $background['default_gift_wrap'] ?? 0) == 0) ? 'selected' : ''; ?>>None</option>
                <?php foreach ($giftWrapOptions as $giftWrap): ?>
                    <option value="<?php echo (int)$giftWrap['theme_id']; ?>" <?php echo (($default_gift_wrap ?? $background['default_gift_wrap'] ?? 0) == (int)$giftWrap['theme_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($giftWrap['theme_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p style="margin-top: 5px; font-size: 0.9em; color: var(--text-secondary);">Theme ID of the default gift wrap (0 for none)</p>
        </div>
        
        <div class="large-input center">
            <input type="submit" class="button text" value="<?= $add ? 'Add' : 'Update' ?> Background" />
        </div>
    </div>
</form>