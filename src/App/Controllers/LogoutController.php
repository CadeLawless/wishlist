<?php

namespace App\Controllers;

use Core\Controller;

class LogoutController extends Controller
{
    public function logout(): void
    {
        unset($_COOKIE['wishlist_session_id']);
        setcookie('wishlist_session_id', "", 1); // empty value and old timestamp
        foreach($_SESSION as $key => $val){
            unset($_SESSION[$key]);
        }
        session_unset();
        session_destroy();
        setcookie("PHPSESSID", "", 1);
        //var_dump($_SESSION);
        header("Location: /wishlist1/login");
    }
}

?>