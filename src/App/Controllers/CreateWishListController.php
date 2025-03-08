<?php

namespace App\Controllers;

use Core\Controller;
use Helpers\FormValidation;
use Helpers\FormField;
use App\Models\WishList;
use App\Models\User;
use App\Models\Item;

class CreateWishListController extends Controller
{
    private formValidation $formValidation;
    private FormField $wishListType;
    private FormField $themeBackgroundID;
    private FormField $themeGiftWrapID;
    private FormField $wishListName;

    public function __construct(User|null $user)
    {
        parent::__construct($user);
        $this->formValidation = new FormValidation();
        $this->wishListType = new FormField(
            formValidation: $this->formValidation,
            name: "wishlist_type",
            type: "select",
            options: [
                ['value' => 'Birthday', 'display' => 'Birthday'],
                ['value' => 'Christmas', 'display' => 'Christmas']
            ],
            required: true,
            label: "Type"
        );
        $this->themeBackgroundID = new FormField(
            formValidation: $this->formValidation,
            name: "theme_background_id",
            type: "hidden",
            required: false,
        );
        $this->themeGiftWrapID = new FormField(
            formValidation: $this->formValidation,
            name: "theme_gift_wrap_id",
            type: "hidden",
            required: false,
        );
        $this->wishListName = new FormField(
            formValidation: $this->formValidation,
            name: 'wishlist_name',
            type: 'text',
            required: true,
            label: 'Name',
            autoCapitalize: 'words'
        );
    }

    public function showForm(): void
    {
        $this->view(
            view: 'create-wishlist',
            data: [
                'title' => 'Wish List | Create a Wish List',
                'formValidation' => $this->formValidation,
                'item' => new Item($this->homeDirectory),
            ]
        );
    }

    public function handleForm(): void
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            $this->formValidation->validateFormFields();

            $wishList = new WishList();

            if($wishList->createWishList(
                $this->wishListType,
                $this->wishListName,
                $this->themeBackgroundID,
                $this->themeGiftWrapID,
                $this->user->username
            )){
                $wishlistID = $wishList->getLastInsertID();
                header("Location: /wishlist1/view-wishlist?id=$wishlistID"); 
            }
        }
    }
}

?>