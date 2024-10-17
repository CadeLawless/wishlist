<?php function write_theme_popup($type, bool $swap=false){ 
    global $db;
    $type_title = ucfirst($type);
    ?>
    <div class='popup-container <?php echo $type; ?> hidden'>
        <div class='popup fullscreen theme-popup-container'>
        <img class='background-theme desktop-background hidden' src="" />
        <img class='background-theme mobile-background hidden' src="" />
            <div class='close-container'>
                <a href='#' class='close-button'>
                <?php require("images/site-images/menu-close.php"); ?>
                </a>
            </div>
            <div class="theme-content">
                <h2 class="theme-header background-header" style="margin-top: 0;">Choose a Background</h2>
                <div class='popup-content choose-theme-popup'>
                    <div class="theme-list">
                        <?php
                        $findBackgrounds = $db->query("SELECT * FROM themes WHERE theme_type = 'Background' AND theme_tag = '$type_title' ORDER BY theme_name ASC");
                        if($findBackgrounds->num_rows > 0){
                            echo "
                            <a class='theme popup-button' href='#'>
                                <div class='theme-image desktop-theme-image default-background'></div>
                                <div class='theme-image mobile-theme-image default-background'></div>
                                <div class='hover-title'>Default</div>
                            </a>
                            <div class='popup-container second center-items individual-theme-popup hidden'>
                                <div class='popup'>
                                    <div class='close-container'>
                                        <a href='#' class='close-button'>";
                                        require("images/site-images/menu-close.php");
                                        echo "</a>
                                    </div>
                                    <div class='popup-content'>
                                        <h2 style='margin-top: 0'>Default</h2>
                                        <div class='theme-nav'>
                                            <a href='#' class='desktop active'>Desktop</a>
                                            <a href='#' class='mobile'>Mobile</a>
                                        </div>
                                        <div class='theme-picture'>
                                            <div style='background-color: var(--background); height: 300px; width: 100%; border: 1px solid var(--text);' class='desktop'></div>
                                            <div style='background-color: var(--background); height: 300px; width: 100%; max-width: 200px; border: 1px solid var(--text);' class='mobile hidden'></div>
                                        </div>
                                        <p class='center'><a class='select-theme button primary' data-default-gift-wrap='0' data-background-id='0' data-background-image='default' href='#'>Select Background</a></p>
                                    </div>
                                </div>
                            </div>";
                            while($row = $findBackgrounds->fetch_assoc()){
                                $background_id = $row["theme_id"];
                                $background_name = $row["theme_name"];
                                $background_image = $row["theme_image"];
                                $default_gift_wrap = $row["default_gift_wrap"];
                                echo "
                                <a class='theme popup-button' href='#'>
                                    <img src='images/site-images/themes/desktop-thumbnails/$background_image' class='theme-image desktop-theme-image' alt='$background_name theme' />
                                    <img src='images/site-images/themes/mobile-thumbnails/$background_image' class='theme-image mobile-theme-image' alt='$background_name theme' />
                                    <div class='hover-title'>$background_name</div>
                                </a>
                                <div class='popup-container second center-items individual-theme-popup hidden'>
                                    <div class='popup'>
                                        <div class='close-container'>
                                            <a href='#' class='close-button'>";
                                            require("images/site-images/menu-close.php");
                                            echo "</a>
                                        </div>
                                        <div class='popup-content'>
                                            <h2 style='margin-top: 0'>$background_name</h2>
                                            <div class='theme-nav'>
                                                <a href='#' class='desktop active'>Desktop</a>
                                                <a href='#' class='mobile'>Mobile</a>
                                            </div>
                                            <div class='theme-picture'>
                                                <img class='desktop' src='images/site-images/themes/desktop-thumbnails/$background_image' alt='$background_name desktop' />
                                                <img class='mobile hidden' src='images/site-images/themes/mobile-thumbnails/$background_image' alt='$background_name desktop' />
                                            </div>
                                            <p class='center'><a class='select-theme button primary' data-default-gift-wrap='$default_gift_wrap' data-background-id='$background_id' data-background-image='$background_image' href='#'>Select Background</a></p>
                                        </div>
                                    </div>
                                </div>";
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="gift-wrap-content hidden">
                <p style="padding-left: calc(5% - 10px); text-align: left;"><a class="button accent back-to" href="#">Back to Backgrounds</a></p>
                <h2 class="theme-header gift-wrap-header" style="margin-top: 0;">
                    <div>Choose a Gift Wrap<?php
                    if(!$swap){
                        echo "<a class='button primary continue-button' href='#'>Continue</a>";
                    }else{
                        echo "
                        <form action='' method='POST' style='display: inline;'>
                            <input type='hidden' id='theme_background_id' name='theme_background_id' value='' />
                            <input type='hidden' id='theme_gift_wrap_id' name='theme_gift_wrap_id' value='' />
                            <input type='submit' class='button primary continue-button' value='Confirm Change' name='theme_submit_button' />
                        </form>";
                    } ?>
                    </div>
                </h2>
                <div class='popup-content no-margin-top'>
                    <div class="theme-dropdown background-dropdown">
                        <strong>Background:</strong>
                        <div class="image-dropdown background" style="margin-bottom: 10px;">
                            <div class="selected-option">
                                <span class="value"></span>
                                <span class="preview-image desktop-image"></span>
                                <span class="preview-image mobile-image"></span>
                                <span class="popup-plus"><?php require("images/site-images/icons/plus.php"); ?></span>
                            </div>
                            <div class="options hidden">
                                <div class='close-container options-close'>
                                    <a href='#' class='close-button'>
                                    <?php require("images/site-images/menu-close.php"); ?>
                                    </a>
                                </div>
                                <div class="options-content">
                                <?php
                                if($findBackgrounds->num_rows > 0){
                                    echo "
                                    <div class='option default-background-option'>
                                        <span class='value' data-background-image='default' data-background-id='0' data-default-gift-wrap='";
                                        echo $type == "birthday" ? "28" : "60";
                                        echo "'>Default</span>
                                        <span class='preview-image desktop-background-image'><span class='default-background'></span></span>
                                        <span class='preview-image mobile-background-image'><span class='default-background'></span></span>
                                    </div>";
                                    foreach($findBackgrounds as $row){
                                        $background_id = $row["theme_id"];
                                        $background_name = $row["theme_name"];
                                        $background_image = $row["theme_image"];
                                        $default_gift_wrap = $row["default_gift_wrap"];
                                        echo "
                                        <div class='option'>
                                            <span class='value' data-background-image='$background_image' data-background-id='$background_id' data-default-gift-wrap='$default_gift_wrap'>$background_name</span>
                                            <span class='preview-image desktop-background-image'><img src='images/site-images/themes/desktop-thumbnails/$background_image' /></span>
                                            <span class='preview-image mobile-background-image'><img src='images/site-images/themes/mobile-thumbnails/$background_image' /></span>
                                        </div>";
                                    }
                                }
                                ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="theme-dropdown gift-wrap-dropdown">
                        <strong>Gift Wrap:</strong>
                        <div class="image-dropdown gift-wrap">
                            <div class="selected-option">
                                <span class="value"></span>
                                <span class="preview-image"></span>
                                <span class="popup-plus"><?php require("images/site-images/icons/plus.php"); ?></span>
                            </div>
                            <div class="options hidden">
                                <div class='close-container options-close'>
                                    <a href='#' class='close-button'>
                                    <?php require("images/site-images/menu-close.php"); ?>
                                    </a>
                                </div>
                                <div class="options-content">
                                <?php
                                $findGiftWraps = $db->query("SELECT * FROM themes WHERE theme_type = 'Gift Wrap' AND theme_tag = '$type_title' ORDER BY theme_name ASC");
                                if($findGiftWraps->num_rows > 0){
                                    while($row = $findGiftWraps->fetch_assoc()){
                                        $wrap_id = $row["theme_id"];
                                        $wrap_name = $row["theme_name"];
                                        $wrap_image = $row["theme_image"];
                                        $wrap_folder_get_count = new FilesystemIterator("images/site-images/themes/gift-wraps/$wrap_image", FilesystemIterator::SKIP_DOTS);
                                        $number_of_wraps = iterator_count($wrap_folder_get_count);
                                        echo "
                                        <div class='option'>
                                            <span class='value' data-wrap-image='$wrap_image' data-wrap-id='$wrap_id' data-number-of-files='$number_of_wraps'>$wrap_name</span>";
                                            for($i=1; $i<=$number_of_wraps; $i++) {
                                                if($i <= 6){
                                                    echo "<span class='preview-image'><img src='images/site-images/themes/gift-wraps/$wrap_image/$i.png' /></span>";
                                                }
                                            }
                                        echo "
                                            <span class='recommended'>Recommended</span>
                                        </div>";
                                    }
                                }
                                ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $wishlist_id = 20;
                    $pageNumber = 1;
                    $itemsPerPage = 12;
                    $type = "buyer";
                    $query = "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? ORDER BY CASE WHEN name = 'Item 1' THEN 1 WHEN name = 'Item 2' THEN 2 WHEN name = 'Item 3' THEN 3 WHEN name = 'Item 4' THEN 4 WHEN name = 'Item 5' THEN 5 ELSE 6 END";
                    require("paginate-sql.php");
                    if($selectQuery->num_rows > 0){
                        echo "<div class='items-list'>";
                        require("includes/write-template-items.php");
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>       
<?php } ?>