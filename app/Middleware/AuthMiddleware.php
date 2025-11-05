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
            return Response::redirect('/login')
                ->withError('Please log in to access this page.');
        }

        return null; // Continue to next middleware/route
    }
}
