<?php

namespace App\Controllers;

use App\Core\Controller;

class TestController extends Controller
{
    public function index(): \App\Core\Response
    {
        return $this->view('test', ['message' => 'Hello from OOP MVC!']);
    }
}
