<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        
        $data = [
            'user' => $user,
            'account_created' => $this->request->get('account_created', false)
        ];

        return $this->view('home/index', $data);
    }
}
