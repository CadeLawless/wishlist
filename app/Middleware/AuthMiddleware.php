<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AuthMiddleware
{
    public function __construct(
        private AuthService $authService = new AuthService()
    ) {}

    public function __invoke(Request $request): ?Response
    {
        if (!$this->authService->isLoggedIn()) {
            $redirect = Response::redirect('/login');
            
            // Don't show error message if user is trying to access homepage
            // User knows they need to login to access homepage
            if ($request->path() !== '/') {
                $redirect->withError('Please log in to access this page.');
            }
            
            return $redirect;
        }

        return null; // Continue to next middleware/route
    }
}
