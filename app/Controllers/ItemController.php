<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\WishlistService;
use App\Services\ValidationService;
use App\Services\FileUploadService;

class ItemController extends Controller
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

    public function create(int $wishlistId): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        $data = [
            'user' => $user,
            'wishlist' => $wishlist,
            'item_name' => $this->request->input('item_name', ''),
            'price' => $this->request->input('price', ''),
            'quantity' => $this->request->input('quantity', '1'),
            'unlimited' => $this->request->input('unlimited', 'No'),
            'link' => $this->request->input('link', ''),
            'notes' => $this->request->input('notes', ''),
            'priority' => $this->request->input('priority', '1')
        ];

        return $this->view('items/create', $data);
    }

    public function store(int $wishlistId): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        $data = $this->request->input();
        $errors = $this->validationService->validateItem($data);

        // Handle file upload
        $filename = '';
        if ($this->request->hasFile('item_image')) {
            $file = $this->request->file('item_image');
            $uploadResult = $this->fileUploadService->uploadItemImage($file, $wishlistId, $data['name']);
            
            if (!$uploadResult['success']) {
                $errors['item_image'][] = $uploadResult['error'];
            } else {
                $filename = $uploadResult['filename'];
            }
        } else {
            $errors['item_image'][] = 'Item image is required.';
        }

        if ($this->validationService->hasErrors($errors)) {
            return $this->view('items/create', [
                'user' => $user,
                'wishlist' => $wishlist,
                'item_name' => $data['item_name'] ?? '',
                'price' => $data['price'] ?? '',
                'quantity' => $data['quantity'] ?? '1',
                'unlimited' => $data['unlimited'] ?? 'No',
                'link' => $data['link'] ?? '',
                'notes' => $data['notes'] ?? '',
                'priority' => $data['priority'] ?? '1',
                'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
            ]);
        }

        $itemData = array_merge($data, ['image' => $filename]);
        $item = $this->wishlistService->addItem($wishlistId, $itemData);
        
        if ($item) {
            return $this->redirect("/wishlist/{$wishlistId}")->withSuccess('Item added successfully!');
        }

        return $this->view('items/create', [
            'user' => $user,
            'wishlist' => $wishlist,
            'item_name' => $data['item_name'] ?? '',
            'price' => $data['price'] ?? '',
            'quantity' => $data['quantity'] ?? '1',
            'unlimited' => $data['unlimited'] ?? 'No',
            'link' => $data['link'] ?? '',
            'notes' => $data['notes'] ?? '',
            'priority' => $data['priority'] ?? '1',
            'error_msg' => '<div class="submit-error"><strong>Item creation failed:</strong><ul><li>Unable to add item. Please try again.</li></ul></div>'
        ]);
    }

    public function edit(int $wishlistId, int $itemId): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        $item = $this->wishlistService->getItem($wishlistId, $itemId);
        
        if (!$item) {
            return $this->redirect("/wishlist/{$wishlistId}")->withError('Item not found.');
        }

        $data = [
            'user' => $user,
            'wishlist' => $wishlist,
            'item' => $item,
            'item_name' => $this->request->input('item_name', $item->name),
            'price' => $this->request->input('price', $item->price),
            'quantity' => $this->request->input('quantity', $item->quantity),
            'unlimited' => $this->request->input('unlimited', $item->unlimited),
            'link' => $this->request->input('link', $item->link),
            'notes' => $this->request->input('notes', $item->notes),
            'priority' => $this->request->input('priority', $item->priority)
        ];

        return $this->view('items/edit', $data);
    }

    public function update(int $wishlistId, int $itemId): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        $item = $this->wishlistService->getItem($wishlistId, $itemId);
        
        if (!$item) {
            return $this->redirect("/wishlist/{$wishlistId}")->withError('Item not found.');
        }

        $data = $this->request->input();
        $errors = $this->validationService->validateItem($data);

        // Handle file upload if new image provided
        $filename = $item->image; // Keep existing image
        if ($this->request->hasFile('item_image')) {
            $file = $this->request->file('item_image');
            $uploadResult = $this->fileUploadService->uploadItemImage($file, $wishlistId, $data['name']);
            
            if (!$uploadResult['success']) {
                $errors['item_image'][] = $uploadResult['error'];
            } else {
                // Delete old image
                $this->fileUploadService->deleteItemImage($wishlistId, $item->image);
                $filename = $uploadResult['filename'];
            }
        }

        if ($this->validationService->hasErrors($errors)) {
            return $this->view('items/edit', [
                'user' => $user,
                'wishlist' => $wishlist,
                'item' => $item,
                'item_name' => $data['item_name'] ?? '',
                'price' => $data['price'] ?? '',
                'quantity' => $data['quantity'] ?? '1',
                'unlimited' => $data['unlimited'] ?? 'No',
                'link' => $data['link'] ?? '',
                'notes' => $data['notes'] ?? '',
                'priority' => $data['priority'] ?? '1',
                'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
            ]);
        }

        $itemData = array_merge($data, ['image' => $filename]);
        
        if ($this->wishlistService->updateItem($wishlistId, $itemId, $itemData)) {
            return $this->redirect("/wishlist/{$wishlistId}")->withSuccess('Item updated successfully!');
        }

        return $this->view('items/edit', [
            'user' => $user,
            'wishlist' => $wishlist,
            'item' => $item,
            'item_name' => $data['item_name'] ?? '',
            'price' => $data['price'] ?? '',
            'quantity' => $data['quantity'] ?? '1',
            'unlimited' => $data['unlimited'] ?? 'No',
            'link' => $data['link'] ?? '',
            'notes' => $data['notes'] ?? '',
            'priority' => $data['priority'] ?? '1',
            'error_msg' => '<div class="submit-error"><strong>Update failed:</strong><ul><li>Unable to update item. Please try again.</li></ul></div>'
        ]);
    }

    public function delete(int $wishlistId, int $itemId): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        $item = $this->wishlistService->getItem($wishlistId, $itemId);
        
        if (!$item) {
            return $this->redirect("/wishlist/{$wishlistId}")->withError('Item not found.');
        }

        // Delete image file
        $this->fileUploadService->deleteItemImage($wishlistId, $item->image);

        if ($this->wishlistService->deleteItem($wishlistId, $itemId)) {
            return $this->redirect("/wishlist/{$wishlistId}")->withSuccess('Item deleted successfully!');
        }

        return $this->redirect("/wishlist/{$wishlistId}")->withError('Unable to delete item. Please try again.');
    }

    public function purchase(int $wishlistId, int $itemId): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        $item = $this->wishlistService->getItem($wishlistId, $itemId);
        
        if (!$item) {
            return $this->redirect("/wishlist/{$wishlistId}")->withError('Item not found.');
        }

        $quantity = (int)$this->request->input('quantity', 1);
        
        if ($this->wishlistService->purchaseItem($wishlistId, $itemId, $quantity)) {
            return $this->redirect("/wishlist/{$wishlistId}")->withSuccess('Item marked as purchased!');
        }

        return $this->redirect("/wishlist/{$wishlistId}")->withError('Unable to mark item as purchased.');
    }
}
