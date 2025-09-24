<?php

namespace App\Controllers;

use Core\Controller;

use Helpers\PopupManager;

use App\Models\WishList;

use Helpers\FormValidation;
use Helpers\FormField;
use App\Models\User;

class ViewWishListController extends Controller
{
    private formValidation $formValidation;
    private FormField $other_wishlist_copy_from;
    private FormField $copy_from_select_all;
    private FormField $other_wishlist_copy_to;
    private FormField $copy_to_select_all;
    private string $wishListID;

    public function __construct(User|null $user)
    {
        parent::__construct($user);

        $this->wishListID = $_GET["wishlistID"] ?? "";
        $username = $user->username ?? "";

        $wishList = new WishList();
        $otherWishLists = $wishList->fetchOtherWishLists($username, $this->wishListID);

        $this->formValidation = new FormValidation();
        $this->other_wishlist_copy_from = new FormField(
            formValidation: $this->formValidation,
            name: "other_wishlist_copy_from",
            type: "select",
            options: $otherWishLists,
            required: true,
            label: "Other Wish List"
        );
        $this->copy_from_select_all = new FormField(
            formValidation: $this->formValidation,
            name: "copy_from_select_all",
            type: "checkbox",
            required: false,
        );
        $this->other_wishlist_copy_to = new FormField(
            formValidation: $this->formValidation,
            name: "other_wishlist_copy_to",
            type: "select",
            options: $otherWishLists,
            required: true,
            label: "Other Wish List"
        );
        $this->copy_to_select_all = new FormField(
            formValidation: $this->formValidation,
            name: "copy_to_select_all",
            type: "checkbox",
            required: false,
        );
    }
    public function viewWishList(): void
    {
        // get wishlist id from SESSION/URL
        if($this->wishListID === "") header("Location: index");
        $_SESSION["wisher_wishlist_id"] = $this->wishListID;
        $_SESSION["type"] = "wisher";

        $pageno = $_GET["pageno"] ?? 1;

        $_SESSION["home"] = "view-wishlist.php?id=$this->wishListID&pageno=$pageno#paginate-top";

        $sort_priority = $_SESSION["wisher_sort_priority"] ?? "";
        $sort_price = $_SESSION["wisher_sort_price"] ?? "";
        $_SESSION["wisher_sort_priority"] = $sort_priority;
        $_SESSION["wisher_sort_price"] = $sort_price;


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
        $wishListInfo = $wishList->getWishListInfo($this->user, $this->wishListID);
        if($wishListInfo === false) header("Location: index");

        $this->view('view-wishlist', ['title' => 'View Wish List', 'wishlistInfo' => $wishListInfo]);
    }
}

?>