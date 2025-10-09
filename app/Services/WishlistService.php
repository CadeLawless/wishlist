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
        return $this->wishlist->where('username', $username)
            ->orderBy('date_created', 'DESC')
            ->get();
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
        $query = $this->item->where('wishlist_id', $wishlistId);
        
        // Apply filters
        if (isset($filters['priority'])) {
            $query = $query->where('priority', $filters['priority']);
        }
        
        if (isset($filters['purchased'])) {
            $query = $query->where('purchased', $filters['purchased']);
        }
        
        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'date_added';
        $sortOrder = $filters['sort_order'] ?? 'DESC';
        
        if ($sortBy === 'priority') {
            $query = $query->orderBy('priority', 'ASC');
        } elseif ($sortBy === 'price') {
            $query = $query->orderBy('price', $sortOrder);
        } else {
            $query = $query->orderBy('date_added', $sortOrder);
        }
        
        return $query->get();
    }

    public function getItem(int $wishlistId, int $itemId): ?Item
    {
        return $this->item->where('wishlist_id', $wishlistId)
            ->where('id', $itemId)
            ->first();
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
        $searchQuery = $this->wishlist->where('wishlist_name', 'LIKE', "%{$query}%");
        
        if ($username) {
            $searchQuery = $searchQuery->where('username', $username);
        }
        
        return $searchQuery->orderBy('date_created', 'DESC')->get();
    }
}
