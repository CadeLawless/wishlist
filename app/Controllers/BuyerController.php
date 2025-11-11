<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\WishlistService;
use App\Services\PaginationService;
use App\Services\HtmlGenerationService;
use App\Services\FilterService;
use App\Services\SessionManager;
use Exception;

class BuyerController extends Controller
{
    public function __construct(
        private WishlistService $wishlistService = new WishlistService(),
        private PaginationService $paginationService = new PaginationService()
    ) {
        parent::__construct();
    }

    public function show(string $secretKey): Response
    {
        $wishlist = $this->wishlistService->getWishlistBySecretKey($secretKey);
        
        if (!$wishlist) {
            return $this->view('errors/404', ['title' => '404 - Wish List Not Found'], 'error');
        }

        if ($wishlist['visibility'] !== 'Public' || $wishlist['complete'] === 'Yes') {
            return $this->view('errors/access-denied', ['title' => '403 - Access Denied'], 'error');
        }

        // Handle pagination
        $pageno = (int) $this->request->get('pageno', 1);
        
        // Process filters and sorting using FilterService
        $requestData = $this->request->get();
        $filters = FilterService::processBuyerFilters($requestData);
        
        // Get sort values for view
        $sort_priority = $requestData['sort_priority'] ?? '1';
        $sort_price = $requestData['sort_price'] ?? '';

        // Get all items first (for pagination)
        $allItems = $this->wishlistService->getWishlistItems($wishlist['id'], $filters);
        
        // Apply pagination
        $paginatedItems = $this->paginationService->paginate($allItems, $pageno);
        $totalPages = $this->paginationService->getTotalPages();
        $correctedPage = $this->paginationService->getCurrentPage();
        
        // Redirect if page number was out of range
        if ($correctedPage !== $pageno && count($allItems) > 0) {
            return $this->redirect("/buyer/{$secretKey}?pageno={$correctedPage}");
        }
        
        $stats = $this->wishlistService->getWishlistStats($wishlist['id']);
        
        $data = [
            'wishlist' => $wishlist,
            'items' => $paginatedItems,
            'all_items' => $allItems,
            'stats' => $stats,
            'filters' => $filters,
            'pageno' => $pageno,
            'total_pages' => $totalPages,
            'sort_priority' => $sort_priority,
            'sort_price' => $sort_price
        ];

        return $this->view('buyer/show', $data, 'buyer');
    }

    public function purchaseItem(string $secretKey, int $itemId): Response
    {
        $wishlist = $this->wishlistService->getWishlistBySecretKey($secretKey);
        
        if (!$wishlist) {
            return $this->redirect('/')->withError('Wishlist not found.');
        }

        if ($wishlist['visibility'] !== 'Public' || $wishlist['complete'] === 'Yes') {
            return $this->redirect('/')->withError('This wishlist is not available for viewing.');
        }

        // Load item and validate
        $item = $this->wishlistService->getItem($wishlist['id'], $itemId);
        if (!$item) {
            return $this->redirect("/buyer/{$secretKey}")->withError('Item not found.');
        }
        if ($item['unlimited'] === 'Yes') {
            return $this->redirect("/buyer/{$secretKey}")->withError('This item is unlimited and cannot be marked as purchased.');
        }

        $requestedQty = max(1, (int)$this->request->input('quantity', 1));
        $alreadyPurchased = (int)($item['quantity_purchased'] ?? 0);
        $needed = max(0, (int)$item['quantity'] - $alreadyPurchased);
        if ($needed <= 0) {
            return $this->redirect("/buyer/{$secretKey}")->withSuccess('This item is already fully purchased.');
        }
        $qtyToApply = min($requestedQty, $needed);
        
        if ($this->wishlistService->purchaseItem($wishlist['id'], $itemId, $qtyToApply)) {
            // Reload to compute remaining
            $updated = $this->wishlistService->getItem($wishlist['id'], $itemId);
            $remaining = max(0, (int)$updated['quantity'] - (int)$updated['quantity_purchased']);
            if ($remaining > 0) {
                return $this->redirect("/buyer/{$secretKey}")->withSuccess("Thanks! {$remaining} still needed.");
            }
            return $this->redirect("/buyer/{$secretKey}")->withSuccess('Item marked as fully purchased!');
        }

        return $this->redirect("/buyer/{$secretKey}")->withError('Unable to mark item as purchased.');
    }

    public function filterItems(string $secretKey): Response
    {
        $wishlist = $this->wishlistService->getWishlistBySecretKey($secretKey);
        
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

        if ($wishlist['visibility'] !== 'Public' || $wishlist['complete'] === 'Yes') {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Wishlist not available',
                'html' => '',
                'current' => 1,
                'total' => 1,
                'paginationInfo' => ''
            ]);
            exit;
        }

        // Get filter parameters
        $sortPriority = $this->request->input('sort_priority', '');
        $sortPrice = $this->request->input('sort_price', '');
        
        // Process filters and sorting using FilterService
        $requestData = $this->request->input();
        $filters = FilterService::processBuyerFilters($requestData);
        
        // Get filtered items (reset to page 1 after filtering)
        $items = $this->wishlistService->getWishlistItems($wishlist['id'], $filters);
        $paginatedItems = $this->paginationService->paginate($items, 1);
        $totalPages = $this->paginationService->getTotalPages();
        $totalRows = count($items);

        // Generate HTML for items only (no pagination controls)
        try {
            $itemsHtml = HtmlGenerationService::generateItemsHtml($paginatedItems, $wishlist['id'], 1, 'buyer');
        } catch (Exception $e) {
            $itemsHtml = '<div class="error">Error loading items</div>';
        }
        
        // Calculate pagination info
        $itemsPerPage = 12;
        $paginationInfoStart = 1;
        $paginationInfoEnd = min($itemsPerPage, $totalRows);
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
            'message' => 'Filter applied successfully',
            'html' => $itemsHtml,
            'current' => 1,
            'total' => $totalPages,
            'paginationInfo' => $paginationInfo
        ];
        
        echo json_encode($jsonData);
        exit;
    }

    public function paginateItems(string $secretKey): Response
    {
        $wishlist = $this->wishlistService->getWishlistBySecretKey($secretKey);
        
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

        if ($wishlist['visibility'] !== 'Public' || $wishlist['complete'] === 'Yes') {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'Wishlist not available',
                'html' => '',
                'current' => 1,
                'total' => 1,
                'paginationInfo' => ''
            ]);
            exit;
        }

        $page = (int) $this->request->input('new_page', 1);
        
        // Apply session filters for pagination (like wisher view)
        $sortPreferences = SessionManager::getBuyerSortPreferences();
        $sortPriority = $sortPreferences['sort_priority'];
        $sortPrice = $sortPreferences['sort_price'];
        
        // Build order clause like in show method
        $priority_order = $sortPriority ? "priority ASC, " : "";
        $price_order = $sortPrice ? "price * 1 ASC, " : "";
        $order_clause = "purchased ASC, {$priority_order}{$price_order}date_added DESC";
        
        $filters = [
            'order_clause' => $order_clause
        ];
        
        $items = $this->wishlistService->getWishlistItems($wishlist['id'], $filters);
        $paginatedItems = $this->paginationService->paginate($items, $page);
        $totalPages = $this->paginationService->getTotalPages();
        $totalRows = count($items);

        // Generate HTML for items only (no pagination controls)
        try {
            $itemsHtml = HtmlGenerationService::generateItemsHtml($paginatedItems, $wishlist['id'], $page, 'buyer');
        } catch (Exception $e) {
            $itemsHtml = '<div class="error">Error loading items</div>';
        }
        
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
        exit;
    }

}
