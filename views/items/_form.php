<div class="large-input">
    <label for="name">Item Name:<br></label>
    <textarea required name="name" id="name" autocapitalize="words" rows="1" placeholder="New Gaming PC"><?php echo htmlspecialchars($item_name ?? ''); ?></textarea>
</div>
<div class="small-input">
    <label for="price">Item Price:<br></label>
    <div id="price-input-container">
        <span class="dollar-sign-input flex">
            <label for="price"><span class="dollar-sign">$</span></label>
            <input type="text" inputmode="decimal" name="price" pattern="(?=.*?\d)^(([1-9]\d{0,2}(,\d{3})*)|\d+)?(\.\d{1,2})?$" value="<?php echo htmlspecialchars($price ?? ''); ?>" id="price" class="price-input" required>
        </span>
        <span class="error-msg hidden">Item Price must match U.S. currency format: 9,999.00</span>
    </div>
</div>
<div class="small-input">
    <label for="quantity">Quantity Needed:<br></label>
    <div class="flex flex-center quantity-container">
        <input <?php if(($unlimited ?? 'No') == "No") echo "required"; ?> type="text" class="<?php if(($unlimited ?? 'No') == "Yes") echo "hidden"; ?>" name="quantity" id="quantity" inputmode="numeric" value="<?php echo htmlspecialchars($quantity ?? '1'); ?>" pattern="^\d+$" />
        <input type="checkbox" name="unlimited" value="Yes" <?php if(($unlimited ?? 'No') == "Yes") echo "checked"; ?> id="unlimited" />
        <label for="unlimited">Unlimited</label>
        <span class="error-msg hidden">Quantity must be a valid number</span>
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
    <input type="text" placeholder="Or paste an image here..." id="paste-image" />
    <?php if($add ?? false){ ?>
        <div class="<?php if(($filename ?? '') == "") echo "hidden"; ?>" id="preview_container">
            <img class="preview" src="">
        </div>
    <?php }else{ ?>
        <div id="preview_container">
            <img class="preview" src="/wishlist/public/images/item-images/<?php echo "{$wishlist['id']}/{$item['image']}"; ?>">
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
        <option value="4" <?php if(($priority ?? '1') == "4") echo "selected"; ?>>(4) Eh, I could do without this item</option>
    </select>
</div>

