<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class FriendRequest extends Model
{
    protected static string $table = 'friend_requests';

    public static function getSentFriendRequestsByUsername(string $username): array
    {
        $stmt = Database::query(
            "SELECT * FROM " . static::$table . " WHERE sender_username = ? AND `status` = 'pending'",
            [$username]
        );

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function getReceivedFriendRequestsByUsername(string $username): array
    {
        $stmt = Database::query(
            "SELECT * FROM " . static::$table . " WHERE receiver_username = ? AND `status` = 'pending'",
            [$username]
        );

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function get(string $username): array
    {
        $stmt = Database::query(
            "SELECT * FROM " . static::$table . " WHERE username_1 = ? OR username_2 = ?",
            [$username, $username]
        );

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function createFriendship(array $data): ?array
    {

        $today = date("Y-m-d H:i:s");

        // Create new wishlist
        $stmt = Database::query(
            "INSERT INTO " . static::$table . "(username_1, username_2, created_at) VALUES(?, ?, ?)",
            [$data['username_1'], $data['username_2'], $today]
        );

        if ($stmt) {
            $friendshipId = Database::lastInsertId();
            // Return the created wishlist data
            return [
                'id' => $friendshipId,
                'date_created' => $today
            ];
        }

        return null;
    }

}