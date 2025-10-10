<?php

namespace App\Services;

use App\Core\Database;

class ItemCopyService
{
    public function copyItems(int $fromWishlistId, int $toWishlistId, array $itemIds): int
    {
        $copiedCount = 0;
        
        foreach ($itemIds as $itemId) {
            // Get source item
            $sourceItem = $this->getItem($itemId);
            if (!$sourceItem || $sourceItem['wishlist_id'] != $fromWishlistId) {
                continue;
            }

            // Check if item already exists in destination (by copy_id)
            if ($this->itemExistsInWishlist($sourceItem['copy_id'], $toWishlistId)) {
                continue;
            }

            // Copy the item
            if ($this->copyItem($sourceItem, $toWishlistId)) {
                $copiedCount++;
            }
        }
        
        return $copiedCount;
    }

    public function copyItem(array $sourceItem, int $toWishlistId): bool
    {
        try {
            // Update copy_id to track original item
            $stmt = Database::query(
                "UPDATE items SET copy_id = ? WHERE id = ?", 
                [$sourceItem['id'], $sourceItem['id']]
            );

            // Copy item image if it exists
            $newImageName = $this->duplicateItemImage(
                $sourceItem['wishlist_id'], 
                $toWishlistId, 
                $sourceItem['image']
            );

            // Insert new item
            $dateAdded = date('Y-m-d H:i:s');
            $stmt = Database::query(
                "INSERT INTO items (wishlist_id, copy_id, name, notes, price, quantity, unlimited, link, image, priority, quantity_purchased, purchased, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [
                    $toWishlistId,
                    $sourceItem['id'],
                    $sourceItem['name'],
                    $sourceItem['notes'],
                    $sourceItem['price'],
                    $sourceItem['quantity'],
                    $sourceItem['unlimited'],
                    $sourceItem['link'],
                    $newImageName,
                    $sourceItem['priority'],
                    $sourceItem['quantity_purchased'],
                    $sourceItem['purchased'],
                    $dateAdded
                ]
            );

            return $stmt->affected_rows > 0;
        } catch (\Exception $e) {
            error_log('Item copy failed: ' . $e->getMessage());
            return false;
        }
    }

    public function duplicateItemImage(int $fromWishlistId, int $toWishlistId, string $imageName): string
    {
        $fromPath = "images/item-images/{$fromWishlistId}/{$imageName}";
        $toDir = "images/item-images/{$toWishlistId}";
        $toPath = "{$toDir}/{$imageName}";

        // Create destination directory if it doesn't exist
        if (!is_dir($toDir)) {
            mkdir($toDir, 0755, true);
        }

        // Check if file already exists and create unique name
        if (file_exists($toPath)) {
            $newImageName = $this->generateUniqueImageName($toDir, $imageName);
            $toPath = "{$toDir}/{$newImageName}";
        } else {
            $newImageName = $imageName;
        }

        // Copy the file
        if (file_exists($fromPath)) {
            if (copy($fromPath, $toPath)) {
                return $newImageName;
            } else {
                error_log("Failed to copy image from {$fromPath} to {$toPath}");
            }
        }

        return $newImageName;
    }

    private function generateUniqueImageName(string $dir, string $originalName): string
    {
        $pathInfo = pathinfo($originalName);
        $name = $pathInfo['filename'];
        $extension = $pathInfo['extension'];
        
        $counter = 1;
        do {
            $newName = "{$name}{$counter}.{$extension}";
            $fullPath = "{$dir}/{$newName}";
            $counter++;
        } while (file_exists($fullPath));
        
        return $newName;
    }

    public function itemExistsInWishlist(?string $copyId, int $wishlistId): bool
    {
        if (!$copyId) {
            return false;
        }

        $stmt = Database::query(
            "SELECT COUNT(*) as count FROM items WHERE copy_id = ? AND wishlist_id = ?",
            [$copyId, $wishlistId]
        );
        
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    public function getItem(int $itemId): ?array
    {
        $stmt = Database::query("SELECT * FROM items WHERE id = ?", [$itemId]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public function getWishlistItems(int $wishlistId): array
    {
        $stmt = Database::query("SELECT * FROM items WHERE wishlist_id = ?", [$wishlistId]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteItemFromAllWishlists(int $itemId): bool
    {
        try {
            // Get the item to find its copy_id
            $item = $this->getItem($itemId);
            if (!$item) {
                return false;
            }

            $copyId = $item['copy_id'] ?: $itemId;

            // Delete all items with this copy_id
            $stmt = Database::query("DELETE FROM items WHERE copy_id = ?", [$copyId]);
            return $stmt->affected_rows > 0;
        } catch (\Exception $e) {
            error_log('Delete from all wishlists failed: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteItemFromWishlist(int $itemId): bool
    {
        try {
            $stmt = Database::query("DELETE FROM items WHERE id = ?", [$itemId]);
            return $stmt->affected_rows > 0;
        } catch (\Exception $e) {
            error_log('Delete item failed: ' . $e->getMessage());
            return false;
        }
    }
}
