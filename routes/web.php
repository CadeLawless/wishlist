<?php

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\WishlistController;
use App\Controllers\ItemController;
use App\Controllers\BuyerController;
use App\Controllers\TestController;
use App\Core\Router;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\AdminMiddleware;

// Register middleware
Router::middleware('auth', new AuthMiddleware());
Router::middleware('guest', new GuestMiddleware());
Router::middleware('admin', new AdminMiddleware());

// Test route
Router::get('/test', [TestController::class, 'index']);

// Dark theme toggle (must be first to avoid conflicts)
Router::post('/toggle-dark-mode', [AuthController::class, 'toggleDarkMode'])->middleware('auth');

// Guest routes (login, register, etc.)
Router::get('/login', [AuthController::class, 'login'])->middleware('guest');
Router::post('/login', [AuthController::class, 'login'])->middleware('guest');
Router::get('/register', [AuthController::class, 'register'])->middleware('guest');
Router::post('/register', [AuthController::class, 'register'])->middleware('guest');
Router::get('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('guest');
Router::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('guest');
Router::get('/reset-password', [AuthController::class, 'resetPassword'])->middleware('guest');
Router::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('guest');
Router::get('/verify-email', [AuthController::class, 'verifyEmail'])->middleware('guest');

// Logout
Router::get('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Home route
Router::get('/', [HomeController::class, 'index']);

// Profile routes (must come before generic /{id} routes)
Router::get('/profile', [AuthController::class, 'profile'])->middleware('auth');
Router::post('/profile', [AuthController::class, 'updateProfile'])->middleware('auth');

// Admin routes (must come before generic /{id} routes)
Router::get('/admin', [AuthController::class, 'admin']);
Router::get('/admin/users', [AuthController::class, 'adminUsers']);
Router::get('/admin/wishlists', [AuthController::class, 'adminWishlists']);


// Wishlist routes (home page shows user's wishlists)
Router::get('/create', [WishlistController::class, 'create'])->middleware('auth');
Router::get('/wishlists', [WishlistController::class, 'wishlists'])->middleware('auth');
Router::post('/', [WishlistController::class, 'store'])->middleware('auth');
Router::get('/{id}', [WishlistController::class, 'show'])->middleware('auth');
Router::get('/{id}/edit', [WishlistController::class, 'edit'])->middleware('auth');
Router::post('/{id}', [WishlistController::class, 'update'])->middleware('auth');
Router::delete('/{id}', [WishlistController::class, 'delete'])->middleware('auth');
Router::post('/{id}/toggle-visibility', [WishlistController::class, 'toggleVisibility'])->middleware('auth');
Router::post('/{id}/toggle-complete', [WishlistController::class, 'toggleComplete'])->middleware('auth');

// Wishlist management routes
Router::post('/{id}/rename', [WishlistController::class, 'rename'])->middleware('auth');
Router::post('/{id}/theme', [WishlistController::class, 'updateTheme'])->middleware('auth');
Router::post('/{id}/copy-from', [WishlistController::class, 'copyFrom'])->middleware('auth');
Router::post('/{id}/copy-to', [WishlistController::class, 'copyTo'])->middleware('auth');
Router::post('/{id}/hide', [WishlistController::class, 'hide'])->middleware('auth');
Router::post('/{id}/show', [WishlistController::class, 'showPublic'])->middleware('auth');
Router::post('/{id}/complete', [WishlistController::class, 'complete'])->middleware('auth');
Router::post('/{id}/reactivate', [WishlistController::class, 'reactivate'])->middleware('auth');

// AJAX endpoints
Router::post('/{id}/paginate', [WishlistController::class, 'paginateItems'])->middleware('auth');
Router::post('/{id}/filter', [WishlistController::class, 'filterItems'])->middleware('auth');
Router::post('/{id}/items', [WishlistController::class, 'getOtherWishlistItems'])->middleware('auth');

// Item routes
Router::get('/{wishlistId}/item/create', [ItemController::class, 'create'])->middleware('auth');
Router::post('/{wishlistId}/item', [ItemController::class, 'store'])->middleware('auth');
Router::get('/{wishlistId}/item/{id}/edit', [ItemController::class, 'edit'])->middleware('auth');
Router::post('/{wishlistId}/item/{id}', [ItemController::class, 'update'])->middleware('auth');
Router::delete('/{wishlistId}/item/{id}', [ItemController::class, 'delete'])->middleware('auth');
Router::post('/{wishlistId}/item/{id}/toggle-purchased', [ItemController::class, 'togglePurchased'])->middleware('auth');

// Buyer routes
Router::get('/buyer/{key}', [BuyerController::class, 'show']);
Router::post('/buyer/{key}/paginate', [BuyerController::class, 'paginateItems']);
Router::post('/buyer/{key}/purchase/{itemId}', [BuyerController::class, 'purchaseItem']);
