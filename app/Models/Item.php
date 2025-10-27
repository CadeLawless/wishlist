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
}