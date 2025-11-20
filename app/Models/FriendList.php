<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class FriendList extends Model
{
    protected static string $table = 'friend_list';

    public function getFriendsByUsername(string $username): array
    {
        return $this->queryBuilder
            ->columns(['friend_username', 'added_at'])
            ->where('username', $username)
            ->getAll();
    }

    public function addFriend(array $data): ?array
    {

        $today = date("Y-m-d H:i:s");

        $result = $this->queryBuilder
            ->columns(['username', 'friend_username', 'added_at'])
            ->params([$data['username'], $data['friend_username'], $today])
            ->insert();

        if ($result) {
            $friendId = Database::lastInsertId();
            // Return the created wishlist data
            return [
                'id' => $friendId,
                'date_created' => $today
            ];
        }

        return null;
    }

    public function getCountOfFriendsByUsername(string $username): int
    {
        $result = $this->queryBuilder
            ->columns(['COUNT(*) as friend_count'])
            ->where('username', $username)
            ->first();
        return (int)$result['friend_count'];
    }

    public function findFriendByUsername(string $username, string $friendUsername): ?array
    {
        return $this->queryBuilder
            ->columns(['username'])
            ->where('username', $username)
            ->andWhere('friend_username', $friendUsername)
            ->first();
    }
}