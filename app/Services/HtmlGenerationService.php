<?php

namespace App\Services;

class HtmlGenerationService
{
    /**
     * Generate HTML for a collection of items
     */
    public static function generateItemsHtml(array $items, int $wishlistId, int $page, string $type = 'wisher'): string
    {
        $html = '';
        
        foreach ($items as $item) {
            $html .= ItemRenderService::renderItem($item, $wishlistId, $page, $type);
        }
        
        return $html;
    }

    /**
     * Generate HTML for item checkboxes in copy functionality
     */
    public static function generateItemCheckboxes(array $items, int $sourceWishlistId, int $targetWishlistId, bool $copyFrom, WishlistService $wishlistService): string
    {
        $html = '';
        
        // First pass: check which items are already in the list and store results
        $copyCounter = 0;
        $alreadyInListMap = [];
        foreach ($items as $item) {
            $itemCopyId = $item['copy_id'] ?? null;
            $alreadyInList = false;
            if ($itemCopyId) {
                $alreadyInList = $wishlistService->itemExistsInWishlist($itemCopyId, $targetWishlistId);
                $alreadyInListMap[$item['id']] = $alreadyInList;
                if ($alreadyInList) {
                    $copyCounter++;
                }
            } else {
                $alreadyInListMap[$item['id']] = false;
            }
        }
        
        // Calculate selectable items (items not already in target list)
        $totalItems = count($items);
        $selectableItemsCount = $totalItems - $copyCounter;
        $allItemsDisabled = $selectableItemsCount === 0;
        
        // Add "All Items" checkbox
        $allItemsDisabledAttr = $allItemsDisabled ? 'disabled' : '';
        $allItemsDisabledClass = $allItemsDisabled ? ' already-in-list' : '';
        $html .= "
        <div class='checkboxes-container'>
            <div class='select-item-container select-all{$allItemsDisabledClass}'>
                <div class='option-title'>All Items</div>
                <div class='option-checkbox'>
                    <input type='checkbox' name='copy_" . ($copyFrom ? "from" : "to") . "_select_all' class='check-all{$allItemsDisabledClass}' {$allItemsDisabledAttr} />
                </div>
            </div>";
        
        foreach ($items as $item) {
            $itemName = htmlspecialchars(string: $item['name'], flags: ENT_QUOTES, encoding: 'UTF-8');
            $itemId = $item['id'];
            $itemCopyId = $item['copy_id'];
            $itemImage = $item['image'];
            
            // Use pre-calculated already-in-list status
            $alreadyInList = $alreadyInListMap[$itemId] ?? false;
            
            // Determine image path - use source wishlist ID for image location
            $absoluteImagePath = __DIR__ . "/../../public/images/item-images/{$sourceWishlistId}/" . htmlspecialchars(string: $itemImage, flags: ENT_QUOTES, encoding: 'UTF-8');
            
            if (!file_exists($absoluteImagePath)) {
                $imagePath = "/wishlist/public/images/site-images/default-photo.png";
            } else {
                $imagePath = "/wishlist/public/images/item-images/{$sourceWishlistId}/" . htmlspecialchars(string: $itemImage, flags: ENT_QUOTES, encoding: 'UTF-8');
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
                    <input type='checkbox' class='{$checkboxClass}' name='item_ids[]' value='{$itemId}' {$disabled} />
                </div>
            </div>";
        }
        
        $html .= "
        </div>
        <p class='center" . ($copyCounter == count($items) ? ' hidden' : '') . "'>
            <input type='submit' class='button primary' name='copy_" . ($copyFrom ? "from" : "to") . "_submit' value='Copy Selected Items' />
        </p>";
        
        return $html;
    }

    /**
     * Generate error HTML for invalid filters
     */
    public static function generateFilterErrorHtml(): string
    {
        return '<strong>Invalid filter. Please try again.</strong>';
    }
}
