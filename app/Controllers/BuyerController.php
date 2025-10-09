<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\WishlistService;

class BuyerController extends Controller
{
    private WishlistService $wishlistService;

    public function __construct()
    {
        parent::__construct();
        $this->wishlistService = new WishlistService();
    }

    public function show(string $secretKey): Response
    {
        $wishlist = $this->wishlistService->getWishlistBySecretKey($secretKey);
        
        if (!$wishlist) {
            return $this->view('errors/404', [], 'main');
        }

        if (!$wishlist->isPublic() || $wishlist->isComplete()) {
            return $this->view('errors/access-denied', [], 'main');
        }

        $filters = [
            'sort_by' => $this->request->get('sort_by', 'date_added'),
            'sort_order' => $this->request->get('sort_order', 'DESC'),
            'priority' => $this->request->get('priority'),
            'purchased' => $this->request->get('purchased')
        ];

        $items = $this->wishlistService->getWishlistItems($wishlist->id, $filters);
        $stats = $this->wishlistService->getWishlistStats($wishlist->id);
        
        $data = [
            'wishlist' => $wishlist,
            'items' => $items,
            'stats' => $stats,
            'filters' => $filters,
            'pageno' => $this->request->get('pageno', 1)
        ];

        return $this->view('buyer/show', $data);
    }

    public function purchaseItem(string $secretKey, int $itemId): Response
    {
        $wishlist = $this->wishlistService->getWishlistBySecretKey($secretKey);
        
        if (!$wishlist) {
            return $this->redirect('/')->withError('Wishlist not found.');
        }

        if (!$wishlist->isPublic() || $wishlist->isComplete()) {
            return $this->redirect('/')->withError('This wishlist is not available for viewing.');
        }

        $quantity = (int)$this->request->input('quantity', 1);
        
        if ($this->wishlistService->purchaseItem($wishlist->id, $itemId, $quantity)) {
            return $this->redirect("/buyer/{$secretKey}")->withSuccess('Item marked as purchased!');
        }

        return $this->redirect("/buyer/{$secretKey}")->withError('Unable to mark item as purchased.');
    }
}
