<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AdminMiddleware
{
    public function __construct(
        private AuthService $authService = new AuthService()
    ) {}

    public function __invoke(Request $request): ?Response
    {
        if (!$this->authService->isLoggedIn()) {
            return Response::redirect('/wishlist/login')
                ->withError('Please log in to access this page.');
        }

        if (!$this->authService->isAdmin()) {
            return Response::redirect('/wishlist/')
                ->withError('Access denied. Admin privileges required.');
        }

        return null; // Continue to next middleware/route
    }
}
