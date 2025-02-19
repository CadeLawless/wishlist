<?php
$priorities = [
    1 => "{$_SESSION["name"]} absolutely needs this item",
    2 => "{$_SESSION["name"]} really wants this item",
    3 => "It would be cool if {$_SESSION["name"]} had this item",
    4 => "Eh, {$_SESSION["name"]} could do without this item"
];
$gift_wrap = 1;

global $wrap_image, $ajax;
$image_folder = match($ajax){
    true => "../../images",
    default => "images",
};
$wrap_folder_get_count = new FilesystemIterator("$image_folder/site-images/themes/gift-wraps/$wrap_image", FilesystemIterator::SKIP_DOTS);
$number_of_wraps = iterator_count($wrap_folder_get_count);

echo "
<div class='popup-container image-popup-container hidden'>
    <div class='popup image-popup'>
        <div class='close-container transparent-background'>
            <a href='#' class='close-button'>";
            require("$image_folder/site-images/menu-close.php");
            echo "</a>
        </div>
        <img class='popup-image' src='' alt='wishlist item image'>
    </div>
</div>";

while($row = $selectQuery->fetch_assoc()){
    $id = $row["id"];
    $copy_id = $row["copy_id"];
    $item_name = htmlspecialchars($row["name"]);
    $item_name_short = htmlspecialchars(mb_substr($row["name"], 0, 25));
    if(strlen($row["name"]) > 25) $item_name_short .= "...";
    $price = htmlspecialchars($row["price"]);
    $quantity = $row["quantity"] != "" ? htmlspecialchars($row["quantity"]) : "";
    $unlimited = $row["unlimited"] == "Yes" ? true : false;
    $link = htmlspecialchars($row["link"]);
    $image = htmlspecialchars($row["image"]);
    $image_path = "$image_folder/item-images/$wishlist_id/{$row["image"]}";
    if(!file_exists($image_path)){
        $image_path = "images/site-images/default-photo.png";
    }else{
        $image_path = "images/item-images/$wishlist_id/$image";
    }
    $priority = htmlspecialchars($row["priority"]);
    $notes = htmlspecialchars($row["notes"]);
    $notes_short = htmlspecialchars(mb_substr($row["notes"], 0, 30));
    $notes = $row["notes"] == "" ? "None" : $notes;
    $notes_short = $row["notes"] == "" ? "None" : $notes_short;
    if(strlen($row["notes"]) > 30) $notes_short .= "...";
    $purchased = $row["purchased"] == "Yes" ? true : false;
    if($type == "buyer"){
        $quanity_purchased = htmlspecialchars($row["quantity_purchased"]);
        $quantity = $quantity - $quanity_purchased;
        if($quantity < 0) $quantity = 0;
        if($gift_wrap == $number_of_wraps) $gift_wrap = 1;
    }
    $date_added = htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_added"])));
    $date_modified = $row["date_modified"] == NULL ? "" : htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_modified"])));
    $price_date = $date_modified == "" ? htmlspecialchars(date("n/j/Y", strtotime($date_added))) : htmlspecialchars(date("n/j/Y", strtotime($date_modified)));
    if($type == "wisher"){
        echo "<div class='item-container'>";
    }elseif($type == "buyer"){
        echo "<div class='item-container'>";
            if($purchased){
                echo "<img src='images/site-images/themes/gift-wraps/$wrap_image/$gift_wrap.png' class='gift-wrap' alt='gift wrap'>";
                $gift_wrap++;
            }
        }
        if($type == "wisher" || ($type == "buyer" && !$purchased)){
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
            <div class='line'><h4 class='notes-label'>Priority: </h4><span>($priority) $priorities[$priority]</span></div>
            <div class='icon-options item-options $type-item-options'>
                <a class='icon-container popup-button' href='#'>";
                require("$image_folder/site-images/icons/view.php");
                echo "<div class='inline-label'>View</div></a>
                <div class='popup-container hidden'>
                    <div class='popup fullscreen'>
                        <div class='close-container'>
                            <a href='#' class='close-button'>";
                            require("$image_folder/site-images/menu-close.php");
                            echo "</a>
                        </div>
                        <div class='popup-content'>
                            <h2 style='margin-top: 0;'>Item Details</h2>
                            <p><label>Item Name:<br /></label>$item_name</p>
                            <label>Item Price:<br /></label>$$price</p>
                            <label>Website Link:<br /></label><a target='_blank' href='$link'>View on Website</a></p>
                            <label>Notes: </label><br />" . nl2br($notes) . "</p>
                            <label>Priority:<br /></label>($priority) $priorities[$priority]</p>
                            <label>Date Added:<br /></label>$date_added</p>";
                            if($date_modified != "") echo "<p><label>Last Date Modified:</label><br />$date_modified</p>";
                            echo "
                        </div>
                    </div>
                </div>
                <a class='icon-container' href='$link' target='_blank'>";
                require("$image_folder/site-images/icons/link.php");
                echo "<div class='inline-label'>Website Link</div></a>";
            if($type == "wisher"){
                echo "
                    <a class='icon-container' href='edit-item.php?id=$id&pageno=$pageNumber'>";
                    require("$image_folder/site-images/icons/edit.php");
                    echo "<div class='inline-label'>Edit</div></a>
                    <a class='icon-container popup-button' href='#'>";
                    require("$image_folder/site-images/icons/delete-x.php");
                    echo "<div class='inline-label'>Delete</div></a>
                    <div class='popup-container hidden'>
                        <div class='popup'>
                            <div class='close-container'>
                                <a href='#' class='close-button'>";
                                require("$image_folder/site-images/menu-close.php");
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
                                                    require("$image_folder/site-images/menu-close.php");
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
                                                    require("$image_folder/site-images/menu-close.php");
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
                                                    require("$image_folder/site-images/menu-close.php");
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
                                    require("$image_folder/site-images/menu-close.php");
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
            }elseif($type == "buyer"){
                echo "</div>";
                if(!$purchased){
                    if($unlimited == "Yes"){
                        echo "
                        <br>
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
                                        require("$image_folder/site-images/menu-close.php");
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
?>