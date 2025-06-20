<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\WishList;
use App\Models\User;

class ViewWishListsController extends Controller
{
    public function __construct(User|null $user)
    {
        parent::__construct($user);
    }

    public function viewWishLists(): void
    {
        $this->view('view-wishlists', ['title' => 'View Wish Lists', 'wishList' => new WishList()]);
    }
}

?>