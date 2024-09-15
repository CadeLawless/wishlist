<?php
$priorities = [
    1 => "{$_SESSION["name"]} absolutely needs this item",
    2 => "{$_SESSION["name"]} really wants this item",
    3 => "It would be cool if {$_SESSION["name"]} had this item",
    4 => "Eh, {$_SESSION["name"]} could do without this item"
];
$gift_wrap = 1;

global $wrap_image, $ajax;
$filename = match($ajax){
    true => "../../images/site-images/themes/gift-wraps/$wrap_image",
    default => "images/site-images/themes/gift-wraps/$wrap_image",
};
$wrap_folder_get_count = new FilesystemIterator($filename, FilesystemIterator::SKIP_DOTS);
$number_of_wraps = iterator_count($wrap_folder_get_count);

while($row = $selectQuery->fetch_assoc()){
    $id = $row["id"];
    $item_name = htmlspecialchars($row["name"]);
    $item_name_short = htmlspecialchars(substr($row["name"], 0, 25));
    if(strlen($row["name"]) > 25) $item_name_short .= "...";
    $price = htmlspecialchars($row["price"]);
    $link = htmlspecialchars($row["link"]);
    $image = htmlspecialchars($row["image"]);
    $priority = htmlspecialchars($row["priority"]);
    $notes = htmlspecialchars($row["notes"]);
    $notes_short = htmlspecialchars(substr($row["notes"], 0, 30));
    $notes = $row["notes"] == "" ? "None" : $notes;
    $notes_short = $row["notes"] == "" ? "None" : $notes_short;
    if(strlen($row["notes"]) > 30) $notes_short .= "...";
    if($type == "buyer"){
        $purchased = $row["purchased"] == "Yes" ? true : false;
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
            <div class='item-image-container popup-button'>
                <img class='item-image' src='images/item-images/$wishlist_id/$image' alt='wishlist item image'>
            </div>
            <div class='popup-container image-popup-container hidden'>
                <div class='popup image-popup'>
                    <div class='close-container'>
                        <img src='images/site-images/menu-close.png' class='close-button'>
                    </div>
                    <img class='popup-image' src='images/item-images/$wishlist_id/$image' alt='wishlist item image'>
                </div>
            </div>";
        }
        echo "
        <div class='item-description'>
            <div class='line'><h3>$item_name_short</h3></div>
            <div class='line'><h4>Price: $$price <span class='price-date'>(as of $price_date)</span></h4></div>
            <div class='line'><h4 class='notes-label'>Notes: </h4><span>$notes_short</span></div>
            <div class='line'><h4 class='notes-label'>Priority: </h4><span>($priority) $priorities[$priority]</span></div>
            <div class='wishlist-options'>
                <a class='icon view popup-button' href='#'><div class='inline-popup'>View Item Information</div></a>
                <div class='popup-container hidden'>
                    <div class='popup fullscreen'>
                        <div class='close-container'>
                            <img src='images/site-images/menu-close.png' class='close-button'>
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
                <a class='icon link' href='$link' target='_blank'><div class='inline-popup'>View Item on Website</div></a>";
            if($type == "wisher"){
                echo "
                    <a class='icon edit edit-item' href='edit-item.php?id=$id&pageno=$pageNumber'><div class='inline-popup'>Edit Item</div></a>
                    <a class='icon x-delete popup-button' href='#'><div class='inline-popup'>Delete Item</div></a>
                    <div class='popup-container hidden'>
                        <div class='popup'>
                            <div class='close-container'>
                                <img src='images/site-images/menu-close.png' class='close-button'>
                            </div>
                            <div class='popup-content'>
                                <label>Are you sure you want to delete this item?</label>
                                <p>";
                                echo htmlspecialchars($row["name"]);
                                echo "</p>
                                <p class='center'><a class='button secondary no-button'>No</a><a class='button primary' href='delete-item.php?id=$id&pageno=$pageNumber'>Yes</a></p>
                            </div>
                        </div>
                    </div>
                </div>";
            }elseif($type == "buyer"){
                echo "</div>";
                if(!$purchased){
                    echo "
                    <div style='margin-top: 18px;' class='center'>
                        <input class='purchased-button popup-button' type='checkbox' id='$id'><label for='$id'> Mark as Purchased</label>
                        <div class='popup-container purchased-popup-$id hidden'>
                            <div class='popup'>
                                <div class='close-container'>
                                    <img src='images/site-images/menu-close.png' class='close-button'>
                                </div>
                                <div class='popup-content'>
                                    <label>Are you sure you want to mark this item as purchased?</label>
                                    <p>";
                                    echo htmlspecialchars($row["name"]);
                                    echo "</p>
                                    <p class='center'><a class='button secondary no-button'>No</a><a class='button primary purchase-button' id='purchase-$id'>Yes</a></p>
                                </div>
                            </div>
                        </div>
                    </div>";
                }else{
                    echo "
                    <br>
                    <div class='center'>
                        <h4 class='center'>This item has been purchased!</h4>
                        <span class='unmark-msg'>If you need to unmark an item as purchased, reach out to {$_SESSION["name"]} or a family member.</span>
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