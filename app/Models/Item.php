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
}