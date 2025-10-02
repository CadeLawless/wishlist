<?php

namespace App\Controllers;

use Core\Controller;

use Helpers\PopupManager;

use App\Models\WishList;
use App\Models\Item;

use Helpers\FormValidation;
use Helpers\FormField;
use App\Models\User;

class ViewWishListController extends Controller
{
    private formValidation $themeFormValidation;
    private formValidation $renameFormValidation;
    private formValidation $copyFromFormValidation;
    private formValidation $copyToFormValidation;
    private FormField $otherWishListCopyFrom;
    private FormField $copyFromSelectAll;
    private FormField $otherWishListCopyTo;
    private FormField $copyToSelectAll;
    private FormField $wishListNameInput;
    private string $wisherSortPriority;
    private string $wisherSortPrice;
    private array $sortOptions = ["", "1", "2"];
    private string $wishListID;
    public array $wishListItems;
    private array|bool $wishListInfo;

    public function __construct(User|null $user)
    {
        parent::__construct($user);

        $this->wisherSortPriority = $_SESSION["wisherSortPriority"] ?? "";
        $this->wisherSortPrice = $_SESSION["wisherSortPrice"] ?? "";

        $this->wishListID = $_GET["wishlistID"] ?? "";
        $username = $user->username ?? "";

        $item = new Item($this->homeDirectory);
        $this->wishListItems = $item->getItemsFromWishList($this->wishListID);

        $wishList = new WishList();
        $this->wishListInfo = $wishList->getWishListInfo(user: $user, wishlistID: $this->wishListID);

        $otherWishLists = $wishList->fetchOtherWishLists($username, $this->wishListID);

        $this->themeFormValidation = new FormValidation();
        $this->renameFormValidation = new FormValidation();
        $this->copyFromFormValidation = new FormValidation();
        $this->copyToFormValidation = new FormValidation();
        $this->otherWishListCopyFrom = new FormField(
            formValidation: $this->copyFromFormValidation,
            name: "otherWishListCopyFrom",
            type: "select",
            options: $otherWishLists,
            required: true,
            label: "Other Wish List"
        );
        $this->copyFromSelectAll = new FormField(
            formValidation: $this->copyFromFormValidation,
            name: "copyFromSelectAll",
            type: "checkbox",
            required: false,
        );
        $this->otherWishListCopyTo = new FormField(
            formValidation: $this->copyToFormValidation,
            name: "otherWishListCopyTo",
            type: "select",
            options: $otherWishLists,
            required: true,
            label: "Other Wish List"
        );
        $this->copyToSelectAll = new FormField(
            formValidation: $this->copyToFormValidation,
            name: "copyToSelectAll",
            type: "checkbox",
            required: false,
        );
        $this->wishListNameInput = new FormField(
            formValidation: $this->renameFormValidation,
            name: 'wishListNameInput',
            type: 'text',
            value: $this->wishListInfo === false ? "" : $this->wishListInfo["wishlist_name"],
            required: true,
            label: 'Name',
            autoCapitalize: 'words'
        );

    }

    public function viewWishList(): void
    {
        // get wishlist id from SESSION/URL
        if($this->wishListID === "" || $this->wishListInfo === false) header("Location: index");
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

        $this->view('view-wishlist', ['title' => 'View Wish List', 'wishListInfo' => $this->wishListInfo]);
    }

    public function handleForms(): void
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_POST["theme_submit_button"])){
                $this->handleThemeForm();
            }
            
            if(isset($_POST["rename_submit_button"])){
                $this->handleRenameForm();
            }

            if(isset($_POST["copy_from_submit"])){
                $this->handleCopyFromForm();
            }

            if(isset($_POST["copy_to_submit"])){
                $this->handleCopyToForm();
            }
        }
    }

    public function handleThemeForm(): void
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["theme_submit_button"])){

        }
    }

    public function handleRenameForm(): void
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["rename_submit_button"])){

        }
    }

    public function handleCopyFromForm(): void
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["copy_from_submit"])){

        }
    }

    public function handleCopyToForm(): void
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST["copy_to_submit"])){

        }
    }
}

?>