<?php
$wishlist_base_id = $copy_from ? $other_wishlist_id : $_SESSION["wisher_wishlist_id"];
if((!$copy_from && $wishlist_base_id != "") || ($copy_from && $wishlist_base_id != "")){
    $image_folder = match($ajax){
        true => "../../images",
        default => "images",
    };
    if($copy_from){
        $findWishListItems = $db->select("SELECT id, copy_id, name, image FROM items WHERE wishlist_id = ?", [$wishlist_base_id]);
    }else{
        $findWishListItems = $db->select("SELECT * FROM items WHERE wishlist_id = ?", [$wishlist_base_id]);
    }
    if($findWishListItems->num_rows > 0){
        echo "
        <div class='checkboxes-container'>
            <div class='select-item-container select-all'>
                <div class='option-title'>All Items</div>
                <div class='option-checkbox'>
                    <input type='checkbox' name='copy_";
                    echo $copy_from ? "from" : "to";
                    echo "_select_all' ";
                    if(($copy_from && $copy_from_select_all == "Yes") || (!$copy_from && $copy_to_select_all == "Yes")) echo "checked";
                    echo " class='check-all' />
                </div>
            </div>";
            $copy_counter = 0;
            while($row = $findWishListItems->fetch_assoc()){
                $item_id = $row["id"];
                $item_copy_id = $row["copy_id"];
                if($item_copy_id != ""){
                    if($copy_from){
                        $findCopyInCurrentList = $db->select("SELECT copy_id FROM items WHERE copy_id = ? AND wishlist_id = ?", [$item_copy_id, $wishlist_base_id]);
                        $alreadyInList = $findCopyInCurrentList->num_rows > 0;
                    }else{
                        $findCopyInOtherList = $db->select("SELECT copy_id FROM items WHERE copy_id = ? AND wishlist_id = ?", [$item_copy_id, $other_wishlist_id]);
                        $alreadyInList = $findCopyInOtherList->num_rows > 0;
                    }
                }else{
                    $alreadyInList = false;
                }
                $item_name = htmlspecialchars($row["name"]);
                $item_image = $row["image"];
                $image_path = "$image_folder/item-images/$wishlist_base_id/$item_image";
                if(!file_exists($image_path)){
                    $image_path = "images/site-images/default-photo.png";
                }else{
                    $image_path = "images/item-images/$wishlist_base_id/" . htmlspecialchars($item_image);
                }

                echo "
                <div class='select-item-container";
                    if($alreadyInList){
                        $copy_counter++;
                        echo " already-in-list";
                    }
                echo "'>
                    <div class='option-image'>
                        <img src='$image_path?t=" . time() . "' alt='wishlist item image'>
                    </div>
                    <div class='option-title'>$item_name";
                    if($alreadyInList) echo " (Already in list)";
                    echo "</div>
                    <div class='option-checkbox'>
                        <input type='checkbox' class='";
                        if($alreadyInList) echo "already-in-list";
                        echo "' ";
                    if(($copy_from && (isset(${"copy_from_item_$item_id"}) || $copy_from_select_all == "Yes")) || (!$copy_from && (isset(${"copy_to_item_$item_id"}) || $copy_to_select_all == "Yes"))) echo "checked";
                    if($alreadyInList) echo " disabled";
                    echo " name='item_$item_id' />
                    </div>
                </div>";
            }
        echo "
        </div>
        <p class='center";
        if($copy_counter == $findWishListItems->num_rows) echo " hidden";
        echo "'><input type='submit' class='button text' name='copy_";
        echo $copy_from ? "from" : "to";
        echo "_submit' value='Copy Items' /></p>";
    }else{
        echo "<p>No items found</p>";
    }
}else{
    echo "<p>No items found</p>";
}
?>