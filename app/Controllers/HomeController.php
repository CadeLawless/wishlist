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
        
        $user = $this->auth();
        
        $data = [
            'title' => 'Any Wish List',
            'user' => $user,
            'account_created' => $this->request->get('account_created', false),
            'customStyles' => '
                #container { margin: 0; }'
        ];

        return $this->view('home/index', $data);
    }
}
