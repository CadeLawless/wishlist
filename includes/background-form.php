<div class="large-input">
    <label for="tag">Tag:<br></label>
    <select required name="tag" id="tag">
        <?php
        foreach($tag_options as $opt){
            echo "<option value='$opt'";
            if($tag == $opt) echo " selected";
            echo ">$opt</option>";
        }
        ?>
    </select>
</div>
<div class="large-input">
    <label for="name">Background Name:<br></label>
    <input required type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" />
</div>
<div class="large-input">
    <label for="image_name">Image Name:<br></label>
    <input required type="text" name="image_name" id="image_name" value="<?php echo htmlspecialchars($image_name); ?>" />
</div>
<div class="large-input">
    <label for="image">Desktop Image:<br></label>
    <a class="file-input">Choose Image</a>
    <input required type="file" name="desktop_image" class="hidden" id="desktop_image" accept=".png" />
    <?php if($add){ ?>
        <div class="<?php if($image_name == "") echo "hidden"; ?>" class="preview_container">
            <img class="preview" src="">
        </div>
    <?php }else{ ?>
        <div id="preview_container">
            <img class="preview" src="images/site-images/themes/desktop-backgrounds<?php echo "/$image_name"; ?>">
        </div>
    <?php } ?>
</div>
<div class="large-input">
    <label for="image">Desktop Thumbnail:<br></label>
    <a class="file-input">Choose Image</a>
    <input required type="file" name="desktop_thumbnail" class="hidden" id="desktop_thumbnail" accept=".png" />
    <?php if($add){ ?>
        <div class="<?php if($image_name == "") echo "hidden"; ?>" class="preview_container">
            <img class="preview" src="">
        </div>
    <?php }else{ ?>
        <div id="preview_container">
            <img class="preview" src="images/site-images/themes/desktop-thumbnails<?php echo "/$image_name"; ?>">
        </div>
    <?php } ?>
</div>
<div class="large-input">
    <label for="image">Mobile Image:<br></label>
    <a class="file-input">Choose Image</a>
    <input required type="file" name="mobile_image" class="hidden" id="mobile_image" accept=".png" />
    <?php if($add){ ?>
        <div class="<?php if($image_name == "") echo "hidden"; ?>" class="preview_container">
            <img class="preview" src="">
        </div>
    <?php }else{ ?>
        <div id="preview_container">
            <img class="preview" src="images/site-images/themes/mobile-backgrounds<?php echo "/$image_name"; ?>">
        </div>
    <?php } ?>
</div>
<div class="large-input">
    <label for="image">Mobile Thumbnail:<br></label>
    <a class="file-input">Choose Image</a>
    <input required type="file" name="mobile_thumbnail" class="hidden" id="mobile_thumbnail" accept=".png" />
    <?php if($add){ ?>
        <div class="<?php if($image_name == "") echo "hidden"; ?>" class="preview_container">
            <img class="preview" src="">
        </div>
    <?php }else{ ?>
        <div id="preview_container">
            <img class="preview" src="images/site-images/themes/mobile-thumbnails<?php echo "/$image_name"; ?>">
        </div>
    <?php } ?>
</div>
<div class="large-input">
    <label for="default_gift_wrap">Default Gift Wrap ID:<br></label>
    <input required type="text" name="default_gift_wrap" id="default_gift_wrap" value="<?php echo htmlspecialchars($default_gift_wrap_id); ?>" />
</div>
