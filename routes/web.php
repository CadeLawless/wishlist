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
Router::get('/logout', [AuthController::class, 'logout']);

// Home route
Router::get('/', [HomeController::class, 'index']);

// Wishlist routes
Router::get('/wishlist', [WishlistController::class, 'index']);
Router::get('/wishlist/create', [WishlistController::class, 'create']);
Router::get('/wishlists', [WishlistController::class, 'wishlists']);
Router::post('/wishlist', [WishlistController::class, 'store']);
Router::get('/{id}', [WishlistController::class, 'show']);
Router::get('/{id}/edit', [WishlistController::class, 'edit']);
Router::post('/{id}', [WishlistController::class, 'update']);
Router::delete('/{id}', [WishlistController::class, 'delete']);
Router::post('/{id}/toggle-visibility', [WishlistController::class, 'toggleVisibility']);
Router::post('/{id}/toggle-complete', [WishlistController::class, 'toggleComplete']);

// Item routes
Router::get('/{wishlistId}/item/create', [ItemController::class, 'create']);
Router::post('/{wishlistId}/item', [ItemController::class, 'store']);
Router::get('/{wishlistId}/item/{id}/edit', [ItemController::class, 'edit']);
Router::post('/{wishlistId}/item/{id}', [ItemController::class, 'update']);
Router::delete('/{wishlistId}/item/{id}', [ItemController::class, 'delete']);
Router::post('/{wishlistId}/item/{id}/toggle-purchased', [ItemController::class, 'togglePurchased']);

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

// Dark theme toggle
Router::post('/toggle-dark-mode', [AuthController::class, 'toggleDarkMode']);