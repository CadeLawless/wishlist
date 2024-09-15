<?php
$priorities = [
    1 => "{$_SESSION["name"]} absolutely needs this item",
    2 => "{$_SESSION["name"]} really wants this item",
    3 => "It would be cool if {$_SESSION["name"]} had this item",
    4 => "Eh, {$_SESSION["name"]} could do without this item"
];

$gift_wrap = 1;

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
        echo "<div class='item-container' style='";
        if($purchased) echo "padding-top: 0";
        echo "'>";
        echo "<img src='images/site-images/themes/gift-wraps/$wrap_image/$gift_wrap.png' class='gift-wrap' alt='gift wrap'>";
        $gift_wrap++;
            
        }
        echo "
        <div class='item-image-container template popup-button'>
            <img class='item-image' src='images/site-images/themes/gift-wraps/$wrap_image/$gift_wrap.png' alt='wishlist item image'>
        </div>

        <div class='item-description'>
            <h3>$item_name_short</h3>
            <h4>Price: $$price <span class='price-date'>(as of $price_date)</span></h4>
            <h4 class='notes-label'>Notes: </h4><span>$notes_short</span><br>
            <h4 class='notes-label'>Priority: </h4><span>($priority) $priorities[$priority]</span><br>
            <div class='center' style='margin: 18px 0;'>
                <a class='template view-button popup-button' href='#'>View Item</a>
                <a class='template link-button' href='$link' target='_blank'>Website Link</a>
            </div>
            <div class='center'>
                <h4 class='center'>This item has been purchased!</h4>
                <span class='unmark-msg'>If you need to unmark an item as purchased, reach out to {$_SESSION["name"]} or a family member.</span>
            </div>";
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