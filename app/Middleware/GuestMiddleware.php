<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class GuestMiddleware
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function __invoke(Request $request): ?Response
    {
        if ($this->authService->isLoggedIn()) {
            return Response::redirect('/')
                ->withInfo('You are already logged in.');
        }

        return null; // Continue to next middleware/route
    }
}
