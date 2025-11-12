<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\WishlistService;
use App\Validation\ItemRequestValidator;
use App\Services\FileUploadService;
use App\Services\ThemeService;
use App\Services\ItemCopyService;
use App\Models\Item;

class ItemController extends Controller
{
    public function __construct(
        private WishlistService $wishlistService = new WishlistService(),
        private ItemRequestValidator $itemValidator = new ItemRequestValidator(),
        private FileUploadService $fileUploadService = new FileUploadService(),
        private ItemCopyService $itemCopyService = new ItemCopyService()
    ) {
        parent::__construct();
    }

    /**
     * Validate and convert ID parameter to integer
     * Returns 404 error page if ID is not numeric
     * 
     * @param string|int $id The ID to validate
     * @return int|Response Returns integer ID or 404 Response
     */
    private function validateId(string|int $id): int|Response
    {
        if (!is_numeric($id)) {
            return $this->view('errors/404', ['title' => '404 - Page Not Found'], 'error');
        }
        return (int) $id;
    }

    /**
     * Show step 1: URL input page
     */
    public function addStep1(string|int $wishlistId): Response
    {
        $user = $this->auth();
        
        $wishlistId = $this->validateId($wishlistId);
        if ($wishlistId instanceof Response) {
            return $wishlistId;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        // Clear any existing fetched data when starting fresh
        \App\Services\SessionManager::clearFetchedItemData();

        // Get background image for theme
        $background_image = ThemeService::getBackgroundImage($wishlist['theme_background_id']) ?? '';

        $data = [
            'title' => 'Add Item to ' . $wishlist['wishlist_name'],
            'user' => $user,
            'wishlist' => array_merge($wishlist, ['background_image' => $background_image])
        ];

        return $this->view('items/add-step1', $data);
    }

    /**
     * Show step 2: Item form (with optional pre-filled data)
     */
    public function create(string|int $wishlistId): Response
    {
        
        $user = $this->auth();
        
        $wishlistId = $this->validateId($wishlistId);
        if ($wishlistId instanceof Response) {
            return $wishlistId;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        // Get background image for theme
        $background_image = ThemeService::getBackgroundImage($wishlist['theme_background_id']) ?? '';

        // Check for validation errors stored in session (from POST redirect)
        $sessionErrors = \App\Services\SessionManager::get('item_create_errors');
        $sessionFormData = \App\Services\SessionManager::get('item_create_form_data');
        
        // Clear session data after reading (one-time use) - always clear if key exists
        if (\App\Services\SessionManager::has('item_create_errors')) {
            \App\Services\SessionManager::remove('item_create_errors');
        }
        if (\App\Services\SessionManager::has('item_create_form_data')) {
            \App\Services\SessionManager::remove('item_create_form_data');
        }

        // Check for fetched data in session with timestamp expiry (10 minutes)
        $fetchedData = \App\Services\SessionManager::getFetchedItemData();
        
        // Use session form data (from validation errors) first, then fetched data, then request input
        $itemName = $sessionFormData['name'] ?? ($fetchedData['title'] ?? $this->request->input('name', ''));
        $price = $sessionFormData['price'] ?? ($fetchedData['price'] ?? $this->request->input('price', ''));
        $link = $sessionFormData['link'] ?? ($fetchedData['link'] ?? $this->request->input('link', ''));
        // For image: prioritize session fetched_image_url (pasted), then fetched data, then request input
        $image = $sessionFormData['fetched_image_url'] ?? ($fetchedData['image'] ?? $this->request->input('image_url', ''));
        $notes = $sessionFormData['notes'] ?? ($fetchedData['product_details'] ?? $this->request->input('notes', ''));
        $quantity = $sessionFormData['quantity'] ?? $this->request->input('quantity', '1');
        $unlimited = $sessionFormData['unlimited'] ?? $this->request->input('unlimited', 'No');
        $priority = $sessionFormData['priority'] ?? $this->request->input('priority', '1');
        
        // Handle temp filename for preview
        $tempFilename = $sessionFormData['temp_filename'] ?? '';
        $isTemp = $sessionFormData['is_temp'] ?? false;
        $filename = ''; // For form hidden field
        if ($isTemp && !empty($tempFilename)) {
            // Use temp image URL for preview
            $filename = \App\Services\FileUploadService::getTempImageUrl($tempFilename);
        } else {
            $filename = $sessionFormData['filename'] ?? $this->request->input('filename', '');
        }
        
        $error_msg = (!empty($sessionErrors)) ? $this->itemValidator->formatErrorsForDisplay($sessionErrors) : '';
        
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
            'title' => 'Add Item to ' . $wishlist['wishlist_name'],
            'user' => $user,
            'wishlist' => array_merge($wishlist, ['background_image' => $background_image]),
            'item_name' => $itemName,
            'price' => $price,
            'quantity' => $quantity,
            'unlimited' => $unlimited,
            'link' => $link,
            'notes' => $notes,
            'priority' => $priority,
            'filename' => $filename,
            'temp_filename' => $tempFilename, // Pass temp filename for resubmission
            'is_temp' => $isTemp, // Indicate if image is in temp folder
            'error_msg' => $error_msg,
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

    public function store(string|int $wishlistId): Response
    {
        
        $user = $this->auth();
        
        $wishlistId = $this->validateId($wishlistId);
        if ($wishlistId instanceof Response) {
            return $wishlistId;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }
        
        // Clear fetched data from session after form submission
        \App\Services\SessionManager::clearFetchedItemData();

        $data = $this->request->input();
        
        // Prepare data for validation (include file upload and temp filename info)
        $validationData = $data;
        if ($this->request->hasFile('item_image')) {
            $validationData['item_image'] = 'uploaded'; // Mark that file was uploaded
        }
        
        $errors = $this->itemValidator->validateItem($validationData, false); // false = create operation

        // Handle file upload - upload to TEMP folder first (for validation)
        $tempFilename = '';
        $hasImage = false;
        $isTempImage = false;
        
        // Check if we're resubmitting with an existing temp file from form or session
        $sessionFormData = \App\Services\SessionManager::get('item_create_form_data');
        $existingTempFilename = $data['temp_filename'] ?? ($sessionFormData['temp_filename'] ?? '');
        
        if (!empty($existingTempFilename) && empty($data['item_image']) && empty($data['paste_image'])) {
            // User is resubmitting, using existing temp file
            $tempFilename = $existingTempFilename;
            $isTempImage = true;
            $hasImage = true;
        } elseif ($this->request->hasFile('item_image')) {
            // New file upload - upload to temp
            $file = $this->request->file('item_image');
            $uploadResult = $this->fileUploadService->uploadItemImageToTemp($file, $data['name'] ?? 'item');
            
            if (!$uploadResult['success']) {
                $errors['item_image'][] = $uploadResult['error'];
            } else {
                $tempFilename = $uploadResult['filename'];
                $isTempImage = true;
                $hasImage = true;
            }
        } elseif (!empty($data['paste_image'])) {
            // Paste image - upload to temp
            if (filter_var($data['paste_image'], FILTER_VALIDATE_URL)) {
                // Handle image URL
                $uploadResult = $this->fileUploadService->uploadFromUrlToTemp($data['paste_image'], $data['name'] ?? 'item');
            } else {
                // Handle paste image (base64)
                $uploadResult = $this->fileUploadService->uploadFromBase64ToTemp($data['paste_image'], $data['name'] ?? 'item');
            }
            
            if (!$uploadResult['success']) {
                $errors['item_image'][] = $uploadResult['error'];
            } else {
                $tempFilename = $uploadResult['filename'];
                $isTempImage = true;
                $hasImage = true;
            }
        }

        if ($this->itemValidator->hasErrors($errors)) {
            // Keep temp files for form persistence - store temp filename in session
            
            // Store form data and errors in session for redirect
            \App\Services\SessionManager::set('item_create_errors', $errors);
            \App\Services\SessionManager::set('item_create_form_data', [
                'name' => $data['name'] ?? '',
                'price' => $data['price'] ?? '',
                'quantity' => $data['quantity'] ?? '1',
                'unlimited' => $data['unlimited'] ?? 'No',
                'link' => $data['link'] ?? '',
                'notes' => $data['notes'] ?? '',
                'priority' => $data['priority'] ?? '1',
                'temp_filename' => $tempFilename, // Store temp filename
                'is_temp' => $isTempImage,
                'fetched_image_url' => !empty($data['paste_image']) ? $data['paste_image'] : ''
            ]);
            
            // Redirect to GET create route (POST-Redirect-GET pattern)
            return $this->redirect("/wishlists/{$wishlistId}/item/create");
        }

        // Validation passed - move temp file to final destination
        $filename = '';
        if ($isTempImage && !empty($tempFilename)) {
            $moveResult = $this->fileUploadService->moveTempToFinal($tempFilename, $wishlistId, $data['name'] ?? 'item');
            if (!$moveResult['success']) {
                // If move fails, delete temp file and return error
                $this->fileUploadService->deleteTempImage($tempFilename);
                \App\Services\SessionManager::set('item_create_errors', ['general' => ['Failed to save image. Please try again.']]);
                \App\Services\SessionManager::set('item_create_form_data', [
                    'name' => $data['name'] ?? '',
                    'price' => $data['price'] ?? '',
                    'quantity' => $data['quantity'] ?? '1',
                    'unlimited' => $data['unlimited'] ?? 'No',
                    'link' => $data['link'] ?? '',
                    'notes' => $data['notes'] ?? '',
                    'priority' => $data['priority'] ?? '1'
                ]);
                return $this->redirect("/wishlists/{$wishlistId}/item/create");
            }
            $filename = $moveResult['filename'];
        } else {
            // No image (shouldn't happen due to validation, but handle it)
            $errors['item_image'][] = 'Item image is required.';
            \App\Services\SessionManager::set('item_create_errors', $errors);
            return $this->redirect("/wishlists/{$wishlistId}/item/create");
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
            // Success! Temp file was already moved to final destination by moveTempToFinal()
            // Clear session form data
            \App\Services\SessionManager::remove('item_create_form_data');
            \App\Services\SessionManager::remove('item_create_errors');
            
            $pageno = $this->request->input('pageno', 1);
            return $this->redirect("/wishlists/{$wishlistId}?pageno={$pageno}")->withSuccess('Item added successfully!');
        }

        // Database operation failed - clean up temp file
        if ($isTempImage && !empty($tempFilename)) {
            $this->fileUploadService->deleteTempImage($tempFilename);
        }

        // Store form data and error in session for redirect
        \App\Services\SessionManager::set('item_create_errors', [
            'general' => ['Unable to add item. Please try again.']
        ]);
        \App\Services\SessionManager::set('item_create_form_data', [
            'name' => $data['name'] ?? '',
            'price' => $data['price'] ?? '',
            'quantity' => $data['quantity'] ?? '1',
            'unlimited' => $data['unlimited'] ?? 'No',
            'link' => $data['link'] ?? '',
            'notes' => $data['notes'] ?? '',
            'priority' => $data['priority'] ?? '1',
            'filename' => $filename,
            'fetched_image_url' => !empty($data['paste_image']) ? $data['paste_image'] : ''
        ]);

        // Redirect to GET create route (POST-Redirect-GET pattern)
        return $this->redirect("/wishlists/{$wishlistId}/item/create");
    }

    public function edit(string|int $wishlistId, string|int $itemId): Response
    {
        
        $user = $this->auth();
        
        $wishlistId = $this->validateId($wishlistId);
        if ($wishlistId instanceof Response) {
            return $wishlistId;
        }
        
        $itemId = $this->validateId($itemId);
        if ($itemId instanceof Response) {
            return $itemId;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        $item = $this->wishlistService->getItem($wishlistId, $itemId);
        
        if (!$item) {
            return $this->redirect("/wishlists/{$wishlistId}")->withError('Item not found.');
        }

        // Get background image for theme
        $background_image = ThemeService::getBackgroundImage($wishlist['theme_background_id']) ?? '';

        // Check for validation errors stored in session (from POST redirect)
        $sessionErrors = \App\Services\SessionManager::get('item_edit_errors');
        $sessionFormData = \App\Services\SessionManager::get('item_edit_form_data');
        
        // Clear session data after reading (one-time use) - always clear if key exists
        if (\App\Services\SessionManager::has('item_edit_errors')) {
            \App\Services\SessionManager::remove('item_edit_errors');
        }
        if (\App\Services\SessionManager::has('item_edit_form_data')) {
            \App\Services\SessionManager::remove('item_edit_form_data');
        }

        // Check for other copies of this item
        // Determine the original copy_id - if item is already a copy, use its copy_id, otherwise use its id
        $originalCopyId = $item['copy_id'] ?: $itemId;
        $otherCopies = false;
        $numberOfOtherCopies = 0;
        if ($originalCopyId) {
            $numberOfOtherCopies = $this->wishlistService->getOtherCopiesCount($originalCopyId, $itemId);
            $otherCopies = $numberOfOtherCopies > 0;
        }

        // Get pageno from request (for back button) - check session data first (from validation errors), then request
        $pageno = (int) ($sessionFormData['pageno'] ?? $this->request->get('pageno', 1));
        
        // Get search term from request (for back button and form persistence)
        $searchTerm = $sessionFormData['search'] ?? $this->request->get('search', '');

        // Use session form data if available (from validation errors), otherwise use request input or item defaults
        $item_name = $sessionFormData['name'] ?? $this->request->input('name', $item['name']);
        $price = $sessionFormData['price'] ?? $this->request->input('price', $item['price']);
        $quantity = $sessionFormData['quantity'] ?? $this->request->input('quantity', $item['quantity']);
        $unlimited = $sessionFormData['unlimited'] ?? $this->request->input('unlimited', $item['unlimited']);
        $link = $sessionFormData['link'] ?? $this->request->input('link', $item['link']);
        $notes = $sessionFormData['notes'] ?? $this->request->input('notes', $item['notes']);
        $priority = $sessionFormData['priority'] ?? $this->request->input('priority', $item['priority']);
        
        // Handle image preview - check for temp image or use existing
        $tempFilename = $sessionFormData['temp_filename'] ?? '';
        $isTemp = $sessionFormData['is_temp'] ?? false;
        $hasNewImage = $sessionFormData['has_new_image'] ?? false;
        $oldImage = $sessionFormData['old_image'] ?? $item['image'];
        
        if ($hasNewImage && $isTemp && !empty($tempFilename)) {
            // Show temp image preview
            $filename = \App\Services\FileUploadService::getTempImageUrl($tempFilename);
        } else {
            // Show existing image
            $filename = $oldImage;
        }
        
        $error_msg = (!empty($sessionErrors)) ? $this->itemValidator->formatErrorsForDisplay($sessionErrors) : '';

        $data = [
            'title' => 'Edit Item on ' . $wishlist['wishlist_name'],
            'user' => $user,
            'wishlist' => array_merge($wishlist, ['background_image' => $background_image]),
            'item' => $item,
            'item_name' => $item_name,
            'price' => $price,
            'quantity' => $quantity,
            'unlimited' => $unlimited,
            'link' => $link,
            'notes' => $notes,
            'priority' => $priority,
            'filename' => $filename,
            'temp_filename' => $tempFilename, // Pass temp filename for resubmission
            'is_temp' => $isTemp, // Indicate if image is in temp folder
            'has_new_image' => $hasNewImage, // Indicate if there's a new image
            'otherCopies' => $otherCopies,
            'numberOfOtherCopies' => $numberOfOtherCopies,
            'error_msg' => $error_msg,
            'priority_options' => ["1", "2", "3", "4"],
            'pageno' => $pageno, // Pass pageno for back button
            'searchTerm' => $searchTerm // Pass search term for back button
        ];

        return $this->view('items/edit', $data);
    }

    public function update(string|int $wishlistId, string|int $itemId): Response
    {
        
        $user = $this->auth();
        
        $wishlistId = $this->validateId($wishlistId);
        if ($wishlistId instanceof Response) {
            return $wishlistId;
        }
        
        $itemId = $this->validateId($itemId);
        if ($itemId instanceof Response) {
            return $itemId;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        $item = $this->wishlistService->getItem($wishlistId, $itemId);
        
        if (!$item) {
            return $this->redirect("/wishlists/{$wishlistId}")->withError('Item not found.');
        }

        $data = $this->request->input();
        $oldImage = $item['image']; // Store old image for potential cleanup
        
        // Prepare data for validation (include file upload info)
        $validationData = $data;
        if ($this->request->hasFile('item_image')) {
            $validationData['item_image'] = 'uploaded'; // Mark that file was uploaded
        }
        
        $errors = $this->itemValidator->validateItem($validationData, true, $oldImage); // true = edit operation, pass existing image

        // Handle file upload - upload to TEMP folder first (for validation)
        $tempFilename = '';
        $hasNewImage = false;
        $isTempImage = false;
        
        // Check if we're resubmitting with an existing temp file from form or session
        $sessionFormData = \App\Services\SessionManager::get('item_edit_form_data');
        $existingTempFilename = $data['temp_filename'] ?? ($sessionFormData['temp_filename'] ?? '');
        
        if (!empty($existingTempFilename) && empty($data['item_image']) && empty($data['paste_image'])) {
            // User is resubmitting, using existing temp file
            $tempFilename = $existingTempFilename;
            $isTempImage = true;
            $hasNewImage = true;
        } elseif ($this->request->hasFile('item_image')) {
            // New file upload - upload to temp
            $file = $this->request->file('item_image');
            $uploadResult = $this->fileUploadService->uploadItemImageToTemp($file, $data['name'] ?? 'item');
            
            if (!$uploadResult['success']) {
                $errors['item_image'][] = $uploadResult['error'];
            } else {
                $tempFilename = $uploadResult['filename'];
                $isTempImage = true;
                $hasNewImage = true;
            }
        } elseif (!empty($data['paste_image'])) {
            // Paste image - upload to temp (only if it's new data, not existing image)
            $pasteData = trim($data['paste_image']);
            
            // Check if it's pointing to our existing image (don't re-upload)
            if (strpos($pasteData, '/public/images/item-images/') !== false) {
                // It's pointing to an existing image, keep the existing one
                $hasNewImage = false;
            } elseif (filter_var($pasteData, FILTER_VALIDATE_URL)) {
                // External URL - upload to temp
                $uploadResult = $this->fileUploadService->uploadFromUrlToTemp($pasteData, $data['name'] ?? 'item');
                
                if (!$uploadResult['success']) {
                    $errors['item_image'][] = $uploadResult['error'];
                } else {
                    $tempFilename = $uploadResult['filename'];
                    $isTempImage = true;
                    $hasNewImage = true;
                }
            } elseif (strpos($pasteData, 'data:image') === 0 || (strlen($pasteData) > 100 && base64_decode($pasteData, true) !== false)) {
                // Base64 data - upload to temp
                $uploadResult = $this->fileUploadService->uploadFromBase64ToTemp($pasteData, $data['name'] ?? 'item');
                
                if (!$uploadResult['success']) {
                    $errors['item_image'][] = $uploadResult['error'];
                } else {
                    $tempFilename = $uploadResult['filename'];
                    $isTempImage = true;
                    $hasNewImage = true;
                }
            }
        }
        // If no new image uploaded, keep existing image (hasNewImage stays false)

        if ($this->itemValidator->hasErrors($errors)) {
            // Keep temp files for form persistence - store temp filename in session
            
            // Get pageno and search term from request to preserve them across redirects
            $pageno = (int) $this->request->input('pageno', 1);
            $searchTerm = trim($this->request->input('search', ''));
            
            // Store form data and errors in session for redirect
            \App\Services\SessionManager::set('item_edit_errors', $errors);
            \App\Services\SessionManager::set('item_edit_form_data', [
                'name' => $data['name'] ?? $item['name'],
                'price' => $data['price'] ?? $item['price'],
                'quantity' => $data['quantity'] ?? $item['quantity'],
                'unlimited' => $data['unlimited'] ?? $item['unlimited'],
                'link' => $data['link'] ?? $item['link'],
                'notes' => $data['notes'] ?? $item['notes'],
                'priority' => $data['priority'] ?? $item['priority'],
                'temp_filename' => $tempFilename, // Store temp filename if new image
                'is_temp' => $isTempImage,
                'has_new_image' => $hasNewImage,
                'old_image' => $oldImage, // Keep reference to old image
                'pageno' => $pageno, // Store pageno to preserve it
                'search' => $searchTerm // Store search term to preserve it
            ]);
            
            // Redirect to GET edit route (POST-Redirect-GET pattern) with pageno and search parameters
            $redirectUrl = "/wishlists/{$wishlistId}/item/{$itemId}/edit";
            $queryParams = [];
            if ($pageno > 1) {
                $queryParams[] = "pageno={$pageno}";
            }
            if (!empty($searchTerm)) {
                $queryParams[] = "search=" . urlencode($searchTerm);
            }
            if (!empty($queryParams)) {
                $redirectUrl .= "?" . implode("&", $queryParams);
            }
            return $this->redirect($redirectUrl);
        }

        // Validation passed - handle image: move temp to final if new image, or keep existing
        $filename = $oldImage; // Default to old image
        $imageChanged = false;
        
        if ($hasNewImage && $isTempImage && !empty($tempFilename)) {
            // Move temp file to final destination
            $moveResult = $this->fileUploadService->moveTempToFinal($tempFilename, $wishlistId, $data['name'] ?? 'item');
            if (!$moveResult['success']) {
                // If move fails, delete temp file and return error
                $this->fileUploadService->deleteTempImage($tempFilename);
                \App\Services\SessionManager::set('item_edit_errors', ['general' => ['Failed to save image. Please try again.']]);
                \App\Services\SessionManager::set('item_edit_form_data', [
                    'name' => $data['name'] ?? $item['name'],
                    'price' => $data['price'] ?? $item['price'],
                    'quantity' => $data['quantity'] ?? $item['quantity'],
                    'unlimited' => $data['unlimited'] ?? $item['unlimited'],
                    'link' => $data['link'] ?? $item['link'],
                    'notes' => $data['notes'] ?? $item['notes'],
                    'priority' => $data['priority'] ?? $item['priority'],
                    'old_image' => $oldImage
                ]);
                return $this->redirect("/wishlists/{$wishlistId}/item/{$itemId}/edit");
            }
            $filename = $moveResult['filename'];
            $imageChanged = true;
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
            // Determine the original copy_id - if item is already a copy, use its copy_id, otherwise use its id
            $originalCopyId = $item['copy_id'] ?: $itemId;
            if ($originalCopyId) {
                $this->wishlistService->updateCopiedItems($originalCopyId, $itemData, $wishlistId, $itemId, $this->fileUploadService);
            }
            
            // Delete old image if it was changed (only after successful database update)
            if ($imageChanged && $oldImage !== $filename) {
                $this->fileUploadService->deleteItemImage($wishlistId, $oldImage);
            }
            
            // Clear session data on success
            \App\Services\SessionManager::remove('item_edit_form_data');
            \App\Services\SessionManager::remove('item_edit_errors');
            
            // Get pageno and search term from request to preserve them in redirect
            $pageno = (int) $this->request->input('pageno', 1);
            $searchTerm = trim($this->request->input('search', ''));
            
            // Build redirect URL with query parameters
            $redirectUrl = "/wishlists/{$wishlistId}";
            $queryParams = [];
            if ($pageno > 1) {
                $queryParams[] = "pageno={$pageno}";
            }
            if (!empty($searchTerm)) {
                $queryParams[] = "search=" . urlencode($searchTerm);
            }
            if (!empty($queryParams)) {
                $redirectUrl .= "?" . implode("&", $queryParams);
            }
            
            return $this->redirect($redirectUrl)->withSuccess('Item updated successfully!');
        }

        // Database operation failed - clean up the new image file we created
        if ($imageChanged && !empty($filename) && $filename !== $oldImage) {
            // Delete the new file we just created (it was moved from temp to final)
            $this->fileUploadService->deleteItemImage($wishlistId, $filename);
        }

        // Get pageno from request to preserve it
        $pageno = (int) $this->request->input('pageno', 1);
        
        // Store form data and error in session for redirect
        \App\Services\SessionManager::set('item_edit_errors', [
            'general' => ['Unable to update item. Please try again.']
        ]);
        \App\Services\SessionManager::set('item_edit_form_data', [
            'name' => $data['name'] ?? $item['name'],
            'price' => $data['price'] ?? $item['price'],
            'quantity' => $data['quantity'] ?? $item['quantity'],
            'unlimited' => $data['unlimited'] ?? $item['unlimited'],
            'link' => $data['link'] ?? $item['link'],
            'notes' => $data['notes'] ?? $item['notes'],
            'priority' => $data['priority'] ?? $item['priority'],
            'old_image' => $oldImage,
            'pageno' => $pageno // Store pageno to preserve it
        ]);

        // Redirect to GET edit route (POST-Redirect-GET pattern) with pageno parameter
        $redirectUrl = "/wishlists/{$wishlistId}/item/{$itemId}/edit";
        if ($pageno > 1) {
            $redirectUrl .= "?pageno={$pageno}";
        }
        return $this->redirect($redirectUrl);
    }

    public function delete(string|int $wishlistId, string|int $itemId): Response
    {
        
        $user = $this->auth();
        
        $wishlistId = $this->validateId($wishlistId);
        if ($wishlistId instanceof Response) {
            return $wishlistId;
        }
        
        $itemId = $this->validateId($itemId);
        if ($itemId instanceof Response) {
            return $itemId;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        $item = $this->wishlistService->getItem($wishlistId, $itemId);
        
        if (!$item) {
            return $this->redirect("/wishlists/{$wishlistId}")->withError('Item not found.');
        }

        $deleteAll = $this->request->input('deleteAll', '') === 'yes';
        $pageno = $this->request->input('pageno', 1);
        $copyId = $item['copy_id'] ?? null;
        $imageName = $item['image'] ?? '';

        if ($deleteAll && !empty($copyId)) {
            // Delete from all wishlists
            // Get all items with this copy_id before deletion to handle images
            $allItems = Item::findByCopyIdExcludingItem($copyId, $itemId);
            $allItems[] = $item; // Include current item for image deletion check
            
            // Delete all items with this copy_id
            if ($this->itemCopyService->deleteItemFromAllWishlists($itemId)) {
                // Delete images only if they're not used by other items
                // Since we're deleting all items with this copy_id, we need to check if the image
                // is used by items with different copy_ids (after deletion)
                foreach ($allItems as $itemToDelete) {
                    if (!empty($itemToDelete['image'])) {
                        // Check if this image is used by any other items (with different copy_id)
                        // After deletion, check if image exists elsewhere
                        $imageUsed = Item::isImageUsedByOtherItems(null, $itemToDelete['image'], 0);
                        if (!$imageUsed) {
                            $this->fileUploadService->deleteItemImage($itemToDelete['wishlist_id'], $itemToDelete['image']);
                        }
                    }
                }
                
                return $this->redirect("/wishlists/{$wishlistId}?pageno={$pageno}")->withSuccess('Item deleted from all wishlists successfully!');
            }
        } else {
            // Delete from this wishlist only
            // Only delete image if it's not used by other items with the same copy_id
            $imageUsed = false;
            if (!empty($imageName)) {
                if (!empty($copyId)) {
                    // Check if image is used by other items with same copy_id
                    $imageUsed = Item::isImageUsedByOtherItems($copyId, $imageName, $itemId);
                } else {
                    // Check if image is used by any other items
                    $imageUsed = Item::isImageUsedByOtherItems(null, $imageName, $itemId);
                }
                
                // Only delete image if not used elsewhere
                if (!$imageUsed) {
                    $this->fileUploadService->deleteItemImage($wishlistId, $imageName);
                }
            }

            if ($this->itemCopyService->deleteItemFromWishlist($itemId)) {
                return $this->redirect("/wishlists/{$wishlistId}?pageno={$pageno}")->withSuccess('Item deleted successfully!');
            }
        }

        return $this->redirect("/wishlists/{$wishlistId}")->withError('Unable to delete item. Please try again.');
    }

    public function purchase(string|int $wishlistId, string|int $itemId): Response
    {
        
        $user = $this->auth();
        
        $wishlistId = $this->validateId($wishlistId);
        if ($wishlistId instanceof Response) {
            return $wishlistId;
        }
        
        $itemId = $this->validateId($itemId);
        if ($itemId instanceof Response) {
            return $itemId;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $wishlistId);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        $item = $this->wishlistService->getItem($wishlistId, $itemId);
        
        if (!$item) {
            return $this->redirect("/wishlists/{$wishlistId}")->withError('Item not found.');
        }

        $quantity = (int)$this->request->input('quantity', 1);
        
        if ($this->wishlistService->purchaseItem($wishlistId, $itemId, $quantity)) {
            return $this->redirect("/wishlists/{$wishlistId}")->withSuccess('Item marked as purchased!');
        }

        return $this->redirect("/wishlists/{$wishlistId}")->withError('Unable to mark item as purchased.');
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
