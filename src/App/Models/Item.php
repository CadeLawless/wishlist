<?php

namespace App\Models;

use Core\Model;
use App\Models\Theme;
use FilesystemIterator;

class Item extends Model
{
    protected string $homeDir;
    protected string $table = 'items';
    protected array $priorities = [];
    protected int $gift_wrap = 1;

    public function __construct($homeDir)
    {
        parent::__construct();
        $this->homeDir = $homeDir;

        $name = $_SESSION["name"] ?? "User"; // Provide a fallback in case the session variable is not set

        $this->priorities = [
            1 => "$name absolutely needs this item",
            2 => "$name really wants this item",
            3 => "It would be cool if $name had this item",
            4 => "Eh, $name could do without this item"
        ];
    }

    public function writeTemplateItems(string $wrapID): void
    {
        $theme = new Theme();
        $wrapImage = $theme->getWrapImage($wrapID);
        $templateID = 20;
        $templateQuery = "SELECT *, $this->table.id as id FROM $this->table LEFT JOIN wishlists ON $this->table.wishlist_id = wishlists.id WHERE $this->table.wishlist_id = ? ORDER BY CASE WHEN name = 'Item 1' THEN 1 WHEN name = 'Item 2' THEN 2 WHEN name = 'Item 3' THEN 3 WHEN name = 'Item 4' THEN 4 WHEN name = 'Item 5' THEN 5 ELSE 6 END";
        $templateItems = $this->select(query: $templateQuery, values: [$templateID]);
        $this->writeItemsGrid(
            items: $templateItems,
            wrapImage: $wrapImage,
            template: true
        );
    }

    public function writeItemsGrid(
        array $items,
        string $type = 'buyer',
        string $wrapImage = '',
        bool $template = false
    ): void
    {
        if(count($items) > 0){
            echo "<div class='items-list'>";
            
            $siteImageFolderServerPath = "$this->homeDir/wishlist1/public/assets/images/site-images";
            $siteImageFolderWebPath = "/wishlist1/public/assets/images/site-images";
            $itemImageFolderWebPath = "/wishlist1/public/assets/images/item-images";

            $wrap_folder_get_count = new FilesystemIterator("$siteImageFolderServerPath/themes/gift-wraps/$wrapImage", FilesystemIterator::SKIP_DOTS);
            $number_of_wraps = iterator_count($wrap_folder_get_count);
            
            if(!$template){
                echo "
                <div class='popup-container image-popup-container hidden'>
                    <div class='popup image-popup'>
                        <div class='close-container transparent-background'>
                            <a href='#' class='close-button'>";
                            require("$siteImageFolderServerPath/menu-close.php");
                            echo "</a>
                        </div>
                        <img class='popup-image' src='' alt='wishlist item image'>
                    </div>
                </div>";
            }

            foreach($items as $row){
                $id = $row["id"];
                $item_name = htmlspecialchars($row["name"]);
                $item_name_short = htmlspecialchars(mb_substr($row["name"], 0, 25));
                if(strlen($row["name"]) > 25) $item_name_short .= "...";
                $price = htmlspecialchars($row["price"]);
                $link = htmlspecialchars($row["link"]);
                $priority = htmlspecialchars($row["priority"]);
                $notes = htmlspecialchars($row["notes"]);
                $notes_short = htmlspecialchars(mb_substr($row["notes"], 0, 30));
                $notes = $row["notes"] == "" ? "None" : $notes;
                $notes_short = $row["notes"] == "" ? "None" : $notes_short;
                if(strlen($row["notes"]) > 30) $notes_short .= "...";
                $date_added = htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_added"])));
                $date_modified = $row["date_modified"] == NULL ? "" : htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_modified"])));
                $price_date = $date_modified == "" ? htmlspecialchars(date("n/j/Y", strtotime($date_added))) : htmlspecialchars(date("n/j/Y", strtotime($date_modified)));
                $quantity = $row["quantity"] != "" ? htmlspecialchars($row["quantity"]) : "";
                $unlimited = $row["unlimited"] == "Yes" ? true : false;
                if(!$template){
                    $copy_id = $row["copy_id"];
                    $image = htmlspecialchars($row["image"]);
                    $image_path = "$image_folder/item-images/$wishlist_id/{$row["image"]}";
                    if(!file_exists($image_path)){
                        $image_path = "$siteImageFolderWebPath/default-photo.png";
                    }else{
                        $image_path = "$itemImageFolderWebPath/$wishlist_id/$image";
                    }
                    $purchased = $row["purchased"] == "Yes" ? true : false;
                }
                if($template || $type == "buyer"){
                    $quanity_purchased = htmlspecialchars($row["quantity_purchased"]);
                    $quantity = $quantity - $quanity_purchased;
                    if($quantity < 0) $quantity = 0;
                    if($this->gift_wrap == $number_of_wraps) $this->gift_wrap = 1;
                }
                if($type == "wisher"){
                    echo "<div class='item-container'>";
                }elseif($type == "buyer" || $template){
                    echo "<div class='item-container'>";
                        if($template || $purchased){
                            echo "<img src='";
                            if($wrapImage != ""){
                                echo "$siteImageFolderWebPath/themes/gift-wraps/$wrapImage/$this->gift_wrap.png";
                            }
                            echo "' class='gift-wrap' alt='gift wrap'>";
                            $this->gift_wrap++;
                        }
                    }
                    if(!$template && ($type == "wisher" || ($type == "buyer" && !$purchased))){
                        echo "
                        <div class='item-image-container image-popup-button'>
                            <img class='item-image' src='$image_path?t=" . time() . "' alt='wishlist item image'>
                        </div>";
                    }
                    echo "
                    <div class='item-description'>
                        <div class='line'><h3>$item_name_short</h3></div>
                        <div class='line'><h4>Price: $$price <span class='price-date'>(as of $price_date)</span></h4></div>
                        <div class='line'><h4 class='notes-label'>Quantity Needed:</h4> ";
                        echo $unlimited ? "Unlimited" : $quantity;
                        echo "
                        </div>
                        <div class='line'><h4 class='notes-label'>Notes: </h4><span>$notes_short</span></div>
                        <div class='line'><h4 class='notes-label'>Priority: </h4><span>($priority) " . $this->priorities[$priority] . "</span></div>
                        <div class='icon-options item-options $type-item-options'>
                            <a class='icon-container popup-button' href='#'>";
                            require("$siteImageFolderServerPath/icons/view.php");
                            echo "<div class='inline-label'>View</div></a>";
                            if(!$template){
                                echo "
                                <div class='popup-container hidden'>
                                    <div class='popup fullscreen'>
                                        <div class='close-container'>
                                            <a href='#' class='close-button'>";
                                            require("$siteImageFolderServerPath/menu-close.php");
                                            echo "</a>
                                        </div>
                                        <div class='popup-content'>
                                            <h2 style='margin-top: 0;'>Item Details</h2>
                                            <p><label>Item Name:<br /></label>$item_name</p>
                                            <label>Item Price:<br /></label>$$price</p>
                                            <label>Website Link:<br /></label><a target='_blank' href='$link'>View on Website</a></p>
                                            <label>Notes: </label><br />" . nl2br($notes) . "</p>
                                            <label>Priority:<br /></label>($priority) " . $this->priorities[$priority] . "</p>
                                            <label>Date Added:<br /></label>$date_added</p>";
                                            if($date_modified != "") echo "<p><label>Last Date Modified:</label><br />$date_modified</p>";
                                            echo "
                                        </div>
                                    </div>
                                </div>";
                            }
                            echo "<a class='icon-container' href='$link' target='_blank'>";
                            require("$siteImageFolderServerPath/icons/link.php");
                            echo "<div class='inline-label'>Website Link</div></a>";
                        if(!$template && $type == "wisher"){
                            echo "
                                <a class='icon-container' href='edit-item.php?id=$id&pageno=$pageNumber'>";
                                require("$siteImageFolderServerPath/icons/edit.php");
                                echo "<div class='inline-label'>Edit</div></a>
                                <a class='icon-container popup-button' href='#'>";
                                require("$siteImageFolderServerPath/icons/delete-x.php");
                                echo "<div class='inline-label'>Delete</div></a>
                                <div class='popup-container hidden'>
                                    <div class='popup'>
                                        <div class='close-container'>
                                            <a href='#' class='close-button'>";
                                            require("$siteImageFolderServerPath/menu-close.php");
                                            echo "</a>
                                        </div>
                                        <div class='popup-content'>";
                                            if($copy_id == ""){
                                                echo "
                                                <label>Are you sure you want to delete this item?</label>
                                                <p>" . htmlspecialchars($row["name"]) . "</p>
                                                <div style='margin: 16px 0;' class='center'>
                                                    <a class='button secondary no-button' href='#'>No</a>";
                                                if(!$purchased){
                                                    echo "<a class='button primary' href='delete-item.php?id=$id&pageno=$pageNumber'>Yes</a>";
                                                }else{
                                                    echo "
                                                    <a class='button primary popup-button' href='#'>Yes</a>
                                                    <div class='popup-container first hidden'>
                                                        <div class='popup'>
                                                            <div class='close-container'>
                                                                <a href='#' class='close-button'>";
                                                                require("$siteImageFolderServerPath/menu-close.php");
                                                                echo "</a>
                                                            </div>
                                                            <div class='popup-content'>
                                                                <p><strong>NOTE: This item has already been marked as purchased.</strong></p>
                                                                <label>Are you REALLY sure you want to delete this item?</label>
                                                                <div style='margin: 16px 0;'>";
                                                                echo htmlspecialchars($row["name"]);
                                                                echo "</p>
                                                                <p class='center'>
                                                                    <a class='button secondary no-button double-no' href='#'>No</a>
                                                                    <a class='button primary' href='delete-item.php?id=$id&pageno=$pageNumber'>Yes</a>
                                                                </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>";
                                                }
                                            }else{
                                                echo "
                                                <label>This item has been copied to or from other wish list(s). Do you want to delete it from this list only or from ALL lists?</label>
                                                <p>" . htmlspecialchars($row["name"]) . "</p>
                                                <div style='margin: 16px 0;' class='center'>
                                                    <a class='button secondary popup-button' style='margin-right: 30px;' href='#'>Delete from this list only</a>
                                                    <div class='popup-container hidden'>
                                                        <div class='popup'>
                                                            <div class='close-container'>
                                                                <a href='#' class='close-button'>";
                                                                require("$siteImageFolderServerPath/menu-close.php");
                                                                echo "</a>
                                                            </div>
                                                            <div class='popup-content'>
                                                                <label>Are you sure you want to delete this item from this wish list only?</label>
                                                                <div style='margin: 16px 0;'>";
                                                                echo htmlspecialchars($row["name"]);
                                                                echo "</p>
                                                                <p class='center'>
                                                                    <a class='button secondary no-button double-no' href='#'>No</a>
                                                                    <a class='button primary' href='delete-item.php?id=$id&pageno=$pageNumber'>Yes</a>
                                                                </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a class='button secondary popup-button' href='#'>Delete from ALL lists</a>
                                                    <div class='popup-container hidden'>
                                                        <div class='popup'>
                                                            <div class='close-container'>
                                                                <a href='#' class='close-button'>";
                                                                require("$siteImageFolderServerPath/menu-close.php");
                                                                echo "</a>
                                                            </div>
                                                            <div class='popup-content'>
                                                                <label>Are you sure you want to delete this item from ALL lists?</label>
                                                                <div style='margin: 16px 0;'>";
                                                                echo htmlspecialchars($row["name"]);
                                                                echo "</p>
                                                                <p class='center'>
                                                                    <a class='button secondary no-button double-no' href='#'>No</a>
                                                                    <a class='button primary' href='delete-item.php?id=$id&pageno=$pageNumber&deleteAll=yes'>Yes</a>
                                                                </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>";
                                            }
                                            echo "
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>";
                            /* if(!$unlimited){
                                echo "
                                <div style='margin-top: 18px;' class='center'>
                                    <a class='button secondary popup-button' href='#'>Unmark as purchased</a>
                                    <div class='popup-container hidden'>
                                        <div class='popup'>
                                            <div class='close-container'>
                                                <a href='#' class='close-button'>";
                                                require("$siteImageFolderServerPath/menu-close.php");
                                                echo "</a>
                                            </div>
                                            <div class='popup-content'>
                                                <p>If this item has been purchased, unmarking this item will make it available for others to mark as purchased again.</p>
                                                <label>Are you sure you want to unmark this item as purchased?</label>
                                                <p>";
                                                echo htmlspecialchars($row["name"]);
                                                echo "</p>
                                                <p class='center'><a class='button secondary no-button' href='#'>No</a><a class='button primary' href='unmark-item.php?id=$id&pageno=$pageNumber'>Yes</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>";
                            } */
                        }elseif($template || $type == "buyer"){
                            echo "</div>";
                            if(!$template && !$purchased){
                                if($unlimited == "Yes"){
                                    echo "
                                    <br />
                                    <div class='center'>
                                        <h4 class='center'>If you buy this item, there is no need to mark it as purchased.</h4>
                                        <span class='unmark-msg'>This item has an unlimited quanity needed.</span>
                                    </div>";
                                }else{
                                    echo "
                                    <div style='margin-top: 18px;' class='center'>
                                        <input class='purchased-button popup-button' type='checkbox' id='$id'><label for='$id'> Mark as Purchased</label>
                                        <div class='popup-container purchased-popup-$id hidden'>
                                            <div class='popup'>
                                                <div class='close-container'>
                                                    <a href='#' class='close-button'>";
                                                    require("$siteImageFolderServerPath/menu-close.php");
                                                    echo "</a>
                                                </div>
                                                <div class='popup-content'>
                                                    <label>Are you sure you want to mark this item as purchased?</label>
                                                    <p>";
                                                    echo htmlspecialchars($row["name"]);
                                                    echo "</p>
                                                    <p class='center'><a class='button secondary no-button' href='#'>No</a><a class='button primary purchase-button' href='#' id='purchase-$id'>Yes</a></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>";
                                }
                            }else{
                                echo "
                                <br>
                                <div class='center'>
                                    <h4 class='center'>This item has been purchased!</h4>
                                    <span class='unmark-msg'>If you need to unmark an item as purchased, email <a href='mailto:support@cadelawless.com'>support@cadelawless.com</a> for help.</span>
                                </div>";
                            }
                        }
                        echo "<p class='date-added center'><em>";
                        if($row["date_modified"] == NULL){
                            echo "Date Added: $date_added";
                        }else{
                            echo "Last Modified: $date_modified";
                        }
                        echo "</em></p>
                    </div>
                </div>";
            }
        }

    }
}

?>