<div class="form-container">
    <h2><?= $add ? 'Add New' : 'Edit' ?> Gift Wrap</h2>
    <form method="POST" action="/admin/gift-wraps/<?= $add ? 'create' : 'update' ?>">
        <input type="hidden" id="theme_id" name="theme_id" value="<?php echo htmlspecialchars($giftWrap['theme_id'] ?? 0); ?>">
        <input type="hidden" id="theme_image_folder" name="theme_image_folder" value="<?php echo htmlspecialchars($giftWrap['theme_image'] ?? '', ENT_QUOTES); ?>">
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
                    <option value="Birthday" <?php echo (($theme_tag ?? $giftWrap['theme_tag'] ?? '') == 'Birthday') ? 'selected' : ''; ?>>Birthday</option>
                    <option value="Christmas" <?php echo (($theme_tag ?? $giftWrap['theme_tag'] ?? '') == 'Christmas') ? 'selected' : ''; ?>>Christmas</option>
                </select>
            </div>
            
            <div class="large-input">
                <label for="theme_image">Image Folder Name:<br></label>
                <input required type="text" name="theme_image" id="theme_image" value="<?php echo htmlspecialchars($theme_image ?? $giftWrap['theme_image'] ?? ''); ?>" maxlength="255" />
                <p style="margin-top: 5px; font-size: 0.9em; color: var(--text-secondary);">Folder name in gift-wraps directory (e.g., "wrap1")</p>
            </div>
            
            <div class="large-input center">
                <input type="submit" class="button text" value="<?= $add ? 'Add' : 'Update' ?> Gift Wrap" />
            </div>
        </div>
    </form>
</div>

<?php if(!$add): ?>     
    <!-- Gift Wrap Images Management Section -->
    <div class="form-container" style="margin-top: 30px;">
        <div class="gift-wrap-images-section">
            <h3 style="margin-bottom: 20px;">Gift Wrap Images</h3>
            
            <!-- Add New Image -->
            <div class="large-input" style="margin-bottom: 30px;">
                <label for="new_gift_wrap_image">Add New Image:<br></label>
                <a class="file-input">Choose Image</a>
                <input type="file" name="new_gift_wrap_image" multiple class="hidden" id="new_gift_wrap_image" accept=".png, .jpg, .jpeg, .webp">
                <button type="button" id="upload-gift-wrap-image" class="button text" style="margin-top: 10px; border: 0; background: unset; color: var(--text); font-size: 0.9em; display: none;">Uploading...</button>
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
                <p style="margin-top: 20px; font-size: 0.9em; color: var(--text-secondary);">Drag images to reorder them. The order will be saved automatically.</p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>