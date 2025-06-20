<?php

namespace App\Controllers;

use Core\Controller;

class ViewWishListController extends Controller
{
    public function viewWishList(): void
    {
        $this->view('view-wishlist', ['title' => 'View Wish List']);
    }
}

?>