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
$requestUrl = str_replace('/wishlist1', '', $requestUrl);

$router = new Router();
$router->add('GET', ['/', '/index'], [App\Controllers\HomeController::class, 'index']);
$router->add('GET', ['/login'], [App\Controllers\LoginController::class, 'showForm']);
$router->add('POST', ['/login'], [App\Controllers\LoginController::class, 'handleForm']);
$router->add('GET', '/logout', [App\Controllers\LogoutController::class, 'logout']);
$router->add('POST', '/change-theme', [App\Controllers\AjaxController::class, 'changeTheme']);
$router->add('GET', '/create-wishlist', [App\Controllers\CreateWishListController::class, 'showForm']);
$router->add('POST', '/show-theme-backgrounds', [App\Controllers\AjaxController::class, 'fetchThemeBackgrounds']);
$router->add('POST', '/show-theme-background-options', [App\Controllers\AjaxController::class, 'fetchThemeBackgroundDropdownOptions']);
$router->add('POST', '/show-theme-gift-wrap-options', [App\Controllers\AjaxController::class, 'fetchThemeGiftWrapDropdownOptions']);

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