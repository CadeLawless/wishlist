<div class="large-input">
    <label for="name">Item Name:<br></label>
    <textarea required name="name" id="name" autocapitalize="words" rows="1" placeholder="New Gaming PC"><?php echo htmlspecialchars($item_name ?? ''); ?></textarea>
</div>
<div class="small-input">
    <label for="price">Item Price:<br></label>
    <div id="price-input-container">
        <span class="dollar-sign-input flex">
            <label for="price"><span class="dollar-sign">$</span></label>
            <input type="text" inputmode="decimal" name="price" value="<?php echo htmlspecialchars($price ?? ''); ?>" id="price" class="price-input" required>
        </span>
    </div>
</div>
<div class="small-input">
    <label for="quantity">Quantity Needed:<br></label>
    <div class="flex flex-center quantity-container">
        <input <?php if(($unlimited ?? 'No') == "No") echo "required"; ?> type="text" class="<?php if(($unlimited ?? 'No') == "Yes") echo "hidden"; ?>" name="quantity" id="quantity" inputmode="numeric" value="<?php echo htmlspecialchars($quantity ?? '1'); ?>" />
        <input type="checkbox" name="unlimited" value="Yes" <?php if(($unlimited ?? 'No') == "Yes") echo "checked"; ?> id="unlimited" />
        <label for="unlimited">Unlimited</label>
    </div>
</div>
<div class="large-input">
    <label for="link">Item URL:<br></label>
    <input required type="url" name="link" id="link" value="<?php echo htmlspecialchars($link ?? ''); ?>" placeholder="https://example.com">
</div>
<div class="large-input">
    <label for="image">Item Image:<br></label>
    <a class="file-input">Choose Item Image</a>
    <input type="file" name="item_image" class="hidden" id="image" accept=".png, .jpg, .jpeg, .webp">
    <input type="text" placeholder="Or paste an image here..." id="paste-image" value="<?php echo htmlspecialchars($fetched_image_url ?? ''); ?>" />
    <input type="hidden" name="paste_image" id="paste-image-hidden" value="<?php echo htmlspecialchars($fetched_image_url ?? ''); ?>" />
    <input type="hidden" name="existing_image" value="<?php echo htmlspecialchars($filename ?? ''); ?>" />
    <?php if($add ?? false){ ?>
        <div class="<?php if(($filename ?? '') == "") echo "hidden"; ?>" id="preview_container">
            <?php if(!empty($filename)): ?>
                <?php if(($is_temp ?? false) && !empty($temp_filename ?? '')): ?>
                    <!-- Temp image preview -->
                    <img class="preview image-preview" src="<?php echo htmlspecialchars($filename); ?>">
                    <input type="hidden" name="temp_filename" value="<?php echo htmlspecialchars($temp_filename); ?>">
                <?php else: ?>
                    <!-- Regular image preview -->
                    <img class="preview image-preview" src="/public/images/item-images/<?php echo "{$wishlist['id']}/{$filename}"; ?>">
                <?php endif; ?>
            <?php else: ?>
                <!-- Empty preview container - img will be created by JS if needed -->
                <img class="preview image-preview" style="display: none;">
            <?php endif; ?>
        </div>
    <?php }else{ ?>
        <div id="preview_container">
            <?php if(($has_new_image ?? false) && ($is_temp ?? false) && !empty($temp_filename ?? '')): ?>
                <!-- Temp image preview for edit form -->
                <img class="preview image-preview" src="<?php echo htmlspecialchars($filename); ?>">
                <input type="hidden" name="temp_filename" value="<?php echo htmlspecialchars($temp_filename); ?>">
            <?php else: ?>
                <!-- Regular existing image preview -->
                <img class="preview image-preview" src="/public/images/item-images/<?php echo "{$wishlist['id']}/" . ($filename ?? $item['image']); ?>">
            <?php endif; ?>
        </div>
    <?php } ?>

</div>
<div class="large-input">
    <label for="notes">Item Notes:<br></label>
    <textarea name="notes" placeholder="Needs to have 16GB RAM" id="notes" rows="4"><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
</div>
<div class="large-input">
    <label for="priority">How much do you want this item?</label><br>
    <select id="priority" name="priority">
        <option value="1" <?php if(($priority ?? '1') == "1") echo "selected"; ?>>(1) I absolutely need this item</option>
        <option value="2" <?php if(($priority ?? '1') == "2") echo "selected"; ?>>(2) I really want this item</option>
        <option value="3" <?php if(($priority ?? '1') == "3") echo "selected"; ?>>(3) It would be cool if I had this item</option>
        <option value="4" <?php if(($priority ?? '1') == "4") echo "selected"; ?>>I could always use this item</option>
    </select>
</div>

