<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\QueryBuilder;

class FriendRequest extends Model
{
    protected static string $table = 'friend_requests';

    public function getSentFriendRequestsByUsername(string $username): array
    {
        return $this->queryBuilder
            ->select(['sender_username', 'receiver_username', 'status', 'created_at'])
            ->where('sender_username', $username)
            ->andWhere('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->getAll();
    }

    public function getReceivedFriendRequestsByUsername(string $username): array
    {
        $stmt = Database::query(
            "SELECT * FROM " . static::$table . " WHERE receiver_username = ? AND `status` = 'pending'",
            [$username]
        );

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function createFriendRequest(array $data): ?array
    {
        $today = date("Y-m-d H:i:s");

        $stmt = Database::query(
            "INSERT INTO " . static::$table . "(sender_username, receiver_username, `status`, created_at) VALUES(?, ?, 'pending', ?)",
            [$data['sender_username'], $data['receiver_username'], $today]
        );

        if ($stmt) {
            $friendRequestId = Database::lastInsertId();
            return [
                'id' => $friendRequestId,
                'date_created' => $today
            ];
        }

        return null;
    }

    public function findByUsernames(string $senderUsername, string $receiverUsername): ?array
    {
        $stmt = Database::query(
            "SELECT * FROM " . static::$table . " WHERE (sender_username = ? AND receiver_username = ?) OR (sender_username = ? AND receiver_username = ?) AND `status` = 'pending'",
            [$senderUsername, $receiverUsername, $receiverUsername, $senderUsername]
        );

        $result = $stmt->get_result()->fetch_assoc();
        return $result ?: null;
    }

    public function getCountOfSentRequests(string $username): int
    {
        $result = $this->queryBuilder
            ->select(['COUNT(*) AS request_count'])
            ->where('sender_username', $username)
            ->andWhere('status', 'pending')
            ->first();

        return $result ? (int)$result['request_count'] : 0;
    }

    public function getCountOfReceivedRequests(string $username): int
    {
        $result = $this->queryBuilder
            ->select(['COUNT(*) AS request_count'])
            ->where('receiver_username', $username)
            ->andWhere('status', 'pending')
            ->first();

        return $result ? (int)$result['request_count'] : 0;
    }
}