<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Item extends Model
{
    protected static string $table = 'items';

    public static function findByWishlistId(int $wishlistId, string $orderBy = 'date_added DESC'): array
    {
        $stmt = Database::query("SELECT * FROM " . static::$table . " WHERE wishlist_id = ? ORDER BY {$orderBy}", [$wishlistId]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function findByCopyIdAndWishlistId(int $copyId, int $wishlistId): ?array
    {
        $stmt = Database::query("SELECT copy_id FROM " . static::$table . " WHERE copy_id = ? AND wishlist_id = ?", [$copyId, $wishlistId]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public static function getPaginatedItems(int $wishlistId, string $username, string $orderBy, int $limit, int $offset): array
    {
        $sql = "SELECT i.* FROM items i JOIN wishlists w ON i.wishlist_id = w.id WHERE i.wishlist_id = ? AND w.username = ? ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        $stmt = Database::query($sql, [$wishlistId, $username, $limit, $offset]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function countItems(int $wishlistId, string $username): int
    {
        $sql = "SELECT COUNT(i.id) FROM items i JOIN wishlists w ON i.wishlist_id = w.id WHERE i.wishlist_id = ? AND w.username = ?";
        $stmt = Database::query($sql, [$wishlistId, $username]);
        $result = $stmt->get_result()->fetch_assoc();
        return (int) $result['COUNT(i.id)'];
    }

    public static function findByCopyIdExcludingWishlist(string $copyId, int $excludeWishlistId): array
    {
        $stmt = Database::query(
            "SELECT id, wishlist_id, image FROM " . static::$table . " WHERE copy_id = ? AND wishlist_id != ?",
            [$copyId, $excludeWishlistId]
        );
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function countByCopyIdExcludingItem(string $copyId, int $excludeItemId): int
    {
        $stmt = Database::query(
            "SELECT COUNT(*) as count FROM " . static::$table . " WHERE copy_id = ? AND id != ?",
            [$copyId, $excludeItemId]
        );
        $result = $stmt->get_result()->fetch_assoc();
        return (int) $result['count'];
    }

    public static function findByCopyIdExcludingItem(string $copyId, int $excludeItemId): array
    {
        $stmt = Database::query(
            "SELECT * FROM " . static::$table . " WHERE copy_id = ? AND id != ?",
            [$copyId, $excludeItemId]
        );
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function existsByCopyIdAndWishlist(string $copyId, int $wishlistId): bool
    {
        $stmt = Database::query(
            "SELECT COUNT(*) as count FROM " . static::$table . " WHERE copy_id = ? AND wishlist_id = ?", 
            [$copyId, $wishlistId]
        );
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    public static function findByWishlistIdWithImages(int $wishlistId): array
    {
        $stmt = Database::query("SELECT image FROM " . static::$table . " WHERE wishlist_id = ?", [$wishlistId]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function deleteByWishlistId(int $wishlistId): bool
    {
        $stmt = Database::query("DELETE FROM " . static::$table . " WHERE wishlist_id = ?", [$wishlistId]);
        return $stmt->affected_rows > 0;
    }

    public static function getPurchasedPosition(int $wishlistId, int $itemId): int
    {
        $stmt = Database::query(
            "SELECT COUNT(*) AS cnt FROM " . static::$table . " WHERE wishlist_id = ? AND purchased = 'Yes' AND id <= ?",
            [$wishlistId, $itemId]
        );
        $row = $stmt->get_result()->fetch_assoc();
        return isset($row['cnt']) ? (int)$row['cnt'] : 1;
    }

    /**
     * Update purchase status for an item and all its copies
     * Updates all items with the same copy_id, or just the item itself if no copy_id
     * 
     * @param int $itemId The ID of the item being purchased
     * @param mixed $copyId The copy_id of the item (null, empty string, or 0 if original)
     * @param int $quantityPurchased The new quantity_purchased value
     * @param string $purchased The purchased status ('Yes' or 'No')
     * @return bool True if update succeeded, false otherwise
     */
    public static function updatePurchaseStatus(int $itemId, $copyId, int $quantityPurchased, string $purchased): bool
    {
        // If copy_id exists and is not empty, update all items with that copy_id, otherwise update just this item
        // Match original logic: $copy_id != "" means use copy_id, otherwise use id
        $whereColumn = ($copyId != "" && $copyId !== null) ? "copy_id" : "id";
        $whereValue = ($copyId != "" && $copyId !== null) ? $copyId : $itemId;
        
        $stmt = Database::query(
            "UPDATE " . static::$table . " SET quantity_purchased = ?, purchased = ? WHERE {$whereColumn} = ?",
            [$quantityPurchased, $purchased, $whereValue]
        );
        
        return $stmt->affected_rows > 0;
    }
}