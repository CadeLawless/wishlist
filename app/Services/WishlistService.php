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

    public function createWishlist(string $username, array $data): ?Wishlist
    {
        $data['username'] = $username;
        return $this->wishlist->createWishlist($data);
    }

    public function getUserWishlists(string $username): array
    {
        return Wishlist::where('username', '=', $username);
    }

    public function getWishlistById(string $username, int $id): ?Wishlist
    {
        return $this->wishlist->findByUserAndId($username, $id);
    }

    public function getWishlistBySecretKey(string $secretKey): ?Wishlist
    {
        return $this->wishlist->findBySecretKey($secretKey);
    }

    public function updateWishlistName(int $id, string $name): bool
    {
        $wishlist = $this->wishlist->find($id);
        if ($wishlist) {
            return $wishlist->updateName($name);
        }
        return false;
    }

    public function updateWishlistTheme(int $id, int $backgroundId, int $giftWrapId): bool
    {
        $wishlist = $this->wishlist->find($id);
        if ($wishlist) {
            return $wishlist->updateTheme($backgroundId, $giftWrapId);
        }
        return false;
    }

    public function toggleVisibility(int $id): bool
    {
        $wishlist = $this->wishlist->find($id);
        if ($wishlist) {
            return $wishlist->toggleVisibility();
        }
        return false;
    }

    public function toggleComplete(int $id): bool
    {
        $wishlist = $this->wishlist->find($id);
        if ($wishlist) {
            return $wishlist->toggleComplete();
        }
        return false;
    }

    public function deleteWishlist(int $id): bool
    {
        $wishlist = $this->wishlist->find($id);
        if ($wishlist) {
            // Delete all items first
            $items = $wishlist->items();
            foreach ($items as $item) {
                $item->delete();
            }
            
            // Delete wishlist
            return $wishlist->delete();
        }
        return false;
    }

    public function addItem(int $wishlistId, array $data): ?Item
    {
        $data['wishlist_id'] = $wishlistId;
        return $this->item->createItem($data);
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

    public function getItem(int $wishlistId, int $itemId): ?Item
    {
        $stmt = \App\Core\Database::query("SELECT * FROM items WHERE wishlist_id = ? AND id = ?", [$wishlistId, $itemId]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? new Item($result) : null;
    }

    public function updateItem(int $wishlistId, int $itemId, array $data): bool
    {
        $item = $this->getItem($wishlistId, $itemId);
        if ($item) {
            return $item->updateItem($data);
        }
        return false;
    }

    public function deleteItem(int $wishlistId, int $itemId): bool
    {
        $item = $this->getItem($wishlistId, $itemId);
        if ($item) {
            return $item->delete();
        }
        return false;
    }

    public function purchaseItem(int $wishlistId, int $itemId, int $quantity = 1): bool
    {
        $item = $this->getItem($wishlistId, $itemId);
        if ($item) {
            return $item->purchase($quantity);
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
}
