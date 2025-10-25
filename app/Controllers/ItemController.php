<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\WishlistService;
use App\Validation\ItemRequestValidator;
use App\Services\FileUploadService;
use App\Services\ThemeService;

class ItemController extends Controller
{
    private WishlistService $wishlistService;
    private ItemRequestValidator $itemValidator;
    private FileUploadService $fileUploadService;

    public function __construct()
    {
        parent::__construct();
        $this->wishlistService = new WishlistService();
        $this->itemValidator = new ItemRequestValidator();
        $this->fileUploadService = new FileUploadService();
    }

    /**
     * Show step 1: URL input page
     */
    public function addStep1(int $wishlistId): Response
    {
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        // Get background image for theme
        $background_image = ThemeService::getBackgroundImage($wishlist['theme_background_id']) ?? '';

        $data = [
            'user' => $user,
            'wishlist' => array_merge($wishlist, ['background_image' => $background_image])
        ];

        return $this->view('items/add-step1', $data);
    }

    /**
     * Show step 2: Item form (with optional pre-filled data)
     */
    public function create(int $wishlistId): Response
    {
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }

        // Get background image for theme
        $background_image = ThemeService::getBackgroundImage($wishlist['theme_background_id']) ?? '';

        // Check for fetched data in session
        $fetchedData = $_SESSION['fetched_item_data'] ?? null;
        
        // Use session data if available, otherwise use URL parameters as fallback
        $itemName = $fetchedData['title'] ?? $this->request->input('name', '');
        $price = $fetchedData['price'] ?? $this->request->input('price', '');
        $link = $fetchedData['link'] ?? $this->request->input('link', '');
        $image = $fetchedData['image'] ?? $this->request->input('image_url', '');
        $notes = $fetchedData['product_details'] ?? $this->request->input('notes', '');
        
        $data = [
            'user' => $user,
            'wishlist' => array_merge($wishlist, ['background_image' => $background_image]),
            'item_name' => $itemName,
            'price' => $price,
            'quantity' => $this->request->input('quantity', '1'),
            'unlimited' => $this->request->input('unlimited', 'No'),
            'link' => $link,
            'notes' => $notes,
            'priority' => $this->request->input('priority', '1'),
            'filename' => $this->request->input('filename', ''),
            'priority_options' => ["1", "2", "3", "4"],
            'add' => true,
            'fetched_data' => $fetchedData, // Pass to view for display
            'fetched_image_url' => $image // Pass fetched image URL to form
        ];

        return $this->view('items/create', $data);
    }

    public function store(int $wishlistId): Response
    {
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist')->withError('Wishlist not found.');
        }
        
        // Clear fetched data from session after form submission
        if (isset($_SESSION['fetched_item_data'])) {
            unset($_SESSION['fetched_item_data']);
        }

        $data = $this->request->input();
        $errors = $this->itemValidator->validateItem($data);

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

        if ($this->itemValidator->hasErrors($errors)) {
            // Don't clean up uploaded files - keep them for form persistence
            
            // Get background image for theme
            $background_image = '';
            $background_image = ThemeService::getBackgroundImage($wishlist['theme_background_id']) ?? '';
            
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
                'error_msg' => $this->itemValidator->formatErrorsForDisplay($errors)
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
            // Don't clean up uploaded files - they are now part of the item
            // $this->fileUploadService->cleanupUploadedFiles($uploadedFiles);
            
            $pageno = $this->request->input('pageno', 1);
            return $this->redirect("/wishlist/{$wishlistId}?pageno={$pageno}")->withSuccess('Item added successfully!');
        }

        // Clean up uploaded files if database operation failed
        $this->fileUploadService->cleanupUploadedFiles($uploadedFiles);

        // Get background image for theme
        $background_image = ThemeService::getBackgroundImage($wishlist['theme_background_id']) ?? '';

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
        $background_image = ThemeService::getBackgroundImage($wishlist['theme_background_id']) ?? '';

        // Check for other copies of this item
        $otherCopies = false;
        $numberOfOtherCopies = 0;
        if (!empty($item['copy_id'])) {
            $numberOfOtherCopies = $this->wishlistService->getOtherCopiesCount($item['copy_id'], $itemId);
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
        $errors = $this->itemValidator->validateItem($data);

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
        } elseif (!empty($data['existing_image']) && $data['existing_image'] !== $item['image']) {
            // Use existing uploaded image from previous validation error
            $filename = $data['existing_image'];
            $imageChanged = true;
        }

        if ($this->itemValidator->hasErrors($errors)) {
            // Don't clean up uploaded files - keep them for form persistence
            
            // Get background image for theme
            $background_image = '';
            $background_image = ThemeService::getBackgroundImage($wishlist['theme_background_id']) ?? '';
            
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
                'filename' => $filename,
                'otherCopies' => $otherCopies ?? false,
                'numberOfOtherCopies' => $numberOfOtherCopies ?? 0,
                'error_msg' => $this->itemValidator->formatErrorsForDisplay($errors)
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
            'date_modified' => date('Y-m-d H:i:s') // Add current timestamp for synchronization
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
            
            // Don't clean up uploaded files - they are now part of the item
            // $this->fileUploadService->cleanupUploadedFiles($uploadedFiles);
            
            $pageno = $this->request->input('pageno', 1);
            return $this->redirect("/wishlist/{$wishlistId}?pageno={$pageno}")->withSuccess('Item updated successfully!');
        }

        // Clean up uploaded files if database operation failed
        $this->fileUploadService->cleanupUploadedFiles($uploadedFiles);

        // Get background image for theme
        $background_image = ThemeService::getBackgroundImage($wishlist['theme_background_id']) ?? '';

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
            'filename' => $filename,
            'otherCopies' => $otherCopies ?? false,
            'numberOfOtherCopies' => $numberOfOtherCopies ?? 0,
            'error_msg' => '<div class="submit-error"><strong>Update failed:</strong><ul><li>Unable to update item. Please try again.</li></ul></div>'
        ]);
    }

    public function delete(int $wishlistId, int $itemId): Response
    {
        
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

    /**
     * Fetch URL metadata via AJAX
     * 
     * @return Response JSON response with extracted metadata
     */
    public function fetchUrlMetadata(): Response
    {
        // Start output buffering to catch any unwanted output
        ob_start();
        
        try {
            // Validate request
            if (!$this->request->isPost()) {
                ob_end_clean();
                return $this->json(['success' => false, 'error' => 'Invalid request method']);
            }

            $url = $this->request->input('url');
            
            if (empty($url)) {
                ob_end_clean();
                return $this->json(['success' => false, 'error' => 'URL is required']);
            }

            // Validate URL format
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                ob_end_clean();
                return $this->json(['success' => false, 'error' => 'Invalid URL format']);
            }

            // Use UrlMetadataService to fetch metadata
            $metadataService = new \App\Services\UrlMetadataService();
            $result = $metadataService->fetchMetadata($url);

            // Clean any output that might have been generated
            ob_end_clean();

            // Return JSON response
            return $this->json([
                'success' => $result['success'],
                'title' => $result['title'],
                'price' => $result['price'],
                'image' => $result['image'],
                'error' => $result['error']
            ]);

        } catch (\Exception $e) {
            // Clean output buffer
            ob_end_clean();
            
            // Log error for debugging
            error_log('UrlMetadataService error: ' . $e->getMessage());
            
            return $this->json([
                'success' => false,
                'error' => 'An error occurred while fetching URL metadata. Please try again.'
            ]);
        }
    }

    /**
     * Store fetched URL data in session
     */
    public function storeFetchedData(): Response
    {
        try {
            $title = $this->request->input('title', '');
            $price = $this->request->input('price', '');
            $link = $this->request->input('link', '');
            $image = $this->request->input('image', '');
            $product_details = $this->request->input('product_details', '');
            
            // Store in session
            $_SESSION['fetched_item_data'] = [
                'title' => $title,
                'price' => $price,
                'link' => $link,
                'image' => $image,
                'product_details' => $product_details,
                'timestamp' => time()
            ];
            
            return $this->json(['success' => true]);
            
        } catch (\Exception $e) {
            error_log('Store fetched data error: ' . $e->getMessage());
            return $this->json(['success' => false, 'error' => 'Failed to store data']);
        }
    }

    /**
     * Test endpoint to verify JSON response
     */
    public function testJson(): Response
    {
        return $this->json([
            'success' => true,
            'message' => 'Test JSON response',
            'timestamp' => time()
        ]);
    }
}
