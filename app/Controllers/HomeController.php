<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\WishlistService;

class HomeController extends Controller
{
    private AuthService $authService;
    private WishlistService $wishlistService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->wishlistService = new WishlistService();
    }

    public function index(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlists = $this->wishlistService->getUserWishlists($user['username']);
        
        $data = [
            'user' => $user,
            'wishlists' => $wishlists,
            'account_created' => $this->request->get('account_created', false)
        ];

        return $this->view('home/index', $data);
    }
}
