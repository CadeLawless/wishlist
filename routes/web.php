<?php

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\WishlistController;
use App\Controllers\ItemController;
use App\Controllers\BuyerController;
use App\Core\Router;

// Register middleware
Router::middleware('auth', function($request) {
    $middleware = new \App\Middleware\AuthMiddleware();
    return $middleware($request);
});

Router::middleware('guest', function($request) {
    $middleware = new \App\Middleware\GuestMiddleware();
    return $middleware($request);
});

Router::middleware('admin', function($request) {
    $middleware = new \App\Middleware\AdminMiddleware();
    return $middleware($request);
});

// Guest routes (login, register, etc.)
Router::get('/login', [AuthController::class, 'showLogin'])->middleware('guest');
Router::post('/login', [AuthController::class, 'login'])->middleware('guest');
Router::get('/register', [AuthController::class, 'showRegister'])->middleware('guest');
Router::post('/register', [AuthController::class, 'register'])->middleware('guest');
Router::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->middleware('guest');
Router::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('guest');
Router::get('/reset-password', [AuthController::class, 'showResetPassword'])->middleware('guest');
Router::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('guest');
Router::get('/verify-email', [AuthController::class, 'verifyEmail'])->middleware('guest');

// Logout (accessible to authenticated users)
Router::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Authenticated routes
Router::get('/', [HomeController::class, 'index'])->middleware('auth');

// Wishlist routes
Router::get('/wishlist', [WishlistController::class, 'index'])->middleware('auth');
Router::get('/wishlist/create', [WishlistController::class, 'create'])->middleware('auth');
Router::post('/wishlist', [WishlistController::class, 'store'])->middleware('auth');
Router::get('/wishlist/{id}', [WishlistController::class, 'show'])->middleware('auth');
Router::get('/wishlist/{id}/edit', [WishlistController::class, 'edit'])->middleware('auth');
Router::post('/wishlist/{id}', [WishlistController::class, 'update'])->middleware('auth');
Router::delete('/wishlist/{id}', [WishlistController::class, 'delete'])->middleware('auth');
Router::post('/wishlist/{id}/toggle-visibility', [WishlistController::class, 'toggleVisibility'])->middleware('auth');
Router::post('/wishlist/{id}/toggle-complete', [WishlistController::class, 'toggleComplete'])->middleware('auth');

// Item routes
Router::get('/wishlist/{wishlistId}/item/create', [ItemController::class, 'create'])->middleware('auth');
Router::post('/wishlist/{wishlistId}/item', [ItemController::class, 'store'])->middleware('auth');
Router::get('/wishlist/{wishlistId}/item/{itemId}/edit', [ItemController::class, 'edit'])->middleware('auth');
Router::post('/wishlist/{wishlistId}/item/{itemId}', [ItemController::class, 'update'])->middleware('auth');
Router::delete('/wishlist/{wishlistId}/item/{itemId}', [ItemController::class, 'delete'])->middleware('auth');
Router::post('/wishlist/{wishlistId}/item/{itemId}/purchase', [ItemController::class, 'purchase'])->middleware('auth');

// Public buyer routes (no authentication required)
Router::get('/buyer/{secretKey}', [BuyerController::class, 'show']);
Router::post('/buyer/{secretKey}/item/{itemId}/purchase', [BuyerController::class, 'purchaseItem']);

// Legacy route compatibility (redirect old URLs to new structure)
Router::get('/view-wishlists.php', function($request) {
    return \App\Core\Response::redirect('/wishlist');
});

Router::get('/view-wishlist.php', function($request) {
    $id = $request->get('id');
    if ($id) {
        return \App\Core\Response::redirect("/wishlist/{$id}");
    }
    return \App\Core\Response::redirect('/wishlist');
});

Router::get('/create-wishlist.php', function($request) {
    return \App\Core\Response::redirect('/wishlist/create');
});

Router::get('/add-item.php', function($request) {
    $wishlistId = $request->get('wishlist_id');
    if ($wishlistId) {
        return \App\Core\Response::redirect("/wishlist/{$wishlistId}/item/create");
    }
    return \App\Core\Response::redirect('/wishlist');
});

Router::get('/buyer-view.php', function($request) {
    $key = $request->get('key');
    if ($key) {
        return \App\Core\Response::redirect("/buyer/{$key}");
    }
    return \App\Core\Response::redirect('/');
});
