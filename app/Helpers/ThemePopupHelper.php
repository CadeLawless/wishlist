<?php

namespace App\Helpers;

use App\Services\ThemeService;

class ThemePopupHelper
{
    public static function renderThemePopup(string $type, bool $swap = false): string
    {
        $typeTitle = ucfirst($type);
        $backgrounds = ThemeService::getBackgroundThemes($typeTitle);
        $giftWraps = ThemeService::getGiftWrapThemes($typeTitle);
        
        ob_start();
        ?>
        <div class='popup-container first <?php echo $type; ?> hidden'>
            <div class='popup fullscreen theme-popup-container'>
                <img class='background-theme desktop-background hidden' src="" />
                <img class='background-theme mobile-background hidden' src="" />
                <div class='close-container'>
                    <a href='#' class='close-button'>
                        <?php require(__DIR__ . "/../../images/site-images/menu-close.php"); ?>
                    </a>
                </div>
                <div class="theme-content">
                    <h2 class="theme-header background-header" style="margin-top: 0;">Choose a Background</h2>
                    <div class='popup-content choose-theme-popup'>
                        <div class="theme-list">
                            <?php
                            // Default background option
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
                                        require(__DIR__ . "/../../images/site-images/menu-close.php");
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
                            
                            // Background themes
                            foreach ($backgrounds as $row) {
                                $backgroundId = $row["theme_id"];
                                $backgroundName = $row["theme_name"];
                                $backgroundImage = $row["theme_image"];
                                $defaultGiftWrap = $row["default_gift_wrap"];
                                echo "
                                <a class='theme popup-button' href='#'>
                                    <img src='images/site-images/themes/desktop-thumbnails/$backgroundImage' class='theme-image desktop-theme-image' alt='$backgroundName theme' />
                                    <img src='images/site-images/themes/mobile-thumbnails/$backgroundImage' class='theme-image mobile-theme-image' alt='$backgroundName theme' />
                                    <div class='hover-title'>$backgroundName</div>
                                </a>
                                <div class='popup-container second center-items individual-theme-popup hidden'>
                                    <div class='popup'>
                                        <div class='close-container'>
                                            <a href='#' class='close-button'>";
                                            require(__DIR__ . "/../../images/site-images/menu-close.php");
                                            echo "</a>
                                        </div>
                                        <div class='popup-content'>
                                            <h2 style='margin-top: 0'>$backgroundName</h2>
                                            <div class='theme-nav'>
                                                <a href='#' class='desktop active'>Desktop</a>
                                                <a href='#' class='mobile'>Mobile</a>
                                            </div>
                                            <div class='theme-picture'>
                                                <img class='desktop' src='images/site-images/themes/desktop-thumbnails/$backgroundImage' alt='$backgroundName desktop' />
                                                <img class='mobile hidden' src='images/site-images/themes/mobile-thumbnails/$backgroundImage' alt='$backgroundName desktop' />
                                            </div>
                                            <p class='center'><a class='select-theme button primary' data-default-gift-wrap='$defaultGiftWrap' data-background-id='$backgroundId' data-background-image='$backgroundImage' href='#'>Select Background</a></p>
                                        </div>
                                    </div>
                                </div>";
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
                        <div class='theme-header' style="margin: 0 0 15px; width: auto;">Gift wraps will be displayed over any purchased item images. You will never see the gift wraps. Only the people who purchase items off your wish list will see them.</div>
                        <div class="theme-dropdown background-dropdown">
                            <strong>Background:</strong>
                            <div class="image-dropdown background" style="margin-bottom: 10px;">
                                <div class="selected-option">
                                    <span class="value"></span>
                                    <span class="preview-image desktop-image"></span>
                                    <span class="preview-image mobile-image"></span>
                                    <span class="popup-plus"><?php require(__DIR__ . "/../../images/site-images/icons/plus.php"); ?></span>
                                </div>
                                <div class="options hidden">
                                    <div class='close-container options-close'>
                                        <a href='#' class='close-button'>
                                        <?php require(__DIR__ . "/../../images/site-images/menu-close.php"); ?>
                                        </a>
                                    </div>
                                    <div class="options-content">
                                    <?php
                                    // Default background option
                                    echo "
                                    <div class='option default-background-option'>
                                        <span class='value' data-background-image='default' data-background-id='0' data-default-gift-wrap='";
                                        echo $type == "birthday" ? "28" : "60";
                                        echo "'>Default</span>
                                        <span class='preview-image desktop-background-image'><span class='default-background'></span></span>
                                        <span class='preview-image mobile-background-image'><span class='default-background'></span></span>
                                    </div>";
                                    
                                    // Background options
                                    foreach ($backgrounds as $row) {
                                        $backgroundId = $row["theme_id"];
                                        $backgroundName = $row["theme_name"];
                                        $backgroundImage = $row["theme_image"];
                                        $defaultGiftWrap = $row["default_gift_wrap"];
                                        echo "
                                        <div class='option'>
                                            <span class='value' data-background-image='$backgroundImage' data-background-id='$backgroundId' data-default-gift-wrap='$defaultGiftWrap'>$backgroundName</span>
                                            <span class='preview-image desktop-background-image'><img src='images/site-images/themes/desktop-thumbnails/$backgroundImage' /></span>
                                            <span class='preview-image mobile-background-image'><img src='images/site-images/themes/mobile-thumbnails/$backgroundImage' /></span>
                                        </div>";
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
                                    <span class="popup-plus"><?php require(__DIR__ . "/../../images/site-images/icons/plus.php"); ?></span>
                                </div>
                                <div class="options hidden">
                                    <div class='close-container options-close'>
                                        <a href='#' class='close-button'>
                                        <?php require(__DIR__ . "/../../images/site-images/menu-close.php"); ?>
                                        </a>
                                    </div>
                                    <div class="options-content">
                                    <?php
                                    foreach ($giftWraps as $row) {
                                        $wrapId = $row["theme_id"];
                                        $wrapName = $row["theme_name"];
                                        $wrapImage = $row["theme_image"];
                                        $numberOfWraps = ThemeService::getGiftWrapFileCount($wrapImage);
                                        echo "
                                        <div class='option'>
                                            <span class='value' data-wrap-image='$wrapImage' data-wrap-id='$wrapId' data-number-of-files='$numberOfWraps'>$wrapName</span>";
                                        for($i=1; $i<=$numberOfWraps; $i++) {
                                            if($i <= 6){
                                                echo "<span class='preview-image'><img src='images/site-images/themes/gift-wraps/$wrapImage/$i.png' /></span>";
                                            }
                                        }
                                        echo "
                                            <span class='recommended'>Recommended</span>
                                        </div>";
                                    }
                                    ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        // Template preview items with gift wrap functionality
                        // Use the same default gift wrap IDs as the original system
                        $defaultWrapImage = $type == "birthday" ? "28" : "60"; // Default gift wrap IDs
                        echo "<div class='items-list preview'>
                            <div class='item-container'>
                                <div class='item-image-container'>
                                    <div class='default-background' style='width: 100px; height: 100px; background-color: var(--background); border: 1px solid var(--text);'></div>
                                    <img src='images/site-images/themes/gift-wraps/$defaultWrapImage/1.png' class='gift-wrap' alt='gift wrap'>
                                </div>
                                <div class='item-description'>
                                    <div class='line'><h3>Sample Item 1</h3></div>
                                    <div class='line'><h4>Price: $25.99</h4></div>
                                    <div class='line'><p>This is how your items will look with the selected theme.</p></div>
                                </div>
                            </div>
                            <div class='item-container'>
                                <div class='item-image-container'>
                                    <div class='default-background' style='width: 100px; height: 100px; background-color: var(--background); border: 1px solid var(--text);'></div>
                                    <img src='images/site-images/themes/gift-wraps/$defaultWrapImage/2.png' class='gift-wrap' alt='gift wrap'>
                                </div>
                                <div class='item-description'>
                                    <div class='line'><h3>Sample Item 2</h3></div>
                                    <div class='line'><h4>Price: $15.50</h4></div>
                                    <div class='line'><p>Gift wraps will appear on purchased items.</p></div>
                                </div>
                            </div>
                            <div class='item-container'>
                                <div class='item-image-container'>
                                    <div class='default-background' style='width: 100px; height: 100px; background-color: var(--background); border: 1px solid var(--text);'></div>
                                    <img src='images/site-images/themes/gift-wraps/$defaultWrapImage/3.png' class='gift-wrap' alt='gift wrap'>
                                </div>
                                <div class='item-description'>
                                    <div class='line'><h3>Sample Item 3</h3></div>
                                    <div class='line'><h4>Price: $42.00</h4></div>
                                    <div class='line'><p>Different gift wrap variations will cycle through.</p></div>
                                </div>
                            </div>
                        </div>";
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
