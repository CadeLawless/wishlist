<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\WishlistService;
use App\Validation\WishlistRequestValidator;
use App\Services\PaginationService;
use App\Services\ItemCopyService;
use App\Services\FilterService;
use App\Services\SessionManager;
use App\Services\PopupManager;
use App\Services\HtmlGenerationService;
use App\Services\WishlistRenderService;

class WishlistController extends Controller
{
    public function __construct(
        private WishlistService $wishlistService = new WishlistService(),
        private WishlistRequestValidator $wishlistValidator = new WishlistRequestValidator(),
        private PaginationService $paginationService = new PaginationService(),
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
     * Display user's wishlist dashboard
     * 
     * Shows all wishlists belonging to the authenticated user.
     * Requires authentication.
     * 
     * @return Response Rendered wishlist index view
     */
    public function index(): Response
    {
        
        $user = $this->auth();
        
        // Get pagination number
        $pageno = (int) $this->request->get('pageno', 1);
        
        // Get all wishlists for the user
        $allWishlists = $this->wishlistService->getUserWishlists($user['username']);
        
        // Apply pagination to get only 12 wishlists per page
        $paginatedWishlists = $this->paginationService->paginate($allWishlists, $pageno);
        $totalPages = $this->paginationService->getTotalPages();
        
        $data = [
            'user' => $user,
            'wishlists' => $paginatedWishlists,
            'all_wishlists' => $allWishlists,
            'pageno' => $pageno,
            'total_pages' => $totalPages,
            'base_url' => '/wishlists',
            'customStyles' => 
                '.paginate-container {
                    margin: 0 0 2rem;
                }
                .paginate-container.bottom {
                    margin: 0.5rem 0;
                }'
        ];

        return $this->view('wishlist/index', $data);
    }


    public function show(string|int $id): Response
    {
        
        $user = $this->auth();
        
        // Validate and convert ID
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('/wishlists')->withError('Wishlist not found.');
        }

        // Get pagination number
        $pageno = (int) $this->request->get('pageno', 1);
        
        // Get other wishlists for copy functionality
        $otherWishlists = $this->wishlistService->getOtherWishlists($user['username'], $id);
        
        // Add item counts to each wishlist for copy from dropdown
        foreach ($otherWishlists as &$otherWishlist) {
            $otherWishlist['item_count'] = \App\Models\Item::countItems($otherWishlist['id'], $user['username']);
        }
        unset($otherWishlist); // Break reference
        
        // Get sorting/filter preferences from session
        SessionManager::setWishlistContext($id, $pageno);

        $sortPreferences = SessionManager::getWisherSortPreferences();
        $sortPriority = $sortPreferences['sort_priority'];
        $sortPrice = $sortPreferences['sort_price'];
        
        $filters = [
            'sort_priority' => $sortPriority,
            'sort_price' => $sortPrice
        ];
        
        // Convert session filters to WishlistService format using FilterService
        $serviceFilters = FilterService::convertWisherSessionFilters($sortPriority, $sortPrice);
        
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
        
        $user = $this->auth();
        $data = $this->request->input();
        $errors = $this->wishlistValidator->validateWishlist($data);

        if ($this->wishlistValidator->hasErrors($errors)) {
            return $this->view('wishlist/create', [
                'user' => $user,
                'wishlist_type' => $data['wishlist_type'] ?? '',
                'wishlist_name' => $data['wishlist_name'] ?? '',
                'theme_background_id' => $data['theme_background_id'] ?? '',
                'theme_gift_wrap_id' => $data['theme_gift_wrap_id'] ?? '',
                'error_msg' => $this->wishlistValidator->formatErrorsForDisplay($errors)
            ]);
        }

        $wishlist = $this->wishlistService->createWishlist($user['username'], $data);
        
        if ($wishlist) {
            return $this->redirect("/wishlists/{$wishlist['id']}")->withSuccess('Wishlist created successfully!');
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

    public function edit(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        $data = [
            'user' => $user,
            'wishlist' => $wishlist,
            'wishlist_name' => $this->request->input('wishlist_name', $wishlist['wishlist_name'])
        ];

        return $this->view('wishlist/edit', $data);
    }

    public function update(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        $data = $this->request->input();
        $errors = $this->wishlistValidator->validateWishlist($data);

        if ($this->wishlistValidator->hasErrors($errors)) {
            return $this->view('wishlist/edit', [
                'user' => $user,
                'wishlist' => $wishlist,
                'wishlist_name' => $data['wishlist_name'] ?? '',
                'error_msg' => $this->wishlistValidator->formatErrorsForDisplay($errors)
            ]);
        }

        if ($this->wishlistService->updateWishlistName($id, $data['wishlist_name'])) {
            return $this->redirect("/wishlists/{$id}")->withSuccess('Wishlist updated successfully!');
        }

        return $this->view('wishlist/edit', [
            'user' => $user,
            'wishlist' => $wishlist,
            'wishlist_name' => $data['wishlist_name'] ?? '',
            'error_msg' => '<div class="submit-error"><strong>Update failed:</strong><ul><li>Unable to update wishlist. Please try again.</li></ul></div>'
        ]);
    }

    public function delete(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->deleteWishlistAndItems($id)) {
            // Update duplicate flags for other wishlists with the same name
            $this->wishlistService->updateDuplicateFlags($user['username'], $wishlist['wishlist_name']);
            
            return $this->redirect('s')->withSuccess('Wishlist deleted successfully!');
        }

        return $this->redirect('')->withError('Unable to delete wishlist. Please try again.');
    }

    public function toggleVisibility(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistVisibility($id)) {
            $message = $wishlist['public'] ? 'Wishlist is now hidden.' : 'Wishlist is now public.';
            return $this->redirect("/wishlists/{$id}")->withSuccess($message);
        }

        return $this->redirect("/wishlists/{$id}")->withError('Unable to update wishlist visibility.');
    }

    public function toggleComplete(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistComplete($id)) {
            $message = $wishlist['complete'] ? 'Wishlist has been reactivated.' : 'Wishlist has been marked as complete.';
            return $this->redirect("/wishlists/{$id}")->withSuccess($message);
        }

        return $this->redirect("/wishlists/{$id}")->withError('Unable to update wishlist status.');
    }

    public function rename(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('s')->withError('Wishlist not found.');
        }

        $name = $this->request->input('wishlist_name');
        $errors = $this->wishlistValidator->validateWishlistName($name);

        if ($this->wishlistValidator->hasErrors($errors)) {
            return $this->redirect("/wishlists/{$id}")->withFlash('rename_error', $this->wishlistValidator->formatErrorsForDisplay($errors));
        }

        if ($this->wishlistService->updateWishlistName($id, $name)) {
            return $this->redirect("/wishlists/{$id}")->withSuccess('Wishlist renamed successfully!');
        }

        return $this->redirect("/wishlists/{$id}")->withFlash('rename_error', 'Unable to rename wishlist. Please try again.');
    }

    public function updateTheme(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('s')->withError('Wishlist not found.');
        }

        $backgroundId = (int) $this->request->input('theme_background_id');
        $giftWrapId = (int) $this->request->input('theme_gift_wrap_id');

        if ($this->wishlistService->updateWishlistTheme($id, $backgroundId, $giftWrapId)) {
            return $this->redirect("/wishlists/{$id}")->withSuccess('Theme updated successfully!');
        }

        return $this->redirect("/wishlists/{$id}")->withError('Unable to update theme. Please try again.');
    }

    public function copyFrom(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('s')->withError('Wishlist not found.');
        }

        $fromWishlistId = (int) $this->request->input('other_wishlist_copy_from');
        $itemIds = $this->request->input('item_ids', []);

        if (empty($itemIds)) {
            // Pass error and selected wishlist ID via URL parameter to preserve state
            return $this->redirect("/wishlists/{$id}?copy_from_error=1&copy_from_wishlist_id={$fromWishlistId}")->withError('Please select at least one item to copy.');
        }

        $copiedCount = $this->itemCopyService->copyItems($fromWishlistId, $id, $itemIds);

        if ($copiedCount > 0) {
            return $this->redirect("/wishlists/{$id}")->withSuccess("Successfully copied {$copiedCount} item(s) to this wishlist!");
        }

        return $this->redirect("/wishlists/{$id}")->withError('Unable to copy items. Please try again.');
    }

    public function copyTo(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('s')->withError('Wishlist not found.');
        }

        $toWishlistId = (int) $this->request->input('other_wishlist_copy_to');
        $itemIds = $this->request->input('item_ids', []);

        if (empty($itemIds)) {
            // Pass error and selected wishlist ID via URL parameter to preserve state
            return $this->redirect("/wishlists/{$id}?copy_to_error=1&copy_to_wishlist_id={$toWishlistId}")->withError('Please select at least one item to copy.');
        }

        $copiedCount = $this->itemCopyService->copyItems($id, $toWishlistId, $itemIds);

        if ($copiedCount > 0) {
            return $this->redirect("/wishlists/{$id}")->withSuccess("Successfully copied {$copiedCount} item(s) to the other wishlist!");
        }

        return $this->redirect("/wishlists/{$id}")->withError('Unable to copy items. Please try again.');
    }

    public function hide(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('s')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistVisibility($id)) {
            return $this->redirect("/wishlists/{$id}")->withSuccess('Wishlist hidden successfully.');
        }

        return $this->redirect("/wishlists/{$id}")->withError('Unable to hide wishlist. Please try again.');
    }

    public function showPublic(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('s')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistVisibility($id)) {
            return $this->redirect("/wishlists/{$id}")->withSuccess('Wishlist made public successfully.');
        }

        return $this->redirect("/wishlists/{$id}")->withError('Unable to make wishlist public. Please try again.');
    }

    public function complete(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('s')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistComplete($id)) {
            return $this->redirect("/wishlists/{$id}")->withSuccess('Wishlist marked as complete.');
        }

        return $this->redirect("/wishlists/{$id}")->withError('Unable to mark wishlist as complete. Please try again.');
    }

    public function reactivate(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return $this->redirect('s')->withError('Wishlist not found.');
        }

        if ($this->wishlistService->toggleWishlistComplete($id)) {
            return $this->redirect("/wishlists/{$id}")->withSuccess('Wishlist reactivated successfully.');
        }

        return $this->redirect("/wishlists/{$id}")->withError('Unable to reactivate wishlist. Please try again.');
    }

    public function paginateItems(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
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
        
        // Apply session filters for pagination using FilterService
        $sortPreferences = SessionManager::getWisherSortPreferences();
        $sortPriority = $sortPreferences['sort_priority'];
        $sortPrice = $sortPreferences['sort_price'];
        
        $serviceFilters = FilterService::convertWisherSessionFilters($sortPriority, $sortPrice);
        
        $items = $this->wishlistService->getWishlistItems($id, $serviceFilters);
        $paginatedItems = $this->paginationService->paginate($items, $page);
        $totalPages = $this->paginationService->getTotalPages($items);
        $totalRows = count($items);

        // Generate HTML for items only (no pagination controls)
        $itemsHtml = HtmlGenerationService::generateItemsHtml($paginatedItems, $id, $page);
        
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

    public function filterItems(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $wishlist = $this->wishlistService->getWishlistById($user['username'], $id);
        
        if (!$wishlist) {
            return new Response(content: 'Wishlist not found', status: 404);
        }

        // Get filter parameters
        $sortPriority = $this->request->input('sort_priority', '');
        $sortPrice = $this->request->input('sort_price', '');
        
        // Validate filter options using FilterService
        if (!FilterService::validateWisherFilters($sortPriority, $sortPrice)) {
            return new Response(content: HtmlGenerationService::generateFilterErrorHtml(), status: 400);
        }
        
        // Update session with filter preferences using SessionManager
        SessionManager::storeWisherSortPreferences($sortPriority, $sortPrice);

        // Convert filter parameters to WishlistService format using FilterService
        $filters = FilterService::convertWisherSessionFilters($sortPriority, $sortPrice);

        // Get filtered items (reset to page 1 after filtering)
        $items = $this->wishlistService->getWishlistItems($id, $filters);
        $paginatedItems = $this->paginationService->paginate($items, 1);
        $totalPages = $this->paginationService->getTotalPages($items);
        $totalRows = count($items);
        
        $html = HtmlGenerationService::generateItemsHtml($paginatedItems, $id, 1);
        
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

    public function getOtherWishlistItems(string|int $id): Response
    {
        
        $user = $this->auth();
        
        $id = $this->validateId($id);
        if ($id instanceof Response) {
            return $id;
        }
        
        $otherWishlistId = (int) $this->request->input('wishlist_id');
        $copyFrom = $this->request->input('copy_from') === 'Yes';

        // Determine which wishlist to get items from based on copy direction
        if ($copyFrom) {
            // Copy FROM: Get items from the selected wishlist (otherWishlistId)
            $items = $this->itemCopyService->getWishlistItems($otherWishlistId);
            $sourceWishlistId = $otherWishlistId;
            $targetWishlistId = $id;
        } else {
            // Copy TO: Get items from the current wishlist (id)
            $items = $this->itemCopyService->getWishlistItems($id);
            $sourceWishlistId = $id;
            $targetWishlistId = $otherWishlistId;
        }
        
        $html = HtmlGenerationService::generateItemCheckboxes($items, $sourceWishlistId, $targetWishlistId, $copyFrom, $this->wishlistService);

        return new Response(content: $html);
    }

    public function paginateWishlists(): Response
    {
        
        $user = $this->auth();
        
        $page = (int) $this->request->input('new_page', 1);
        
        // Get all wishlists for the user
        $allWishlists = $this->wishlistService->getUserWishlists($user['username']);
        $paginatedWishlists = $this->paginationService->paginate($allWishlists, $page);
        $totalPages = $this->paginationService->getTotalPages();
        $totalRows = count($allWishlists);
        
        // Generate HTML for wishlist grid items only (no pagination controls)
        $wishlistsHtml = WishlistRenderService::generateWishlistsHtml($paginatedWishlists);
        
        // Calculate pagination info
        $itemsPerPage = 12;
        $paginationInfoStart = (($page - 1) * $itemsPerPage) + 1;
        $paginationInfoEnd = min($page * $itemsPerPage, $totalRows);
        $paginationInfo = "Showing {$paginationInfoStart}-{$paginationInfoEnd} of {$totalRows} wishlists";
        
        // Clear any output buffering first
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers and output JSON directly
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $jsonData = [
            'status' => 'success',
            'message' => 'Wishlists loaded successfully',
            'html' => $wishlistsHtml,
            'current' => $page,
            'total' => $totalPages,
            'paginationInfo' => $paginationInfo
        ];
        
        echo json_encode($jsonData);
        flush();
        exit;
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
