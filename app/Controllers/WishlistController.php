<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\WishlistService;
use App\Services\ValidationService;
use App\Services\FileUploadService;

class WishlistController extends Controller
{
    private AuthService $authService;
    private WishlistService $wishlistService;
    private ValidationService $validationService;
    private FileUploadService $fileUploadService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->wishlistService = new WishlistService();
        $this->validationService = new ValidationService();
        $this->fileUploadService = new FileUploadService();
    }

    public function index(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlists = $this->wishlistService->getUserWishlists($user['username']);
        
        $data = [
            'user' => $user,
            'wishlists' => $wishlists
        ];

        return $this->view('wishlist/index', $data);
    }

    public function wishlists(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlists = $this->wishlistService->getUserWishlists($user['username']);
        
        $data = [
            'user' => $user,
            'wishlists' => $wishlists
        ];

        return $this->view('wishlist/index', $data);
    }

    public function show(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        // Get pagination number
        $pageno = $this->request->get('pageno', 1);
        
        // Get other wishlists for copy functionality
        $otherWishlists = $this->wishlistService->getOtherWishlists($user['username'], $id);
        
        // Get sorting/filter preferences from session
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $_SESSION['wisher_wishlist_id'] = $id;
        $_SESSION['home'] = "/wishlist/view-wishlist.php?id=$id&pageno=$pageno#paginate-top";
        $_SESSION['type'] = 'wisher';

        $filters = [
            'sort_priority' => $_SESSION['wisher_sort_priority'] ?? '',
            'sort_price' => $_SESSION['wisher_sort_price'] ?? ''
        ];
        
        // Build SQL order clause based on filters
        $priorityOrder = $filters['sort_priority'] ? "priority ASC, " : "";
        $priceOrder = $filters['sort_price'] ? "price {$filters['sort_price']}, " : "";

        // Get items with sorting
        $items = $this->wishlistService->getWishlistItems($id, []);
        
        $data = [
            'user' => $user,
            'wishlist' => $wishlist,
            'items' => $items,
            'other_wishlists' => $otherWishlists,
            'pageno' => $pageno,
            'filters' => $filters,
            'wishlist_id' => $id
        ];

        return $this->view('wishlist/show', $data);
    }

    public function create(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        
        $data = [
            'user' => $user,
            'wishlist_type' => $this->request->input('wishlist_type', ''),
            'wishlist_name' => $this->request->input('wishlist_name', ''),
            'theme_background_id' => $this->request->input('theme_background_id', ''),
            'theme_gift_wrap_id' => $this->request->input('theme_gift_wrap_id', '')
        ];

        return $this->view('wishlist/create', $data);
    }

    public function store(): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $data = $this->request->input();
        $errors = $this->validationService->validateWishlist($data);

        if ($this->validationService->hasErrors($errors)) {
            return $this->view('wishlist/create', [
                'user' => $user,
                'wishlist_type' => $data['wishlist_type'] ?? '',
                'wishlist_name' => $data['wishlist_name'] ?? '',
                'theme_background_id' => $data['theme_background_id'] ?? '',
                'theme_gift_wrap_id' => $data['theme_gift_wrap_id'] ?? '',
                'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
            ]);
        }

        $wishlist = $this->wishlistService->createWishlist($user['username'], $data);
        
        if ($wishlist) {
            return $this->redirect("/wishlist/{$wishlist->id}")->withSuccess('Wishlist created successfully!');
        }

        return $this->view('wishlist/create', [
            'user' => $user,
            'wishlist_type' => $data['wishlist_type'] ?? '',
            'wishlist_name' => $data['wishlist_name'] ?? '',
            'theme_background_id' => $data['theme_background_id'] ?? '',
            'theme_gift_wrap_id' => $data['theme_gift_wrap_id'] ?? '',
            'error_msg' => '<div class="submit-error"><strong>Wishlist creation failed:</strong><ul><li>Unable to create wishlist. Please try again.</li></ul></div>'
        ]);
    }

    public function edit(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        $data = [
            'user' => $user,
            'wishlist' => $wishlist,
            'wishlist_name' => $this->request->input('wishlist_name', $wishlist->wishlist_name)
        ];

        return $this->view('wishlist/edit', $data);
    }

    public function update(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        $data = $this->request->input();
        $errors = $this->validationService->validateWishlist($data);

        if ($this->validationService->hasErrors($errors)) {
            return $this->view('wishlist/edit', [
                'user' => $user,
                'wishlist' => $wishlist,
                'wishlist_name' => $data['wishlist_name'] ?? '',
                'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
            ]);
        }

        if ($this->wishlistService->updateWishlistName($id, $data['wishlist_name'])) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist updated successfully!');
        }

        return $this->view('wishlist/edit', [
            'user' => $user,
            'wishlist' => $wishlist,
            'wishlist_name' => $data['wishlist_name'] ?? '',
            'error_msg' => '<div class="submit-error"><strong>Update failed:</strong><ul><li>Unable to update wishlist. Please try again.</li></ul></div>'
        ]);
    }

    public function delete(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->deleteWishlist($id)) {
            return $this->redirect('/wishlist')->withSuccess('Wishlist deleted successfully!');
        }

        return $this->redirect('/wishlist')->withError('Unable to delete wishlist. Please try again.');
    }

    public function toggleVisibility(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleVisibility($id)) {
            $message = $wishlist->isPublic() ? 'Wishlist is now hidden.' : 'Wishlist is now public.';
            return $this->redirect("/wishlist/{$id}")->withSuccess($message);
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to update wishlist visibility.');
    }

    public function toggleComplete(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleComplete($id)) {
            $message = $wishlist->isComplete() ? 'Wishlist has been reactivated.' : 'Wishlist has been marked as complete.';
            return $this->redirect("/wishlist/{$id}")->withSuccess($message);
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to update wishlist status.');
    }
}
