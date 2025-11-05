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
Router::get('/verify-email', [AuthController::class, 'verifyEmail']);

// AJAX validation endpoints
Router::post('/api/check-username', [AuthController::class, 'checkUsername']);
Router::post('/api/check-email', [AuthController::class, 'checkEmail']);
Router::post('/api/resend-verification', [AuthController::class, 'resendVerification']);

// Logout
Router::get('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Home route
Router::get('/', [HomeController::class, 'index'])->middleware('auth');

// Profile routes (must come before generic /{id} routes)
Router::get('/profile', [AuthController::class, 'profile'])->middleware('auth');
Router::post('/profile', [AuthController::class, 'updateProfile'])->middleware('auth');

// Admin routes (must come before generic /{id} routes)
Router::get('/admin', [AuthController::class, 'admin'])->middleware('admin');
Router::get('/admin/users', [AuthController::class, 'admin'])->middleware('admin');
Router::get('/admin/wishlists', [AuthController::class, 'adminWishlists'])->middleware('admin');


// Wishlist routes (home page shows user's wishlists)
Router::get('/wishlists/create', [WishlistController::class, 'create'])->middleware('auth');
Router::get('/wishlists', [WishlistController::class, 'index'])->middleware('auth');
Router::post('/wishlists/paginate', [WishlistController::class, 'paginateWishlists'])->middleware('auth');
Router::post('/wishlists', [WishlistController::class, 'store'])->middleware('auth');
Router::get('/wishlists/{id}', [WishlistController::class, 'show'])->middleware('auth');
Router::get('/wishlists/{id}/edit', [WishlistController::class, 'edit'])->middleware('auth');
Router::post('/wishlists/{id}', [WishlistController::class, 'update'])->middleware('auth');
Router::delete('/wishlists/{id}', [WishlistController::class, 'delete'])->middleware('auth');
Router::post('/wishlists/{id}/toggle-visibility', [WishlistController::class, 'toggleVisibility'])->middleware('auth');
Router::post('/wishlists/{id}/toggle-complete', [WishlistController::class, 'toggleComplete'])->middleware('auth');

// Wishlist management routes
Router::post('/wishlists/{id}/rename', [WishlistController::class, 'rename'])->middleware('auth');
Router::post('/wishlists/{id}/theme', [WishlistController::class, 'updateTheme'])->middleware('auth');
Router::post('/wishlists/{id}/copy-from', [WishlistController::class, 'copyFrom'])->middleware('auth');
Router::post('/wishlists/{id}/copy-to', [WishlistController::class, 'copyTo'])->middleware('auth');
Router::post('/wishlists/{id}/hide', [WishlistController::class, 'hide'])->middleware('auth');
Router::post('/wishlists/{id}/show', [WishlistController::class, 'showPublic'])->middleware('auth');
Router::post('/wishlists/{id}/complete', [WishlistController::class, 'complete'])->middleware('auth');
Router::post('/wishlists/{id}/reactivate', [WishlistController::class, 'reactivate'])->middleware('auth');

// AJAX endpoints
Router::post('/wishlists/{id}/paginate', [WishlistController::class, 'paginateItems'])->middleware('auth');
Router::post('/wishlists/{id}/filter', [WishlistController::class, 'filterItems'])->middleware('auth');
Router::post('/wishlists/{id}/items', [WishlistController::class, 'getOtherWishlistItems'])->middleware('auth');

// Item routes - Two-step flow
Router::get('/wishlists/{wishlistId}/item/add', [ItemController::class, 'addStep1'])->middleware('auth');
Router::get('/wishlists/{wishlistId}/item/create', [ItemController::class, 'create'])->middleware('auth');
Router::post('/wishlists/{wishlistId}/item', [ItemController::class, 'store'])->middleware('auth');

// API routes for URL metadata fetching
Router::post('/wishlists/{wishlistId}/api/fetch-url-metadata', [ItemController::class, 'fetchUrlMetadata'])->middleware('auth');
Router::post('/wishlists/{wishlistId}/api/store-fetched-data', [ItemController::class, 'storeFetchedData'])->middleware('auth');
Router::get('/wishlists/{wishlistId}/api/test-json', [ItemController::class, 'testJson'])->middleware('auth');
Router::get('/wishlists/{wishlistId}/item/{id}/edit', [ItemController::class, 'edit'])->middleware('auth');
Router::post('/wishlists/{wishlistId}/item/{id}', [ItemController::class, 'update'])->middleware('auth');
Router::delete('/wishlists/{wishlistId}/item/{id}', [ItemController::class, 'delete'])->middleware('auth');
Router::post('/wishlists/{wishlistId}/item/{id}/toggle-purchased', [ItemController::class, 'togglePurchased'])->middleware('auth');

// Buyer routes
Router::get('/buyer/{key}', [BuyerController::class, 'show']);
Router::post('/buyer/{key}/filter', [BuyerController::class, 'filterItems']);
Router::post('/buyer/{key}/paginate', [BuyerController::class, 'paginateItems']);
Router::post('/buyer/{key}/purchase/{itemId}', [BuyerController::class, 'purchaseItem']);
