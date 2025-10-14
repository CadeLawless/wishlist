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
        $stmt = \App\Core\Database::query(
            "SELECT * FROM wishlists WHERE username = ? AND id = ?", 
            [$username, $id]
        );
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public function getOtherWishlists(string $username, int $excludeId): array
    {
        $stmt = \App\Core\Database::query(
            "SELECT wishlist_name, id FROM wishlists WHERE username = ? AND id <> ?", 
            [$username, $excludeId]
        );
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getWishlistBySecretKey(string $secretKey): ?Wishlist
    {
        return $this->wishlist->findBySecretKey($secretKey);
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
        
        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'date_added';
        $sortOrder = $filters['sort_order'] ?? 'DESC';
        
        if ($sortBy === 'priority') {
            $sql .= " ORDER BY priority ASC";
        } elseif ($sortBy === 'price') {
            $sql .= " ORDER BY price {$sortOrder}";
        } else {
            $sql .= " ORDER BY date_added {$sortOrder}";
        }
        
        $stmt = \App\Core\Database::query($sql, $params);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getItem(int $wishlistId, int $itemId): ?array
    {
        $stmt = \App\Core\Database::query("SELECT * FROM items WHERE wishlist_id = ? AND id = ?", [$wishlistId, $itemId]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public function updateItem(int $wishlistId, int $itemId, array $data): bool
    {
        $item = $this->getItem($wishlistId, $itemId);
        if ($item) {
            error_log('WishlistService::updateItem - Item found, attempting update with data: ' . json_encode($data));
            $result = Item::update($itemId, $data);
            error_log('WishlistService::updateItem - Update result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            return $result;
        }
        error_log('WishlistService::updateItem - Item not found for wishlistId: ' . $wishlistId . ', itemId: ' . $itemId);
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

    public function copyItems(int $fromWishlistId, int $toWishlistId, array $itemIds): int
    {
        $copiedCount = 0;
        
        foreach ($itemIds as $itemId) {
            $sourceItem = $this->item->find($itemId);
            if ($sourceItem && $sourceItem->wishlist_id == $fromWishlistId) {
                $itemData = $sourceItem->toArray();
                unset($itemData['id']); // Remove ID to create new item
                $itemData['wishlist_id'] = $toWishlistId;
                $itemData['copy_id'] = $itemId; // Track original item
                
                $newItem = $this->item->createItem($itemData);
                if ($newItem) {
                    $copiedCount++;
                }
            }
        }
        
        return $copiedCount;
    }

    public function getWishlistStats(int $wishlistId): array
    {
        $items = $this->getWishlistItems($wishlistId);
        
        $totalItems = count($items);
        $purchasedItems = 0;
        $totalPrice = 0;
        $purchasedPrice = 0;
        
        foreach ($items as $item) {
            $totalPrice += $item->getTotalPrice();
            $purchasedPrice += $item->getPurchasedPrice();
            
            if ($item->isPurchased()) {
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
        $sql = "SELECT * FROM wishlists WHERE wishlist_name LIKE ?";
        $params = ["%{$query}%"];
        
        if ($username) {
            $sql .= " AND username = ?";
            $params[] = $username;
        }
        
        $sql .= " ORDER BY date_created DESC";
        
        $stmt = \App\Core\Database::query($sql, $params);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }


    public function updateWishlistTheme(int $id, int $backgroundId, int $giftWrapId): bool
    {
        $stmt = \App\Core\Database::query(
            "UPDATE wishlists SET theme_background_id = ?, theme_gift_wrap_id = ? WHERE id = ?",
            [$backgroundId, $giftWrapId, $id]
        );
        return $stmt->affected_rows > 0;
    }

    public function toggleWishlistVisibility(int $id): bool
    {
        // Get current visibility
        $stmt = \App\Core\Database::query("SELECT visibility FROM wishlists WHERE id = ?", [$id]);
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            return false;
        }
        
        $newVisibility = $result['visibility'] === 'Public' ? 'Hidden' : 'Public';
        
        $stmt = \App\Core\Database::query(
            "UPDATE wishlists SET visibility = ? WHERE id = ?",
            [$newVisibility, $id]
        );
        return $stmt->affected_rows > 0;
    }

    public function toggleWishlistComplete(int $id): bool
    {
        // Get current complete status
        $stmt = \App\Core\Database::query("SELECT complete FROM wishlists WHERE id = ?", [$id]);
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result) {
            return false;
        }
        
        $newComplete = $result['complete'] === 'Yes' ? 'No' : 'Yes';
        
        $stmt = \App\Core\Database::query(
            "UPDATE wishlists SET complete = ? WHERE id = ?",
            [$newComplete, $id]
        );
        return $stmt->affected_rows > 0;
    }

    public function deleteWishlistAndItems(int $id): bool
    {
        try {
            // Get all items with their images before deleting
            $itemsStmt = \App\Core\Database::query("SELECT image FROM items WHERE wishlist_id = ?", [$id]);
            $items = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
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
            $stmt = \App\Core\Database::query("DELETE FROM items WHERE wishlist_id = ?", [$id]);
            
            // Delete wishlist from database
            $stmt = \App\Core\Database::query("DELETE FROM wishlists WHERE id = ?", [$id]);
            return $stmt->affected_rows > 0;
        } catch (\Exception $e) {
            error_log('Delete wishlist failed: ' . $e->getMessage());
            return false;
        }
    }

    public function updateWishlistName(int $id, string $name): bool
    {
        try {
            $stmt = \App\Core\Database::query(
                "UPDATE wishlists SET wishlist_name = ? WHERE id = ?", 
                [$name, $id]
            );
            return $stmt->affected_rows > 0;
        } catch (\Exception $e) {
            error_log('Update wishlist name failed: ' . $e->getMessage());
            return false;
        }
    }

    public function updateDuplicateFlags(string $username, string $wishlistName): void
    {
        try {
            // Count how many wishlists the user has with this name
            $countStmt = \App\Core\Database::query(
                "SELECT COUNT(*) as count FROM wishlists WHERE username = ? AND wishlist_name = ?",
                [$username, $wishlistName]
            );
            $result = $countStmt->get_result()->fetch_assoc();
            $count = $result['count'];

            // If there's only one wishlist with this name, set duplicate flag to 0
            // If there are multiple, update the duplicate flags to reflect the count
            $newDuplicateFlag = max(0, $count - 1);
            
            $stmt = \App\Core\Database::query(
                "UPDATE wishlists SET duplicate = ? WHERE username = ? AND wishlist_name = ?",
                [$newDuplicateFlag, $username, $wishlistName]
            );
        } catch (\Exception $e) {
            error_log('Update duplicate flags failed: ' . $e->getMessage());
        }
    }
}
