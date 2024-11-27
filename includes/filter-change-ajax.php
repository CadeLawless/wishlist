<?php
$wrap_image = $_SESSION["buyer_wrap_image"] ?? "";
$ajax = true;
if(isset($_POST["sort_price"], $_POST["sort_priority"])){
    require("../filter-options.php");
    $errors = false;
    $sort_priority = $_POST["sort_priority"];
    $sort_price = $_POST["sort_price"];
    if(!in_array($sort_priority, $valid_options)) $errors = true;
    if(!in_array($sort_price, $valid_options)) $errors = true;
    if(!$errors){
        require("../sort.php");
        $pageno = 1;
        if($type == "wisher"){
            $_SESSION["wisher_sort_priority"] = $sort_priority;
            $_SESSION["wisher_sort_price"] = $sort_price;
            paginate(type: "wisher", db: $db, query: "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? AND wishlists.username = ? ORDER BY $priority_order$price_order date_added DESC", values: [$wishlistID, $username], itemsPerPage: 12, pageNumber: $pageno, wishlist_id: $wishlistID, username: $username);
            $_SESSION["home"] = "view-wishlist.php?id=$wishlistID&pageno=$pageno#paginate-top";
        }else{
            $_SESSION["buyer_sort_priority"] = $sort_priority;
            $_SESSION["buyer_sort_price"] = $sort_price;
            paginate(type: "buyer", db: $db, query: "SELECT *, items.id as id FROM items LEFT JOIN wishlists ON items.wishlist_id = wishlists.id WHERE items.wishlist_id = ? ORDER BY purchased ASC, $priority_order$price_order date_added DESC", values: [$wishlistID], itemsPerPage: 12, pageNumber: $pageno, wishlist_id: $wishlistID, wishlist_key: $wishlistKey);
            $_SESSION["home"] = "buyer-view.php?key=$wishlistKey&pageno=$pageno#paginate-top";
        }
    }else{
        echo "<strong>Invalid filter. Please try again.</strong>";
    }
}
?>