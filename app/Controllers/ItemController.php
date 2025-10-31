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
    public function __construct(
        private WishlistService $wishlistService = new WishlistService(),
        private ItemRequestValidator $itemValidator = new ItemRequestValidator(),
        private FileUploadService $fileUploadService = new FileUploadService()
    ) {
        parent::__construct();
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

        // Clear any existing fetched data when starting fresh
        \App\Services\SessionManager::clearFetchedItemData();

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

        // Check for fetched data in session with timestamp expiry (10 minutes)
        $fetchedData = \App\Services\SessionManager::getFetchedItemData();
        
        // Use session data if available, otherwise use URL parameters as fallback
        $itemName = $fetchedData['title'] ?? $this->request->input('name', '');
        $price = $fetchedData['price'] ?? $this->request->input('price', '');
        $link = $fetchedData['link'] ?? $this->request->input('link', '');
        $image = $fetchedData['image'] ?? $this->request->input('image_url', '');
        $notes = $fetchedData['product_details'] ?? $this->request->input('notes', '');
        
        // Check if we have partial data (some fields filled but not all)
        $hasPartialData = false;
        if ($fetchedData && !($fetchedData['fetch_error'] ?? false)) {
            $filledFields = 0;
            if (!empty($itemName)) $filledFields++;
            if (!empty($price)) $filledFields++;
            if (!empty($image)) $filledFields++;
            if (!empty($notes)) $filledFields++;
            
            // If we have some but not all fields, it's partial data
            $hasPartialData = ($filledFields > 0 && $filledFields < 4);
        }
        
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
            'fetched_image_url' => $image, // Pass fetched image URL to form
            'fetch_error' => $fetchedData['fetch_error'] ?? false, // Pass error state
            'fetch_error_message' => $fetchedData['error_message'] ?? '', // Pass error message
            'has_partial_data' => $hasPartialData // Pass partial data flag
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
        \App\Services\SessionManager::clearFetchedItemData();

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
            // Check if it's a URL or base64 data
            if (filter_var($data['paste_image'], FILTER_VALIDATE_URL)) {
                // Handle image URL
                $uploadResult = $this->fileUploadService->uploadFromUrl($data['paste_image'], $wishlistId, $data['name']);
            } else {
                // Handle paste image (base64)
                $uploadResult = $this->fileUploadService->uploadFromBase64($data['paste_image'], $wishlistId, $data['name']);
            }
            
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
            'date_added' => date(format: 'Y-m-d H:i:s')
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
            'filename' => $item['image'], // Pass existing image filename
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
            // Only process paste_image if it's a URL or valid base64 data
            $pasteData = trim($data['paste_image']);
            
            // Check if it's a URL pointing to an external resource (not our local images)
            if (filter_var($pasteData, FILTER_VALIDATE_URL) && strpos($pasteData, '/wishlist/public/images/') === false) {
                $uploadResult = $this->fileUploadService->uploadFromUrl($pasteData, $wishlistId, $data['name']);
                
                if (!$uploadResult['success']) {
                    $errors['item_image'][] = $uploadResult['error'];
                } else {
                    $filename = $uploadResult['filename'];
                    $imageChanged = true;
                    $uploadedFiles[] = $uploadResult['filepath']; // Track for cleanup
                }
            } elseif (strpos($pasteData, 'data:image') === 0 || (strlen($pasteData) > 100 && base64_decode($pasteData, true) !== false)) {
                // It's base64 data - either with data URI prefix or raw base64
                $uploadResult = $this->fileUploadService->uploadFromBase64($pasteData, $wishlistId, $data['name']);
                
                if (!$uploadResult['success']) {
                    $errors['item_image'][] = $uploadResult['error'];
                } else {
                    $filename = $uploadResult['filename'];
                    $imageChanged = true;
                    $uploadedFiles[] = $uploadResult['filepath']; // Track for cleanup
                }
            }
            // If it's neither URL nor base64, ignore it (likely leftover form data)
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
                'item_name' => $data['name'] ?? $item['name'],
                'price' => $data['price'] ?? $item['price'],
                'quantity' => $data['quantity'] ?? $item['quantity'],
                'unlimited' => $data['unlimited'] ?? $item['unlimited'],
                'link' => $data['link'] ?? $item['link'],
                'notes' => $data['notes'] ?? $item['notes'],
                'priority' => $data['priority'] ?? $item['priority'],
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
            'date_modified' => date(format: 'Y-m-d H:i:s') // Add current timestamp for synchronization
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
            'item_name' => $data['name'] ?? $item['name'],
            'price' => $data['price'] ?? $item['price'],
            'quantity' => $data['quantity'] ?? $item['quantity'],
            'unlimited' => $data['unlimited'] ?? $item['unlimited'],
            'link' => $data['link'] ?? $item['link'],
            'notes' => $data['notes'] ?? $item['notes'],
            'priority' => $data['priority'] ?? $item['priority'],
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
                'product_details' => $result['product_details'] ?? '',
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
            $fetch_error = $this->request->input('fetch_error', 'false');
            $error_message = $this->request->input('error_message', '');
            
            // Convert string to boolean
            $isError = ($fetch_error === 'true' || $fetch_error === true);
            
            // Truncate title to 100 characters (database limit)
            if (strlen($title) > 100) {
                $title = substr($title, 0, 97) . '...';
            }
            
            // Store in session using SessionManager
            \App\Services\SessionManager::setFetchedItemData([
                'title' => $title,
                'price' => $price,
                'link' => $link,
                'image' => $image,
                'product_details' => $product_details,
                'fetch_error' => $isError,
                'error_message' => $error_message
            ]);
            
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
