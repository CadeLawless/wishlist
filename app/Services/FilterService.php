<?php

namespace App\Services;

class FilterService
{
    /**
     * Build order clause for wishlist items based on sort parameters
     */
    public static function buildOrderClause(string $sortPriority, string $sortPrice): string
    {
        $priority_order = $sortPriority ? "priority ASC, " : "";
        $price_order = $sortPrice ? "price * 1 ASC, " : "";
        return "purchased ASC, {$priority_order}{$price_order}date_added DESC";
    }

    /**
     * Process buyer view filters and sorting
     */
    public static function processBuyerFilters(array $requestData): array
    {
        $sortPriority = $requestData['sort_priority'] ?? '';
        $sortPrice = $requestData['sort_price'] ?? '';
        
        // Store preferences in session
        SessionManager::storeBuyerSortPreferences($sortPriority, $sortPrice);
        
        // Build order clause
        $orderClause = self::buildOrderClause($sortPriority, $sortPrice);
        
        return [
            'sort_by' => $requestData['sort_by'] ?? 'date_added',
            'sort_order' => $requestData['sort_order'] ?? 'DESC',
            'priority' => $requestData['priority'] ?? null,
            'purchased' => $requestData['purchased'] ?? null,
            'order_clause' => $orderClause
        ];
    }

    /**
     * Process wisher view filters and sorting
     */
    public static function processWisherFilters(array $requestData): array
    {
        return [
            'sort_by' => $requestData['sort_by'] ?? 'date_added',
            'sort_order' => $requestData['sort_order'] ?? 'DESC',
            'priority' => $requestData['priority'] ?? null,
            'purchased' => $requestData['purchased'] ?? null,
            'order_clause' => $requestData['order_clause'] ?? 'purchased ASC, date_added DESC'
        ];
    }

    /**
     * Convert wisher session filters to service format
     */
    public static function convertWisherSessionFilters(string $sortPriority, string $sortPrice): array
    {
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
        
        return $serviceFilters;
    }

    /**
     * Validate wisher filter options
     */
    public static function validateWisherFilters(string $sortPriority, string $sortPrice): bool
    {
        $validOptions = ['', '1', '2'];
        return in_array($sortPriority, $validOptions) && in_array($sortPrice, $validOptions);
    }
}
