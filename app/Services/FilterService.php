<?php

namespace App\Services;

class FilterService
{
    /**
     * Get sort direction based on sort value
     */
    private static function getSortDirection(string $sortValue): string
    {
        return match ($sortValue) {
            "1" => "ASC",
            "2" => "DESC",
            default => "",
        };
    }

    /**
     * Build order clause for wishlist items based on sort parameters
     */
    public static function buildOrderClause(string $sortPriority, string $sortPrice): string
    {
        $priority_order = "";
        if ($sortPriority) {
            $direction = self::getSortDirection($sortPriority);
            $priority_order = "priority {$direction}, ";
        }
        
        $price_order = "";
        if ($sortPrice) {
            $direction = self::getSortDirection($sortPrice);
            $price_order = "price * 1 {$direction}, ";
        }
        
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
        
        // Build base filters and add order clause
        $filters = self::buildBaseFilters($requestData);
        $filters['order_clause'] = $orderClause;
        
        return $filters;
    }

    /**
     * Process wisher view filters and sorting
     */
    public static function processWisherFilters(array $requestData): array
    {
        $filters = self::buildBaseFilters($requestData);
        $filters['order_clause'] = $requestData['order_clause'] ?? 'purchased ASC, date_added DESC';
        
        return $filters;
    }

    /**
     * Convert wisher session filters to service format
     * 
     * Uses buildOrderClause() to handle both priority and price sorting
     * when both are selected, ensuring consistent behavior with buyer view.
     */
    public static function convertWisherSessionFilters(string $sortPriority, string $sortPrice): array
    {
        $serviceFilters = [];
        
        // Use buildOrderClause for consistent multi-sort behavior
        if ($sortPriority || $sortPrice) {
            $serviceFilters['order_clause'] = self::buildOrderClause($sortPriority, $sortPrice);
        } else {
            $serviceFilters['sort_by'] = 'date_added';
            $serviceFilters['sort_order'] = 'DESC';
        }
        
        return $serviceFilters;
    }

    /**
     * Build base filter array with common defaults
     */
    private static function buildBaseFilters(array $requestData): array
    {
        return [
            'sort_by' => $requestData['sort_by'] ?? 'date_added',
            'sort_order' => $requestData['sort_order'] ?? 'DESC',
            'priority' => $requestData['priority'] ?? null,
            'purchased' => $requestData['purchased'] ?? null,
        ];
    }

    /**
     * Validate filter options
     */
    public static function validateFilters(string $sortPriority, string $sortPrice): bool
    {
        $validOptions = ['', '1', '2'];
        return in_array($sortPriority, $validOptions) && in_array($sortPrice, $validOptions);
    }

    /**
     * Validate wisher filter options (alias for consistency)
     */
    public static function validateWisherFilters(string $sortPriority, string $sortPrice): bool
    {
        return self::validateFilters($sortPriority, $sortPrice);
    }
}
