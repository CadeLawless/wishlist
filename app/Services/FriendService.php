<?php

namespace App\Services;

use App\Models\Friendship;
use App\Models\FriendRequest;
use App\Models\User;

class FriendService
{
    public function __construct(
        private Friendship $friendship = new Friendship(),
        private FriendRequest $friendRequest = new FriendRequest(),
    ) {}

    public function createFriendship(string $username, array $data): ?array
    {
        $data['username_1'] = $username;
        return $this->friendship->createFriendship($data);
    }

    public function getUserFriendships(string $username): array
    {
        return $this->friendship->getFriendshipsByUsername($username);
    }

    public function getUserFriendRequests(string $username): array
    {
        return $this->friendRequest->getReceivedFriendRequestsByUsername($username);
    }

    public function getSentFriendRequests(string $username): array
    {
        return $this->friendRequest->getSentFriendRequestsByUsername($username);
    }
}
