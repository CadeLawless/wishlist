<?php

ini_set("display_errors", 1);

// Include the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use Core\Router;
use App\Models\User;
use Middleware\AuthMiddleware;
session_start();

$requestUrl = $_SERVER['REQUEST_URI'];

// Strip the base directory
$requestUrl = str_replace('/wishlist', '', $requestUrl);

$router = new Router();
$router->add(
    serverMethod: 'GET',
    path: ['/', '/index'],
    handler: [App\Controllers\HomeController::class, 'index']
);
$router->add(
    serverMethod: 'GET',
    path: '/login',
    handler: [App\Controllers\LoginController::class, 'showForm']
);
$router->add(
    serverMethod: 'POST',
    path: '/login',
    handler: [App\Controllers\LoginController::class, 'handleForm']
);
$router->add(
    serverMethod: 'GET',
    path: '/logout',
    handler: [App\Controllers\LogoutController::class, 'logout']
);
$router->add(
    serverMethod: 'POST',
    path: '/change-theme',
    handler: [App\Controllers\AjaxController::class, 'changeTheme']
);
$router->add(
    serverMethod: 'GET',
    path: '/create-wishlist',
    handler: [App\Controllers\CreateWishListController::class, 'showForm']
);
$router->add(
    serverMethod: 'POST',
    path: '/create-wishlist',
    handler: [App\Controllers\CreateWishListController::class, 'handleForm']
);
$router->add(
    serverMethod: 'POST',
    path: '/show-theme-backgrounds',
    handler: [App\Controllers\AjaxController::class, 'fetchThemeBackgrounds']
);
$router->add(
    serverMethod: 'POST',
    path: '/show-theme-background-options',
    handler: [App\Controllers\AjaxController::class, 'fetchThemeBackgroundDropdownOptions']
);
$router->add(
    serverMethod: 'POST',
    path: '/show-theme-gift-wrap-options',
    handler: [App\Controllers\AjaxController::class, 'fetchThemeGiftWrapDropdownOptions']
);
$router->add(
    serverMethod: 'GET',
    path: '/view-wishlist',
    handler: [App\Controllers\ViewWishListController::class, 'viewWishList']
);
$router->add(
    serverMethod: 'POST',
    path: '/view-wishlist',
    handler: [App\Controllers\ViewWishListController::class, 'handleForms']
);
$router->add(
    serverMethod: 'POST',
    path: '/item-page-change',
    handler: [App\Controllers\AjaxController::class, 'fetchPaginatedResults']
);
$router->add(
    serverMethod: 'GET',
    path: '/view-wishlists',
    handler: [App\Controllers\ViewWishListsController::class, 'viewWishLists']
);

if(in_array($requestUrl, ["/login", "/create-an-account"])){
    $router->dispatch($requestUrl);
}else{
    $user = new User();

    // Run middleware before routing
    $authMiddleware = new AuthMiddleware();
    $authMiddleware->handle(user: $user);
    $router->dispatch($requestUrl, $user);
}

?>