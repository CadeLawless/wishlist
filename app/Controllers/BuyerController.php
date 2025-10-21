<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\WishlistService;
use App\Services\PaginationService;
use App\Services\FilterService;
use Exception;

class BuyerController extends Controller
{
    private WishlistService $wishlistService;
    private PaginationService $paginationService;

    public function __construct()
    {
        parent::__construct();
        $this->wishlistService = new WishlistService();
        $this->paginationService = new PaginationService();
    }

    public function show(string $secretKey): Response
    {
        $wishlist = $this->wishlistService->getWishlistBySecretKey($secretKey);
        
        if (!$wishlist) {
            return $this->view('errors/404', [], 'main');
        }

        if ($wishlist['visibility'] !== 'Public' || $wishlist['complete'] === 'Yes') {
            return $this->view('errors/access-denied', [], 'main');
        }

        // Handle pagination
        $pageno = (int) $this->request->get('pageno', 1);
        
        // Process filters and sorting using FilterService
        $requestData = $this->request->get();
        $filters = FilterService::processBuyerFilters($requestData);
        
        // Get sort values for view
        $sort_priority = $requestData['sort_priority'] ?? '';
        $sort_price = $requestData['sort_price'] ?? '';

        // Get all items first (for pagination)
        $allItems = $this->wishlistService->getWishlistItems($wishlist['id'], $filters);
        
        // Apply pagination
        $paginatedItems = $this->paginationService->paginate($allItems, $pageno);
        $totalPages = $this->paginationService->getTotalPages();
        
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

        $quantity = (int)$this->request->input('quantity', 1);
        
        if ($this->wishlistService->purchaseItem($wishlist['id'], $itemId, $quantity)) {
            return $this->redirect("/buyer/{$secretKey}")->withSuccess('Item marked as purchased!');
        }

        return $this->redirect("/buyer/{$secretKey}")->withError('Unable to mark item as purchased.');
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
        $sortPriority = $_SESSION['buyer_sort_priority'] ?? '';
        $sortPrice = $_SESSION['buyer_sort_price'] ?? '';
        
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
            $itemsHtml = $this->generateItemsHtml($paginatedItems, $wishlist['id'], $page);
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

    private function generateItemsHtml(array $items, int $wishlistId, int $page): string
    {
        $html = '';
        foreach ($items as $item) {
            $html .= \App\Services\ItemRenderService::renderItem($item, $wishlistId, $page, 'buyer');
        }
        return $html;
    }
}
