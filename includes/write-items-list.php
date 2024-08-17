<?php
$priorities = [
    1 => "{$_SESSION["name"]} absolutely needs this item",
    2 => "{$_SESSION["name"]} really wants this item",
    3 => "It would be cool if {$_SESSION["name"]} had this item",
    4 => "Eh, {$_SESSION["name"]} could do without this item"
];
$bow = 1;
$gift_wrap = 1;

while($row = $selectQuery->fetch_assoc()){
    $id = $row["id"];
    $item_name = htmlspecialchars(substr($row["name"], 0, 25));
    if(strlen($row["name"]) > 25) $item_name .= "...";
    $price = htmlspecialchars($row["price"]);
    $link = htmlspecialchars($row["link"]);
    $image = htmlspecialchars($row["image"]);
    $priority = htmlspecialchars($row["priority"]);
    $notes = htmlspecialchars(substr($row["notes"], 0, 30));
    $notes = $notes == "" ? "None" : $notes;
    if(strlen($row["notes"]) > 30) $notes .= "...";
    if($type == "buyer"){
        $purchased = $row["purchased"] == "Yes" ? true : false;
        if($bow == 7) $bow = 1;
        if($gift_wrap == 13) $gift_wrap = 1;
    }
    $date_added = $row["date_modified"] == NULL ? htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_added"]))) : htmlspecialchars(date("n/j/Y g:i A", strtotime($row["date_modified"])));
    $price_date = $row["date_modified"] == NULL ? htmlspecialchars(date("n/j/Y", strtotime($row["date_added"]))) : htmlspecialchars(date("n/j/Y", strtotime($row["date_modified"])));
    if($type == "wisher"){
        echo "<div class='item-container'>";
    }elseif($type == "buyer"){
        echo "<div class='item-container' style='";
        if($purchased) echo "padding-top: 0";
        echo "'>";
            if($purchased){
                echo "<img src='images/site-images/gift-wrap-$gift_wrap.png' class='gift-wrap' alt='gift wrap'>";
                $gift_wrap++;
            }else{
                echo "<img class='bow' src='images/site-images/bow-$bow.png' alt='bow'>";
                $bow++;
            }
        }
        echo "
        <div class='item-image-container'>
            <img class='item-image' src='images/item-images/{$_SESSION["wishlist_id"]}/$image' alt='wishlist item image'>
        </div>
        <div class='item-description'>
            <h3>$item_name</h3>
            <h4>Price: $$price <span class='price-date'>(as of $price_date)</span></h4>
            <h4 class='notes-label'>Notes: </h4><span>$notes</span><br>
            <h4 class='notes-label'>Priority: </h4><span>($priority) $priorities[$priority]</span><br>";
            if($type == "wisher"){
                echo "
                <p class='center'>
                    <a class='view-button' href='view-item.php?id=$id&pageno=$pageNumber'>View Item</a>
                    <a class='link-button' href='$link' target='_blank'>View Item on Website</a>
                </p>
                <p class='center'>
                    <a class='edit-button' href='edit-item.php?id=$id&pageno=$pageNumber'>Edit Item</a>
                    <a class='delete-button popup-button' id='$id'>Delete Item</a>
                    <div class='popup-container delete-popup-$id hidden'>
                        <div class='popup'>
                            <div class='close-container'>
                                <img src='images/site-images/close.png' class='close-button'>
                            </div>
                            <div class='popup-content'>
                                <label>Are you sure you want to delete this item?</label>
                                <p>";
                                echo htmlspecialchars($row["name"]);
                                echo "</p>
                                <p class='center'><a class='red_button no-button'>No</a><a class='green_button' href='delete-item.php?id=$id&pageno=$pageNumber'>Yes</a></p>
                            </div>
                        </div>
                    </div>
                </p>";
            }elseif($type == "buyer"){
                if(!$purchased){
                    echo "
                    <p class='center'>
                        <a class='view-button' href='view-item.php?id=$id&pageno=$pageNumber'>View Item</a>
                        <a class='link-button' href='$link' target='_blank'>View Item on Website</a>
                    </p>
                    <div class='center'>
                        <input class='purchased-button popup-button' type='checkbox' id='$id'><label for='$id'> Mark as Purchased</label>
                        <div class='popup-container purchased-popup-$id hidden'>
                            <div class='popup'>
                                <div class='close-container'>
                                    <img src='images/site-images/close.png' class='close-button'>
                                </div>
                                <div class='popup-content'>
                                    <label>Are you sure you want to mark this item as purchased?</label>
                                    <p>";
                                    echo htmlspecialchars($row["name"]);
                                    echo "</p>
                                    <p class='center'><a class='red_button no-button'>No</a><a class='green_button purchase-button' id='purchase-$id'>Yes</a></p>
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
                echo "Date Added";
            }else{
                echo "Last Modified";
            }
            echo ": $date_added</em></p>
        </div>
    </div>";
}
?>