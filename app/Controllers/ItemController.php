<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
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

        // Get background image for theme
        $background_image = '';
        if ($wishlist['theme_background_id'] != 0) {
            $stmt = \App\Core\Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$wishlist['theme_background_id']]);
            $bg_row = $stmt->get_result()->fetch_assoc();
            if ($bg_row) {
                $background_image = $bg_row['theme_image'];
            }
        }

        $data = [
            'user' => $user,
            'wishlist' => array_merge($wishlist, ['background_image' => $background_image]),
            'item_name' => $this->request->input('name', ''),
            'price' => $this->request->input('price', ''),
            'quantity' => $this->request->input('quantity', '1'),
            'unlimited' => $this->request->input('unlimited', 'No'),
            'link' => $this->request->input('link', ''),
            'notes' => $this->request->input('notes', ''),
            'priority' => $this->request->input('priority', '1'),
            'filename' => $this->request->input('filename', ''),
            'priority_options' => ["1", "2", "3", "4"]
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

        // Handle file upload - support both file upload and paste
        $filename = '';
        $hasImage = false;
        $uploadedFiles = []; // Track uploaded files for cleanup
        
        if ($this->request->hasFile('item_image')) {
            $file = $this->request->file('item_image');
            $uploadResult = $this->fileUploadService->uploadItemImage($file, $wishlistId, $data['name']);
            
            if (!$uploadResult['success']) {
                $errors['item_image'][] = $uploadResult['error'];
            } else {
                $filename = $uploadResult['filename'];
                $hasImage = true;
                $uploadedFiles[] = $uploadResult['filepath']; // Track for cleanup
            }
        } elseif (!empty($data['paste_image'])) {
            // Handle paste image (base64)
            $uploadResult = $this->fileUploadService->uploadFromBase64($data['paste_image'], $wishlistId, $data['name']);
            
            if (!$uploadResult['success']) {
                $errors['item_image'][] = $uploadResult['error'];
            } else {
                $filename = $uploadResult['filename'];
                $hasImage = true;
                $uploadedFiles[] = $uploadResult['filepath']; // Track for cleanup
            }
        }
        
        if (!$hasImage) {
            $errors['item_image'][] = 'Item image is required.';
        }

        if ($this->validationService->hasErrors($errors)) {
            // Don't clean up uploaded files - keep them for form persistence
            
            // Get background image for theme
            $background_image = '';
            if ($wishlist['theme_background_id'] != 0) {
                $stmt = \App\Core\Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$wishlist['theme_background_id']]);
                $bg_row = $stmt->get_result()->fetch_assoc();
                if ($bg_row) {
                    $background_image = $bg_row['theme_image'];
                }
            }
            
            return $this->view('items/create', [
                'user' => $user,
                'wishlist' => array_merge($wishlist, ['background_image' => $background_image]),
                'item_name' => $data['name'] ?? '',
                'price' => $data['price'] ?? '',
                'quantity' => $data['quantity'] ?? '1',
                'unlimited' => $data['unlimited'] ?? 'No',
                'link' => $data['link'] ?? '',
                'notes' => $data['notes'] ?? '',
                'priority' => $data['priority'] ?? '1',
                'filename' => $filename,
                'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
            ]);
        }

        // Filter data to only include database fields with proper defaults and correct types
        $itemData = [
            'name' => $data['name'],
            'notes' => $data['notes'] ?? '',
            'price' => $data['price'],
            'quantity' => (int)($data['quantity'] ?? 1), // Convert to integer
            'unlimited' => $data['unlimited'] ?? 'No', // Default to 'No' if checkbox not checked
            'link' => $data['link'],
            'image' => $filename,
            'priority' => $data['priority'] ?? '1',
            'quantity_purchased' => 0,
            'purchased' => 'No',
            'date_added' => date('Y-m-d H:i:s')
        ];
        
        $item = $this->wishlistService->addItem($wishlistId, $itemData);
        
        if ($item) {
            // Clean up any previously uploaded files that are no longer needed
            $this->fileUploadService->cleanupUploadedFiles($uploadedFiles);
            
            $pageno = $this->request->input('pageno', 1);
            return $this->redirect("/wishlist/{$wishlistId}?pageno={$pageno}")->withSuccess('Item added successfully!');
        }

        // Clean up uploaded files if database operation failed
        $this->fileUploadService->cleanupUploadedFiles($uploadedFiles);

        // Get background image for theme
        $background_image = '';
        if ($wishlist['theme_background_id'] != 0) {
            $stmt = \App\Core\Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$wishlist['theme_background_id']]);
            $bg_row = $stmt->get_result()->fetch_assoc();
            if ($bg_row) {
                $background_image = $bg_row['theme_image'];
            }
        }

        return $this->view('items/create', [
            'user' => $user,
            'wishlist' => array_merge($wishlist, ['background_image' => $background_image]),
            'item_name' => $data['name'] ?? '',
            'price' => $data['price'] ?? '',
            'quantity' => $data['quantity'] ?? '1',
            'unlimited' => $data['unlimited'] ?? 'No',
            'link' => $data['link'] ?? '',
            'notes' => $data['notes'] ?? '',
            'priority' => $data['priority'] ?? '1',
            'filename' => $filename,
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

        // Get background image for theme
        $background_image = '';
        if ($wishlist['theme_background_id'] != 0) {
            $stmt = \App\Core\Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$wishlist['theme_background_id']]);
            $bg_row = $stmt->get_result()->fetch_assoc();
            if ($bg_row) {
                $background_image = $bg_row['theme_image'];
            }
        }

        // Check for other copies of this item
        $otherCopies = false;
        $numberOfOtherCopies = 0;
        if (!empty($item['copy_id'])) {
            $stmt = \App\Core\Database::query(
                "SELECT COUNT(*) as count FROM items WHERE copy_id = ? AND id != ?",
                [$item['copy_id'], $itemId]
            );
            $result = $stmt->get_result()->fetch_assoc();
            $numberOfOtherCopies = $result['count'];
            $otherCopies = $numberOfOtherCopies > 0;
        }

        $data = [
            'user' => $user,
            'wishlist' => array_merge($wishlist, ['background_image' => $background_image]),
            'item' => $item,
            'item_name' => $this->request->input('name', $item['name']),
            'price' => $this->request->input('price', $item['price']),
            'quantity' => $this->request->input('quantity', $item['quantity']),
            'unlimited' => $this->request->input('unlimited', $item['unlimited']),
            'link' => $this->request->input('link', $item['link']),
            'notes' => $this->request->input('notes', $item['notes']),
            'priority' => $this->request->input('priority', $item['priority']),
            'otherCopies' => $otherCopies,
            'numberOfOtherCopies' => $numberOfOtherCopies,
            'priority_options' => ["1", "2", "3", "4"]
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

        // Handle file upload - support both file upload and paste
        $filename = $item['image']; // Keep existing image
        $imageChanged = false;
        $uploadedFiles = []; // Track uploaded files for cleanup
        
        if ($this->request->hasFile('item_image')) {
            $file = $this->request->file('item_image');
            $uploadResult = $this->fileUploadService->uploadItemImage($file, $wishlistId, $data['name']);
            
            if (!$uploadResult['success']) {
                $errors['item_image'][] = $uploadResult['error'];
            } else {
                $filename = $uploadResult['filename'];
                $imageChanged = true;
                $uploadedFiles[] = $uploadResult['filepath']; // Track for cleanup
            }
        } elseif (!empty($data['paste_image'])) {
            // Handle paste image (base64)
            $uploadResult = $this->fileUploadService->uploadFromBase64($data['paste_image'], $wishlistId, $data['name']);
            
            if (!$uploadResult['success']) {
                $errors['item_image'][] = $uploadResult['error'];
            } else {
                $filename = $uploadResult['filename'];
                $imageChanged = true;
                $uploadedFiles[] = $uploadResult['filepath']; // Track for cleanup
            }
        }

        if ($this->validationService->hasErrors($errors)) {
            // Don't clean up uploaded files - keep them for form persistence
            
            // Get background image for theme
            $background_image = '';
            if ($wishlist['theme_background_id'] != 0) {
                $stmt = \App\Core\Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$wishlist['theme_background_id']]);
                $bg_row = $stmt->get_result()->fetch_assoc();
                if ($bg_row) {
                    $background_image = $bg_row['theme_image'];
                }
            }
            
            return $this->view('items/edit', [
                'user' => $user,
                'wishlist' => array_merge($wishlist, ['background_image' => $background_image]),
                'item' => $item,
                'item_name' => $data['name'] ?? '',
                'price' => $data['price'] ?? '',
                'quantity' => $data['quantity'] ?? '1',
                'unlimited' => $data['unlimited'] ?? 'No',
                'link' => $data['link'] ?? '',
                'notes' => $data['notes'] ?? '',
                'priority' => $data['priority'] ?? '1',
                'error_msg' => $this->validationService->formatErrorsForDisplay($errors)
            ]);
        }

        // Filter data to only include database fields with proper defaults and correct types
        $itemData = [
            'name' => $data['name'],
            'notes' => $data['notes'] ?? '',
            'price' => $data['price'],
            'quantity' => (int)($data['quantity'] ?? 1), // Convert to integer
            'unlimited' => $data['unlimited'] ?? 'No', // Default to 'No' if checkbox not checked
            'link' => $data['link'],
            'image' => $filename,
            'priority' => $data['priority'] ?? '1'
        ];
        
        // Handle purchased status when quantity changes
        $unlimited = $data['unlimited'] ?? 'No';
        if ($unlimited == 'Yes') {
            $itemData['purchased'] = 'No';
        } else {
            $originalQuantity = $item['quantity'];
            $newQuantity = (int)($data['quantity'] ?? '1');
            $itemData['purchased'] = $newQuantity > $originalQuantity ? 'No' : $item['purchased'];
        }
        
        if ($this->wishlistService->updateItem($wishlistId, $itemId, $itemData)) {
            // Handle copied items updates (all fields, not just images)
            if (!empty($item['copy_id'])) {
                $this->wishlistService->updateCopiedItems($item['copy_id'], $itemData, $wishlistId, $this->fileUploadService);
            }
            
            // Delete old image if it was changed
            if ($imageChanged) {
                $this->fileUploadService->deleteItemImage($wishlistId, $item['image']);
            }
            
            // Clean up any previously uploaded files that are no longer needed
            $this->fileUploadService->cleanupUploadedFiles($uploadedFiles);
            
            $pageno = $this->request->input('pageno', 1);
            return $this->redirect("/wishlist/{$wishlistId}?pageno={$pageno}")->withSuccess('Item updated successfully!');
        }

        // Clean up uploaded files if database operation failed
        $this->fileUploadService->cleanupUploadedFiles($uploadedFiles);

        // Get background image for theme
        $background_image = '';
        if ($wishlist['theme_background_id'] != 0) {
            $stmt = \App\Core\Database::query("SELECT theme_image FROM themes WHERE theme_id = ?", [$wishlist['theme_background_id']]);
            $bg_row = $stmt->get_result()->fetch_assoc();
            if ($bg_row) {
                $background_image = $bg_row['theme_image'];
            }
        }

        return $this->view('items/edit', [
            'user' => $user,
            'wishlist' => array_merge($wishlist, ['background_image' => $background_image]),
            'item' => $item,
            'item_name' => $data['name'] ?? '',
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

        // Delete image file from all wishlists if it's a copied item
        if (!empty($item['copy_id'])) {
            $this->fileUploadService->deleteImageFromAllWishlists($item['copy_id'], $item['image']);
        } else {
            $this->fileUploadService->deleteItemImage($wishlistId, $item['image']);
        }

        if ($this->wishlistService->deleteItem($wishlistId, $itemId)) {
            $pageno = $this->request->input('pageno', 1);
            return $this->redirect("/wishlist/{$wishlistId}?pageno={$pageno}")->withSuccess('Item deleted successfully!');
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
