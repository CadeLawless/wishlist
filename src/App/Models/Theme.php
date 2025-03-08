<?php

namespace App\Models;

use Core\Model;
use FilesystemIterator;

class Theme extends Model
{
    protected string $table = 'themes';
    protected string $siteImageFolderWebPath = "/wishlist1/public/assets/images/site-images";


    public function getBackgrounds(string $type): array
    {
        $findBackgrounds = $this->select("SELECT * FROM $this->table WHERE theme_type = 'Background' AND theme_tag = ? ORDER BY theme_name ASC", [$type]);
        return $findBackgrounds;
    }

    public function getThemeBackgrounds(string $homeDir): void
    {
        $siteImageFolderServerPath = "$homeDir/wishlist1/public/assets/images/site-images";
        if (isset($_POST["type"])) {
            $type = $_POST["type"];
            $findBackgrounds = $this->getBackgrounds(type: $type);
            if(count($findBackgrounds) > 0){
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
                            require("$siteImageFolderServerPath/menu-close.php");
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
                foreach($findBackgrounds as $row){
                    $background_id = $row["theme_id"];
                    $background_name = $row["theme_name"];
                    $background_image = $row["theme_image"];
                    $default_gift_wrap = $row["default_gift_wrap"];
                    echo "
                    <a class='theme popup-button' href='#'>
                        <img src='$this->siteImageFolderWebPath/themes/desktop-thumbnails/$background_image' class='theme-image desktop-theme-image' alt='$background_name theme' />
                        <img src='$this->siteImageFolderWebPath/themes/mobile-thumbnails/$background_image' class='theme-image mobile-theme-image' alt='$background_name theme' />
                        <div class='hover-title'>$background_name</div>
                    </a>
                    <div class='popup-container second center-items individual-theme-popup hidden'>
                        <div class='popup'>
                            <div class='close-container'>
                                <a href='#' class='close-button'>";
                                require("$siteImageFolderServerPath/menu-close.php");
                                echo "</a>
                            </div>
                            <div class='popup-content'>
                                <h2 style='margin-top: 0'>$background_name</h2>
                                <div class='theme-nav'>
                                    <a href='#' class='desktop active'>Desktop</a>
                                    <a href='#' class='mobile'>Mobile</a>
                                </div>
                                <div class='theme-picture'>
                                    <img class='desktop' src='$this->siteImageFolderWebPath/themes/desktop-thumbnails/$background_image' alt='$background_name desktop' />
                                    <img class='mobile hidden' src='$this->siteImageFolderWebPath/themes/mobile-thumbnails/$background_image' alt='$background_name desktop' />
                                </div>
                                <p class='center'><a class='select-theme button primary' data-default-gift-wrap='$default_gift_wrap' data-background-id='$background_id' data-background-image='$background_image' href='#'>Select Background</a></p>
                            </div>
                        </div>
                    </div>";
                }
            }
        }
    }

    public function getThemeBackgroundDropdownOptions(): void
    {
        if (isset($_POST["type"])) {
            $type = $_POST["type"];
            $findBackgrounds = $this->getBackgrounds(type: $type);
            if(count($findBackgrounds) > 0){
                echo "
                <div class='option default-background-option'>
                    <span class='value' data-background-image='default' data-background-id='0' data-default-gift-wrap='";
                    echo $type == "Birthday" ? "28" : "60";
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
                        <span class='preview-image desktop-background-image'><img src='$this->siteImageFolderWebPath/themes/desktop-thumbnails/$background_image' /></span>
                        <span class='preview-image mobile-background-image'><img src='$this->siteImageFolderWebPath/themes/mobile-thumbnails/$background_image' /></span>
                    </div>";
                }
            }
        }
    }

    public function getWrapImage(string $wrapID): string
    {
        $findGiftWrapImage = $this->select("SELECT theme_image FROM $this->table WHERE theme_type = 'Gift Wrap' AND theme_id = ?", [$wrapID]);
        return count($findGiftWrapImage) > 0 ? $findGiftWrapImage[0]["theme_image"] : "";
    }

    public function getThemeGiftWrapDropdownOptions(string $homeDir): void
    {
        $siteImageFolderServerPath = "$homeDir/wishlist1/public/assets/images/site-images";
        if (isset($_POST["type"])) {
            $type = $_POST["type"];
            $findGiftWraps = $this->select("SELECT * FROM $this->table WHERE theme_type = 'Gift Wrap' AND theme_tag = ? ORDER BY theme_name ASC", [$type]);
            if(count($findGiftWraps) > 0){
                foreach($findGiftWraps as $row){
                    $wrap_id = $row["theme_id"];
                    $wrap_name = $row["theme_name"];
                    $wrap_image = $row["theme_image"];
                    $wrap_folder_get_count = new FilesystemIterator("$siteImageFolderServerPath/themes/gift-wraps/$wrap_image", FilesystemIterator::SKIP_DOTS);
                    $number_of_wraps = iterator_count($wrap_folder_get_count);
                    echo "
                    <div class='option'>
                        <span class='value' data-wrap-image='$wrap_image' data-wrap-id='$wrap_id' data-number-of-files='$number_of_wraps'>$wrap_name</span>";
                        for($i=1; $i<=$number_of_wraps; $i++) {
                            if($i <= 6){
                                echo "<span class='preview-image'><img src='$this->siteImageFolderWebPath/themes/gift-wraps/$wrap_image/$i.png' /></span>";
                            }
                        }
                    echo "
                        <span class='recommended'>Recommended</span>
                    </div>";
                }
            }
}
    }
}

?>