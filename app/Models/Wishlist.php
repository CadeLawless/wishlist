<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Wishlist extends Model
{
    protected static string $table = 'wishlists';

    public static function findByUserAndId(string $username, int $id): ?array
    {
        $stmt = Database::query("SELECT * FROM " . static::$table . " WHERE username = ? AND id = ?", [$username, $id]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public static function findOtherWishlists(string $username, int $currentWishlistId): array
    {
        $stmt = Database::query("SELECT wishlist_name, id FROM " . static::$table . " WHERE username = ? AND id <> ?", [$username, $currentWishlistId]);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function findBySecretKey(string $key): ?array
    {
        $stmt = Database::query("SELECT * FROM " . static::$table . " WHERE secret_key = ?", [$key]);
        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public static function findDuplicates(string $type, string $wishlistName, string $username): int
    {
        $stmt = Database::query("SELECT id FROM " . static::$table . " WHERE type = ? AND wishlist_name = ? AND username = ?", [$type, $wishlistName, $username]);
        return $stmt->get_result()->num_rows;
    }
}