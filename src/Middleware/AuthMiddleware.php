<?php

namespace Middleware;

use App\Models\User;

class AuthMiddleware
{
    public function handle(User &$user): void
    {
        $logged_in = $user->checkIfLoggedIn();

        if (!$logged_in) {
            // Redirect to login page if not authenticated
            header('Location: /login');
            exit;
        }else{
            $user->setInformation();
        }
    }
}

?>