<?php

use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\WishlistController;
use App\Controllers\ItemController;
use App\Controllers\BuyerController;
use App\Controllers\TestController;
use App\Core\Router;

// Test route
Router::get('/test', [TestController::class, 'index']);

// Guest routes (login, register, etc.)
Router::get('/login', [AuthController::class, 'login']);
Router::post('/login', [AuthController::class, 'login']);
Router::get('/register', [AuthController::class, 'register']);
Router::post('/register', [AuthController::class, 'register']);
Router::get('/forgot-password', [AuthController::class, 'forgotPassword']);
Router::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Router::get('/reset-password', [AuthController::class, 'resetPassword']);
Router::post('/reset-password', [AuthController::class, 'resetPassword']);
Router::get('/verify-email', [AuthController::class, 'verifyEmail']);

// Logout
Router::post('/logout', [AuthController::class, 'logout']);

// Home route
Router::get('/', [HomeController::class, 'index']);

// Wishlist routes
Router::get('/wishlist', [WishlistController::class, 'index']);
Router::get('/wishlist/create', [WishlistController::class, 'create']);
Router::get('/wishlists', [WishlistController::class, 'wishlists']);
Router::post('/wishlist', [WishlistController::class, 'store']);
Router::get('/wishlist/{id}', [WishlistController::class, 'show']);
Router::get('/wishlist/{id}/edit', [WishlistController::class, 'edit']);
Router::post('/wishlist/{id}', [WishlistController::class, 'update']);
Router::delete('/wishlist/{id}', [WishlistController::class, 'delete']);
Router::post('/wishlist/{id}/toggle-visibility', [WishlistController::class, 'toggleVisibility']);
Router::post('/wishlist/{id}/toggle-complete', [WishlistController::class, 'toggleComplete']);

// Item routes
Router::get('/wishlist/{wishlistId}/item/create', [ItemController::class, 'create']);
Router::post('/wishlist/{wishlistId}/item', [ItemController::class, 'store']);
Router::get('/wishlist/{wishlistId}/item/{id}/edit', [ItemController::class, 'edit']);
Router::post('/wishlist/{wishlistId}/item/{id}', [ItemController::class, 'update']);
Router::delete('/wishlist/{wishlistId}/item/{id}', [ItemController::class, 'delete']);
Router::post('/wishlist/{wishlistId}/item/{id}/toggle-purchased', [ItemController::class, 'togglePurchased']);

// Buyer routes
Router::get('/buyer/{key}', [BuyerController::class, 'show']);
Router::post('/buyer/{key}/purchase/{itemId}', [BuyerController::class, 'purchaseItem']);

// Profile routes
Router::get('/profile', [AuthController::class, 'profile']);
Router::post('/profile', [AuthController::class, 'updateProfile']);

// Admin routes
Router::get('/admin', [AuthController::class, 'admin']);
Router::get('/admin/users', [AuthController::class, 'adminUsers']);
Router::get('/admin/wishlists', [AuthController::class, 'adminWishlists']);