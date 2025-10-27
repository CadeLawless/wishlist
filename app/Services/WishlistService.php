<?php

namespace App\Services;

use App\Models\Wishlist;
use App\Models\Item;
use App\Models\User;

class WishlistService
{
    private Wishlist $wishlist;
    private Item $item;

    public function __construct()
    {
        $this->wishlist = new Wishlist();
        $this->item = new Item();
    }

    public function createWishlist(string $username, array $data): ?array
    {
        $data['username'] = $username;
        return $this->wishlist->createWishlist($data);
    }

    public function getUserWishlists(string $username): array
    {
        return Wishlist::where('username', '=', $username);
    }

    public function getWishlistById(string $username, int $id): ?array
    {
        return Wishlist::findByUserAndId($username, $id);
    }

    public function getOtherWishlists(string $username, int $excludeId): array
    {
        return Wishlist::findOtherWishlists($username, $excludeId);
    }

    public function getWishlistBySecretKey(string $secretKey): ?array
    {
        return Wishlist::findBySecretKey($secretKey);
    }


    public function addItem(int $wishlistId, array $data): ?int
    {
        $data['wishlist_id'] = $wishlistId;
        return Item::create($data);
    }

    public function getWishlistItems(int $wishlistId, array $filters = []): array
    {
        // Build SQL query with filters
        $sql = "SELECT * FROM items WHERE wishlist_id = ?";
        $params = [$wishlistId];
        
        if (isset($filters['priority'])) {
            $sql .= " AND priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (isset($filters['purchased'])) {
            $sql .= " AND purchased = ?";
            $params[] = $filters['purchased'];
        }
        
        // Apply sorting - use custom order clause if provided (for buyer view)
        if (isset($filters['order_clause'])) {
            $sql .= " ORDER BY " . $filters['order_clause'];
        } else {
            $sortBy = $filters['sort_by'] ?? 'date_added';
            $sortOrder = $filters['sort_order'] ?? 'DESC';
            
            if ($sortBy === 'priority') {
                $sql .= " ORDER BY priority {$sortOrder}";
            } elseif ($sortBy === 'price') {
                $sql .= " ORDER BY price * 1 {$sortOrder}";
            } else {
                $sql .= " ORDER BY date_added {$sortOrder}";
            }
        }
        
        $stmt = \App\Core\Database::query($sql, $params);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getItem(int $wishlistId, int $itemId): ?array
    {
        $item = Item::find($itemId);
        return ($item && $item['wishlist_id'] == $wishlistId) ? $item : null;
    }

    public function updateItem(int $wishlistId, int $itemId, array $data): bool
    {
        $item = $this->getItem($wishlistId, $itemId);
        if ($item) {
            return Item::update($itemId, $data);
        }
        return false;
    }

    public function deleteItem(int $wishlistId, int $itemId): bool
    {
        $item = $this->getItem($wishlistId, $itemId);
        if ($item) {
            return Item::delete($itemId);
        }
        return false;
    }

    public function purchaseItem(int $wishlistId, int $itemId, int $quantity = 1): bool
    {
        $item = $this->getItem($wishlistId, $itemId);
        if ($item) {
            // Update quantity_purchased
            $newQuantityPurchased = $item['quantity_purchased'] + $quantity;
            $data = ['quantity_purchased' => $newQuantityPurchased];
            
            // Mark as purchased if quantity purchased >= quantity needed
            if ($newQuantityPurchased >= $item['quantity'] && $item['unlimited'] != 'Yes') {
                $data['purchased'] = 'Yes';
            }
            
            return Item::update($itemId, $data);
        }
        return false;
    }

    /**
     * Update all copied items when the original item is modified
     * 
     * This method synchronizes changes across all copies of an item when the original
     * is updated. It handles image copying, file management, and data synchronization.
     * 
     * @param string $copyId The copy ID that links all related items
     * @param array $updateData The data to update across all copies
     * @param string $sourceWishlistId The wishlist ID of the original item
     * @param \App\Services\FileUploadService $fileUploadService Service for file operations
     * @return bool True if all updates succeeded, false otherwise
     */
    public function updateCopiedItems(string $copyId, array $updateData, string $sourceWishlistId, \App\Services\FileUploadService $fileUploadService): bool
    {
        try {
            // Find all items with this copy_id (excluding the source item)
            $items = Item::findByCopyIdExcludingWishlist($copyId, $sourceWishlistId);

            foreach ($items as $item) {
                $itemId = $item['id'];
                $wishlistId = $item['wishlist_id'];
                $currentImage = $item['image'];
                
                // Prepare update data for this item
                $itemUpdateData = $updateData;
                
                // Handle image updates - copy images between wishlists
                if (isset($updateData['image'])) {
                    $newImage = $updateData['image'];
                    
                    // Skip if this is the same image
                    if ($currentImage === $newImage) {
                        unset($itemUpdateData['image']); // Don't update image field
                    } else {
                        // Delete old image if it exists and is different
                        if ($currentImage !== $updateData['image']) {
                            $fileUploadService->deleteItemImage($wishlistId, $currentImage);
                        }

                        // Copy new image to this wishlist from the source wishlist
                        $sourcePath = "images/item-images/{$sourceWishlistId}/{$newImage}";
                        $targetDir = "images/item-images/{$wishlistId}/";
                        
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0755, true);
                        }

                        $targetFilename = $this->getUniqueFilename($targetDir, $newImage);
                        $targetPath = $targetDir . $targetFilename;

                        if (copy($sourcePath, $targetPath)) {
                            $itemUpdateData['image'] = $targetFilename;
                        } else {
                            unset($itemUpdateData['image']); // Skip image update if copy failed
                        }
                    }
                }
                
                // Update the item with the new data
                if (!empty($itemUpdateData)) {
                    \App\Core\Database::query(
                        "UPDATE items SET " . implode(' = ?, ', array_keys($itemUpdateData)) . " = ? WHERE id = ?",
                        array_merge(array_values($itemUpdateData), [$itemId])
                    );
                }
            }

            return true;
        } catch (\Exception $e) {
            error_log('Error updating copied items: ' . $e->getMessage());
            return false;
        }
    }

    private function getUniqueFilename(string $directory, string $filename): string
    {
        $pathInfo = pathinfo($filename);
        $name = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        $counter = 1;

        while (file_exists($directory . $filename)) {
            $filename = $name . $counter . '.' . $extension;
            $counter++;
        }

        return $filename;
    }

    public function copyItems(int $fromWishlistId, int $toWishlistId, array $itemIds): int
    {
        $copiedCount = 0;
        
        foreach ($itemIds as $itemId) {
            $sourceItem = Item::find($itemId);
            if ($sourceItem && $sourceItem['wishlist_id'] == $fromWishlistId) {
                $itemData = $sourceItem;
                unset($itemData['id']); // Remove ID to create new item
                $itemData['wishlist_id'] = $toWishlistId;
                $itemData['copy_id'] = $itemId; // Track original item
                
                $newItemId = Item::create($itemData);
                if ($newItemId) {
                    $copiedCount++;
                }
            }
        }
        
        return $copiedCount;
    }

    /**
     * Calculate comprehensive statistics for a wishlist
     * 
     * Processes all items in a wishlist to calculate totals, purchased counts,
     * and price breakdowns. Handles both regular and unlimited quantity items.
     * 
     * @param int $wishlistId The wishlist ID to calculate stats for
     * @return array Statistics including item counts and price totals
     */
    public function getWishlistStats(int $wishlistId): array
    {
        $items = $this->getWishlistItems($wishlistId);
        
        $totalItems = count($items);
        $purchasedItems = 0;
        $totalPrice = 0;
        $purchasedPrice = 0;
        
        foreach ($items as $item) {
            // Calculate total price (price * quantity)
            $itemPrice = (float)str_replace(['$', ','], '', $item['price']);
            $quantity = $item['unlimited'] === 'Yes' ? 1 : (int)$item['quantity'];
            $itemTotalPrice = $itemPrice * $quantity;
            $totalPrice += $itemTotalPrice;
            
            // Calculate purchased price
            $quantityPurchased = (int)$item['quantity_purchased'];
            $purchasedItemPrice = $itemPrice * $quantityPurchased;
            $purchasedPrice += $purchasedItemPrice;
            
            if ($item['purchased'] === 'Yes') {
                $purchasedItems++;
            }
        }
        
        return [
            'total_items' => $totalItems,
            'purchased_items' => $purchasedItems,
            'remaining_items' => $totalItems - $purchasedItems,
            'total_price' => $totalPrice,
            'purchased_price' => $purchasedPrice,
            'remaining_price' => $totalPrice - $purchasedPrice
        ];
    }

    public function searchWishlists(string $query, string $username = null): array
    {
        return Wishlist::searchByName($query, $username);
    }


    public function updateWishlistTheme(int $id, int $backgroundId, int $giftWrapId): bool
    {
        return Wishlist::updateTheme($id, $backgroundId, $giftWrapId);
    }

    public function toggleWishlistVisibility(int $id): bool
    {
        return Wishlist::toggleVisibility($id);
    }

    public function toggleWishlistComplete(int $id): bool
    {
        return Wishlist::toggleComplete($id);
    }

    public function deleteWishlistAndItems(int $id): bool
    {
        try {
            // Get all items with their images before deleting
            $items = Item::findByWishlistIdWithImages($id);
            
            // Delete item images from server
            foreach ($items as $item) {
                if (!empty($item['image'])) {
                    $imagePath = __DIR__ . "/../../images/item-images/{$id}/{$item['image']}";
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
            }
            
            // Delete the item images directory if it exists and is empty
            $itemImagesDir = __DIR__ . "/../../images/item-images/{$id}";
            if (is_dir($itemImagesDir) && count(scandir($itemImagesDir)) <= 2) { // Only . and .. entries
                rmdir($itemImagesDir);
            }
            
            // Delete all items from database
            Item::deleteByWishlistId($id);
            
            // Delete wishlist from database
            return Wishlist::delete($id);
        } catch (\Exception $e) {
            error_log('Delete wishlist failed: ' . $e->getMessage());
            return false;
        }
    }

    public function updateWishlistName(int $id, string $name): bool
    {
        try {
            return Wishlist::updateName($id, $name);
        } catch (\Exception $e) {
            error_log('Update wishlist name failed: ' . $e->getMessage());
            return false;
        }
    }

    public function updateDuplicateFlags(string $username, string $wishlistName): void
    {
        try {
            Wishlist::updateDuplicateFlags($username, $wishlistName);
        } catch (\Exception $e) {
            error_log('Update duplicate flags failed: ' . $e->getMessage());
        }
    }

    /**
     * Get count of other copies of an item (excluding the item itself)
     * 
     * @param string $copyId The copy ID to search for
     * @param int $excludeItemId The item ID to exclude from count
     * @return int Number of other copies
     */
    public function getOtherCopiesCount(string $copyId, int $excludeItemId): int
    {
        return Item::countByCopyIdExcludingItem($copyId, $excludeItemId);
    }

    /**
     * Check if an item with the given copy_id already exists in a wishlist
     * 
     * @param string $copyId The copy ID to search for
     * @param int $wishlistId The wishlist ID to check
     * @return bool True if item already exists in wishlist
     */
    public function itemExistsInWishlist(string $copyId, int $wishlistId): bool
    {
        return Item::existsByCopyIdAndWishlist($copyId, $wishlistId);
    }
}
