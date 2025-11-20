<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class FriendInvitation extends Model
{
    protected static string $table = 'friend_invitations';

    public function getSentInvitationsByUsername(string $username): array
    {
        return $this->queryBuilder
            ->columns(['sender_username', 'receiver_username', 'status', 'created_at'])
            ->where('sender_username', $username)
            ->andWhere('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->getAll();
    }

    public function getReceivedInvitationsByUsername(string $username): array
    {
        return $this->queryBuilder
            ->columns(['sender_username', 'receiver_username', 'status', 'created_at'])
            ->where('receiver_username', $username)
            ->andWhere('status', 'pending')
            ->orderBy('created_at', 'DESC')
            ->getAll();
    }

    public function createInvitation(array $data): ?array
    {
        $today = date("Y-m-d H:i:s");

        $result = $this->queryBuilder
            ->columns(['sender_username', 'receiver_username', 'status', 'created_at'])
            ->params([$data['sender_username'], $data['receiver_username'], 'pending', $today])
            ->insert();

        if ($result) {
            $invitationId = Database::lastInsertId();
            return [
                'id' => $invitationId,
                'date_created' => $today
            ];
        }

        return null;
    }

    public function getCountOfSentInvitations(string $username): int
    {
        $result = $this->queryBuilder
            ->columns(['COUNT(*) AS request_count'])
            ->where('sender_username', $username)
            ->andWhere('status', 'pending')
            ->first();

        return $result ? (int)$result['request_count'] : 0;
    }

    public function getCountOfReceivedInvitations(string $username): int
    {
        $result = $this->queryBuilder
            ->columns(['COUNT(*) AS request_count'])
            ->where('receiver_username', $username)
            ->andWhere('status', 'pending')
            ->first();

        return $result ? (int)$result['request_count'] : 0;
    }

    public function findInvitation(string $senderUsername, string $receiverUsername): ?array
    {
        return $this->queryBuilder
            ->columns(['id', 'sender_username', 'receiver_username', 'status', 'created_at'])
            ->where('sender_username', $senderUsername)
            ->andWhere('receiver_username', $receiverUsername)
            ->andWhere('status', 'pending')
            ->first();
    }
}