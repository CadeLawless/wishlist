<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\AuthService;
use App\Services\WishlistService;
use App\Services\ValidationService;
use App\Services\FileUploadService;
use App\Services\PaginationService;
use App\Services\ItemCopyService;
use App\Services\PopupManager;

class WishlistController extends Controller
{
    private AuthService $authService;
    private WishlistService $wishlistService;
    private ValidationService $validationService;
    private FileUploadService $fileUploadService;
    private PaginationService $paginationService;
    private ItemCopyService $itemCopyService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->wishlistService = new WishlistService();
        $this->validationService = new ValidationService();
        $this->fileUploadService = new FileUploadService();
        $this->paginationService = new PaginationService();
        $this->itemCopyService = new ItemCopyService();
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
        $pageno = (int) $this->request->get('pageno', 1);
        
        // Get other wishlists for copy functionality
        $otherWishlists = $this->wishlistService->getOtherWishlists($user['username'], $id);
        
        // Get sorting/filter preferences from session
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $_SESSION['wisher_wishlist_id'] = $id;
        $_SESSION['home'] = "/wishlist/{$id}?pageno={$pageno}#paginate-top";
        $_SESSION['type'] = 'wisher';

        $sortPriority = $_SESSION['wisher_sort_priority'] ?? '';
        $sortPrice = $_SESSION['wisher_sort_price'] ?? '';
        
        $filters = [
            'sort_priority' => $sortPriority,
            'sort_price' => $sortPrice
        ];
        
        // Convert session filters to WishlistService format
        $serviceFilters = [];
        if ($sortPriority === '1') {
            $serviceFilters['sort_by'] = 'priority';
            $serviceFilters['sort_order'] = 'ASC';
        } elseif ($sortPriority === '2') {
            $serviceFilters['sort_by'] = 'priority';
            $serviceFilters['sort_order'] = 'DESC';
        } elseif ($sortPrice === '1') {
            $serviceFilters['sort_by'] = 'price';
            $serviceFilters['sort_order'] = 'ASC';
        } elseif ($sortPrice === '2') {
            $serviceFilters['sort_by'] = 'price';
            $serviceFilters['sort_order'] = 'DESC';
        } else {
            $serviceFilters['sort_by'] = 'date_added';
            $serviceFilters['sort_order'] = 'DESC';
        }
        
        // Get ALL items first (for total count and filtering)
        $allItems = $this->wishlistService->getWishlistItems($id, $serviceFilters);
        
        // Apply pagination to get only 12 items per page
        $paginatedItems = $this->paginationService->paginate($allItems, $pageno);
        $totalPages = $this->paginationService->getTotalPages($allItems);
        
        $data = [
            'user' => $user,
            'wishlist' => $wishlist,
            'items' => $paginatedItems,
            'all_items' => $allItems, // For total count display
            'other_wishlists' => $otherWishlists,
            'pageno' => $pageno,
            'total_pages' => $totalPages,
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
            'title' => 'Create a Wish List',
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
            return $this->redirect("/wishlist/{$wishlist['id']}")->withSuccess('Wishlist created successfully!');
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
            'wishlist_name' => $this->request->input('wishlist_name', $wishlist['wishlist_name'])
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

        if ($this->wishlistService->deleteWishlistAndItems($id)) {
            // Update duplicate flags for other wishlists with the same name
            $this->wishlistService->updateDuplicateFlags($user['username'], $wishlist['wishlist_name']);
            
            return $this->redirect('/wishlist/wishlists')->withSuccess('Wishlist deleted successfully!');
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

        if ($this->wishlistService->toggleWishlistVisibility($id)) {
            $message = $wishlist['public'] ? 'Wishlist is now hidden.' : 'Wishlist is now public.';
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

        if ($this->wishlistService->toggleWishlistComplete($id)) {
            $message = $wishlist['complete'] ? 'Wishlist has been reactivated.' : 'Wishlist has been marked as complete.';
            return $this->redirect("/wishlist/{$id}")->withSuccess($message);
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to update wishlist status.');
    }

    public function rename(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        $name = $this->request->input('wishlist_name');
        $errors = $this->validationService->validateWishlistName($name);

        if ($this->validationService->hasErrors($errors)) {
            return $this->redirect("/wishlist/{$id}")->withError($this->validationService->formatErrorsForDisplay($errors));
        }

        if ($this->wishlistService->updateWishlistName($id, $name)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist renamed successfully!');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to rename wishlist. Please try again.');
    }

    public function updateTheme(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        $backgroundId = (int) $this->request->input('theme_background_id');
        $giftWrapId = (int) $this->request->input('theme_gift_wrap_id');

        if ($this->wishlistService->updateWishlistTheme($id, $backgroundId, $giftWrapId)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Theme updated successfully!');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to update theme. Please try again.');
    }

    public function copyFrom(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        $fromWishlistId = (int) $this->request->input('other_wishlist_copy_from');
        $itemIds = $this->request->input('item_ids', []);

        if (empty($itemIds)) {
            return $this->redirect("/wishlist/{$id}")->withError('Please select at least one item to copy.');
        }

        $copiedCount = $this->itemCopyService->copyItems($fromWishlistId, $id, $itemIds);

        if ($copiedCount > 0) {
            return $this->redirect("/wishlist/{$id}")->withSuccess("Successfully copied {$copiedCount} item(s) to this wishlist!");
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to copy items. Please try again.');
    }

    public function copyTo(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        $toWishlistId = (int) $this->request->input('other_wishlist_copy_to');
        $itemIds = $this->request->input('item_ids', []);

        if (empty($itemIds)) {
            return $this->redirect("/wishlist/{$id}")->withError('Please select at least one item to copy.');
        }

        $copiedCount = $this->itemCopyService->copyItems($id, $toWishlistId, $itemIds);

        if ($copiedCount > 0) {
            return $this->redirect("/wishlist/{$id}")->withSuccess("Successfully copied {$copiedCount} item(s) to the other wishlist!");
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to copy items. Please try again.');
    }

    public function hide(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistVisibility($id)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist is now hidden.');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to hide wishlist. Please try again.');
    }

    public function showPublic(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistVisibility($id)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist is now public.');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to make wishlist public. Please try again.');
    }

    public function complete(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistComplete($id)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist has been marked as complete.');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to mark wishlist as complete. Please try again.');
    }

    public function reactivate(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlist/wishlists')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistComplete($id)) {
            return $this->redirect("/wishlist/{$id}")->withSuccess('Wishlist has been reactivated.');
        }

        return $this->redirect("/wishlist/{$id}")->withError('Unable to reactivate wishlist. Please try again.');
    }

    public function paginateItems(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Wishlist not found',
                'html' => '',
                'current' => 1,
                'total' => 1,
                'paginationInfo' => ''
            ]);
            exit;
        }

        $page = (int) $this->request->input('new_page', 1);
        
        // Apply session filters for pagination
        $sortPriority = $_SESSION['wisher_sort_priority'] ?? '';
        $sortPrice = $_SESSION['wisher_sort_price'] ?? '';
        
        $serviceFilters = [];
        if ($sortPriority === '1') {
            $serviceFilters['sort_by'] = 'priority';
            $serviceFilters['sort_order'] = 'ASC';
        } elseif ($sortPriority === '2') {
            $serviceFilters['sort_by'] = 'priority';
            $serviceFilters['sort_order'] = 'DESC';
        } elseif ($sortPrice === '1') {
            $serviceFilters['sort_by'] = 'price';
            $serviceFilters['sort_order'] = 'ASC';
        } elseif ($sortPrice === '2') {
            $serviceFilters['sort_by'] = 'price';
            $serviceFilters['sort_order'] = 'DESC';
        } else {
            $serviceFilters['sort_by'] = 'date_added';
            $serviceFilters['sort_order'] = 'DESC';
        }
        
        $items = $this->wishlistService->getWishlistItems($id, $serviceFilters);
        $paginatedItems = $this->paginationService->paginate($items, $page);
        $totalPages = $this->paginationService->getTotalPages($items);
        $totalRows = count($items);

        // Generate HTML for items only (no pagination controls)
        $itemsHtml = $this->generateItemsHtml($paginatedItems, $id, $page);
        
        // Calculate pagination info
        $itemsPerPage = 12;
        $paginationInfoStart = (($page - 1) * $itemsPerPage) + 1;
        $paginationInfoEnd = min($page * $itemsPerPage, $totalRows);
        $paginationInfo = "Showing {$paginationInfoStart}-{$paginationInfoEnd} of {$totalRows} items";

        // Clear any output buffering first
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers and output JSON directly
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $jsonData = [
            'status' => 'success',
            'message' => 'Items loaded successfully',
            'html' => $itemsHtml,
            'current' => $page,
            'total' => $totalPages,
            'paginationInfo' => $paginationInfo
        ];
        
        echo json_encode($jsonData);
        flush();
        exit;
    }

    public function filterItems(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return new Response('Wishlist not found', 404);
        }

        // Get filter parameters
        $sortPriority = $this->request->input('sort_priority', '');
        $sortPrice = $this->request->input('sort_price', '');
        
        // Validate filter options
        $validOptions = ['', '1', '2'];
        if (!in_array($sortPriority, $validOptions) || !in_array($sortPrice, $validOptions)) {
            return new Response('<strong>Invalid filter. Please try again.</strong>', 400);
        }
        
        // Update session with filter preferences
        if (!isset($_SESSION)) {
            session_start();
        }
        
        $_SESSION['wisher_sort_priority'] = $sortPriority;
        $_SESSION['wisher_sort_price'] = $sortPrice;

        // Convert filter parameters to WishlistService format
        $filters = [];
        
        // Apply sorting based on original sort.php logic
        if ($sortPriority === '1') {
            $filters['sort_by'] = 'priority';
            $filters['sort_order'] = 'ASC';
        } elseif ($sortPriority === '2') {
            $filters['sort_by'] = 'priority';
            $filters['sort_order'] = 'DESC';
        } elseif ($sortPrice === '1') {
            $filters['sort_by'] = 'price';
            $filters['sort_order'] = 'ASC';
        } elseif ($sortPrice === '2') {
            $filters['sort_by'] = 'price';
            $filters['sort_order'] = 'DESC';
        } else {
            $filters['sort_by'] = 'date_added';
            $filters['sort_order'] = 'DESC';
        }

        // Get filtered items (reset to page 1 after filtering)
        $items = $this->wishlistService->getWishlistItems($id, $filters);
        $paginatedItems = $this->paginationService->paginate($items, 1);
        $totalPages = $this->paginationService->getTotalPages($items);
        $totalRows = count($items);
        
        $html = $this->generateItemsHtml($paginatedItems, $id, 1, $totalPages);
        
        // Calculate pagination info
        $itemsPerPage = 12;
        $paginationInfoStart = 1;
        $paginationInfoEnd = min($itemsPerPage, $totalRows);
        $paginationInfo = "Showing {$paginationInfoStart}-{$paginationInfoEnd} of {$totalRows} items";

        // Clear any output buffering first
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers and output JSON directly (like pagination does)
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $jsonData = [
            'status' => 'success',
            'message' => 'Filter applied successfully',
            'html' => $html,
            'current' => 1,
            'total' => $totalPages,
            'paginationInfo' => $paginationInfo
        ];
        
        echo json_encode($jsonData);
        flush();
        exit;
    }

    public function getOtherWishlistItems(int $id): Response
    {
        $this->requireAuth();
        
        $user = $this->auth();
        $otherWishlistId = (int) $this->request->input('wishlist_id');
        $copyFrom = $this->request->input('copy_from') === 'Yes';

        $items = $this->itemCopyService->getWishlistItems($otherWishlistId);
        $html = $this->generateItemCheckboxes($items, $otherWishlistId, $id, $copyFrom);

        return new Response($html);
    }

    private function generateItemsHtml(array $items, int $wishlistId, int $page): string
    {
        $html = '';
        
        foreach ($items as $item) {
            $html .= $this->generateItemHtml($item, $wishlistId, $page);
        }
        
        return $html;
    }

    public function generateItemHtml(array $item, int $wishlistId, int $page): string
    {
        return \App\Services\ItemRenderService::renderItem($item, $wishlistId, $page);
    }

    private function generateItemCheckboxes(array $items, int $sourceWishlistId, int $currentWishlistId, bool $copyFrom): string
    {
        $html = '';
        
        // Add "All Items" checkbox
        $html .= "
        <div class='checkboxes-container'>
            <div class='select-item-container select-all'>
                <div class='option-title'>All Items</div>
                <div class='option-checkbox'>
                    <input type='checkbox' name='copy_" . ($copyFrom ? "from" : "to") . "_select_all' class='check-all' />
                </div>
            </div>";
        
        $copyCounter = 0;
        
        foreach ($items as $item) {
            $itemName = htmlspecialchars($item['name']);
            $itemId = $item['id'];
            $itemCopyId = $item['copy_id'];
            $itemImage = $item['image'];
            
            // Check if item already exists in the target wishlist (by copy_id)
            $alreadyInList = false;
            if ($itemCopyId) {
                if ($copyFrom) {
                    // For copy from: check if this copy_id exists in current wishlist
                    $stmt = \App\Core\Database::query(
                        "SELECT COUNT(*) as count FROM items WHERE copy_id = ? AND wishlist_id = ?", 
                        [$itemCopyId, $currentWishlistId]
                    );
                    $result = $stmt->get_result()->fetch_assoc();
                    $alreadyInList = $result['count'] > 0;
                } else {
                    // For copy to: check if this copy_id exists in the target wishlist
                    $stmt = \App\Core\Database::query(
                        "SELECT COUNT(*) as count FROM items WHERE copy_id = ? AND wishlist_id = ?", 
                        [$itemCopyId, $sourceWishlistId]
                    );
                    $result = $stmt->get_result()->fetch_assoc();
                    $alreadyInList = $result['count'] > 0;
                }
            }
            
            if ($alreadyInList) {
                $copyCounter++;
            }
            
            // Determine image path - use source wishlist ID for image location
            $absoluteImagePath = __DIR__ . "/../../images/item-images/{$sourceWishlistId}/" . htmlspecialchars($itemImage);
            if (!file_exists($absoluteImagePath)) {
                $imagePath = "images/site-images/default-photo.png";
            } else {
                $imagePath = "images/item-images/{$sourceWishlistId}/" . htmlspecialchars($itemImage);
            }
            
            $containerClass = $alreadyInList ? 'select-item-container already-in-list' : 'select-item-container';
            $checkboxClass = $alreadyInList ? 'already-in-list' : '';
            $disabled = $alreadyInList ? 'disabled' : '';
            $alreadyInListText = $alreadyInList ? ' (Already in list)' : '';
            
            $html .= "
            <div class='{$containerClass}'>
                <div class='option-image'>
                    <img src='{$imagePath}?t=" . time() . "' alt='wishlist item image'>
                </div>
                <div class='option-title'>{$itemName}{$alreadyInListText}</div>
                <div class='option-checkbox'>
                    <input type='checkbox' class='{$checkboxClass}' name='item_{$itemId}' {$disabled} />
                </div>
            </div>";
        }
        
        $html .= "
        </div>
        <p class='center" . ($copyCounter == count($items) ? ' hidden' : '') . "'>
            <input type='submit' class='button text' name='copy_" . ($copyFrom ? "from" : "to") . "_submit' value='Copy Items' />
        </p>";
        
        return $html;
    }

    private function generatePaginationHtml(int $currentPage, int $totalPages, int $totalItems): string
    {
        if ($totalPages <= 1) {
            return '';
        }

        $startItem = (($currentPage - 1) * 12) + 1;
        $endItem = min($currentPage * 12, $totalItems);

        // Generate pagination HTML without PHP includes (use SVG placeholders for now)
        $firstDisabled = $currentPage <= 1 ? ' disabled' : '';
        $prevDisabled = $currentPage <= 1 ? ' disabled' : '';
        $nextDisabled = $currentPage >= $totalPages ? ' disabled' : '';
        $lastDisabled = $currentPage >= $totalPages ? ' disabled' : '';

        return "
        <div class='center'>
            <div class='paginate-container'>
                <a class='paginate-arrow paginate-first{$firstDisabled}' href='#'>
                    <svg><!-- First icon --></svg>
                </a>
                <a class='paginate-arrow paginate-previous{$prevDisabled}' href='#'>
                    <svg><!-- Previous icon --></svg>
                </a>
                <div class='paginate-title'>
                    <span class='page-number'>{$currentPage}</span>/<span class='last-page'>{$totalPages}</span>
                </div>
                <a class='paginate-arrow paginate-next{$nextDisabled}' href='#'>
                    <svg><!-- Next icon --></svg>
                </a>
                <a class='paginate-arrow paginate-last{$lastDisabled}' href='#'>
                    <svg><!-- Last icon --></svg>
                </a>
            </div>
        </div>
        <div class='count-showing'>Showing {$startItem}-{$endItem} of {$totalItems} items</div>";
    }
    
    /**
     * Generate success message popup using PopupManager
     */
    private function generateSuccessPopup(string $title, string $message): string
    {
        return PopupManager::generateInfoPopup([
            'title' => $title,
            'message' => $message,
            'type' => 'standard',
            'classes' => 'active'
        ]);
    }
}
