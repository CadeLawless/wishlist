<?php

namespace App\Controllers;

use Core\Controller;

use Helpers\PopupManager;

use App\Models\WishList;

class ViewWishListController extends Controller
{
    public function viewWishList(): void
    {
        // get wishlist id from SESSION/URL
        $wishlistID = $_GET["id"] ?? false;
        if(!$wishlistID) header("Location: index");
        $_SESSION["wisher_wishlist_id"] = $wishlistID;
        $_SESSION["type"] = "wisher";

        // Get any popups from SESSION
        $popupNames = [
            "copyFromPopup",
            "wishlistHiddenPopup",
            "wishlistPublicPopup",
            "wishlistCompletePopup",
            "wishlistReactivatedPopup",
            "itemDeletedPopup"
        ];
        $popupManager = new PopupManager($popupNames);

        $wishList = new WishList();
        $wishListInfo = $wishList->getWishListInfo($this->user, $wishlistID);
        if($wishListInfo === false) header("Location: index");

        $this->view('view-wishlist', ['title' => 'View Wish List', 'wishlistInfo' => $wishListInfo]);
    }
}

?>