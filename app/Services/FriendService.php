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

    public function sendFriendRequest(string $username, string $targetUsername): ?array
    {
        return $this->friendRequest->createFriendRequest([
            'sender_username' => $username,
            'receiver_username' => $targetUsername
        ]);
    }

    public function findExistingFriendRequest(string $senderUsername, string $receiverUsername): ?array
    {
        return $this->friendRequest->findByUsernames($senderUsername, $receiverUsername);
    }

    public static function searchForRequests(string $username, string $searchTerm): array
    {
        $allUsers = User::findNameAndEmailForAll();
        $allUsers = array_filter($allUsers, function($u) use ($username) {
            return $u['username'] !== $username;
        });
        $filteredUsers = array_values(FriendService::filterUsers($allUsers, $searchTerm));
        $allUsersWithExistingRequest = array_map(function($user) use ($username) {
            $existingRequest = (new FriendRequest())->findByUsernames($username, $user['username']);
            $user['existing_friend_request'] = $existingRequest !== null;
            return $user;
        }, $filteredUsers);

        return $allUsersWithExistingRequest;
    }

        /**
     * Filter users by search term
     */
    public static function filterUsers(array $users, string $searchTerm): array
    {
        if (empty(trim($searchTerm))) {
            return $users;
        }
        
        $searchLower = strtolower(trim($searchTerm));
        return array_filter($users, function($user) use ($searchLower) {
            $name = strtolower($user['name'] ?? '');
            $username = strtolower($user['username'] ?? '');
            
            return strpos($name, $searchLower) !== false ||
                   strpos($username, $searchLower) !== false;
        });
    }

}
