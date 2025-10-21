<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Helpers\StringHelper;

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

    public function createWishlist(array $data): ?array
    {
        // Generate random key for wishlist
        $unique = false;
        while (!$unique) {
            $secret_key = StringHelper::generateRandomString(10);
            // Check to make sure that key doesn't exist for another wishlist in the database
            $checkKey = Database::query("SELECT secret_key FROM " . static::$table . " WHERE secret_key = ?", [$secret_key]);
            if ($checkKey->get_result()->num_rows == 0) $unique = true;
        }

        // Get year for wishlist
        $currentYear = date("Y");
        $year = date("m/d/Y") >= "12/25/$currentYear" ? $currentYear + 1 : $currentYear;

        // Find if there is a duplicate type and name in database
        $duplicateValue = static::findDuplicates($data['wishlist_type'], $data['wishlist_name'], $data['username']);

        $today = date("Y-m-d H:i:s");

        // Create new wishlist
        $stmt = Database::query(
            "INSERT INTO " . static::$table . "(type, wishlist_name, theme_background_id, theme_gift_wrap_id, year, duplicate, username, secret_key, date_created) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$data['wishlist_type'], $data['wishlist_name'], $data['theme_background_id'], $data['theme_gift_wrap_id'], $year, $duplicateValue, $data['username'], $secret_key, $today]
        );

        if ($stmt) {
            $wishlistId = Database::lastInsertId();
            // Return the created wishlist data
            return [
                'id' => $wishlistId,
                'type' => $data['wishlist_type'],
                'wishlist_name' => $data['wishlist_name'],
                'theme_background_id' => $data['theme_background_id'],
                'theme_gift_wrap_id' => $data['theme_gift_wrap_id'],
                'year' => $year,
                'username' => $data['username'],
                'secret_key' => $secret_key,
                'date_created' => $today
            ];
        }

        return null;
    }

}