<?php

namespace App\Services;

use App\Models\FriendList;
use App\Models\FriendInvitation;
use App\Models\User;

class FriendService
{
    public function __construct(
        private FriendList $friendList = new FriendList(),
        private FriendInvitation $friendInvitation = new FriendInvitation(),
        private User $user = new User(),
    ) {}

    public function addFriend(string $username, string $targetUsername): ?array
    {
        $result = $this->friendList->addFriend([
            'username' => $username,
            'friend_username' => $targetUsername
        ]);

        if($result === null){
            throw new \Exception("Failed to add friend");
        }

        return $result;
    }

    public function addFriendAndSendInvitation(string $username, string $targetUsername): ?array
    {
        try {
            $this->friendList->beginTransaction();

            $existingOutgoingFriend = $this->findExistingFriend($username, $targetUsername);
            if ($existingOutgoingFriend !== null) {
                $this->friendList->rollback();
                return null;
            }

            $this->addFriend($username, $targetUsername);

            $existingIncomingFriend = $this->findExistingFriend($targetUsername, $username);
            $existingIncomingInvitation = $this->friendInvitation->findInvitation($targetUsername, $username);
            $existingOutgoingInvitation = $this->friendInvitation->findInvitation($username, $targetUsername);
            if ($existingIncomingFriend === null && $existingIncomingInvitation === null && $existingOutgoingInvitation === null) {
                $this->sendInvitation($username, $targetUsername);
            }

            $this->friendList->commit();

            return ['status' => 'success'];
        } catch (\Exception $e) {
            $this->friendList->rollback();
            return null;
        }
    }

    public function getUserFriendList(string $username): array
    {
        return $this->friendList->getFriendsByUsername($username);
    }

    public function getFriendInvitations(string $username): array
    {
        $receivedInvitations = $this->friendInvitation->getReceivedInvitationsByUsername($username);

        $receivedInvitations = $this->addNameAndProfilePictureToRequests($receivedInvitations, 'received');

        return $receivedInvitations;

    }

    public function getSentFriendInvitations(string $username): array
    {
        $sentInvitations = $this->friendInvitation->getSentInvitationsByUsername($username);

        $sentInvitations = $this->addNameAndProfilePictureToRequests($sentInvitations, 'sent');

        return $sentInvitations;
    }

    public function sendInvitation(string $username, string $targetUsername): ?array
    {
        $result = $this->friendInvitation->createInvitation([
            'sender_username' => $username,
            'receiver_username' => $targetUsername
        ]);

        if($result === null){
            throw new \Exception("Failed to send friend invitation");
        }

        return $result;
    }

    public function searchForUsers(string $username, string $searchTerm): array
    {
        $allUsers = User::findNameAndEmailForAll();
        $allUsers = array_filter($allUsers, function($u) use ($username) {
            return $u['username'] !== $username;
        });
        $filteredUsers = array_values($this->filterUsers($allUsers, $searchTerm));

        return $filteredUsers;
    }

    public function findExistingFriend(string $username, string $friendUsername): ?array
    {
        return $this->friendList->findFriendByUsername($username, $friendUsername);
    }

        /**
     * Filter users by search term
     */
    public function filterUsers(array $users, string $searchTerm): array
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

    public function getNameAndProfilePicture(string $username): ?array
    {
        return $this->user->getNameAndProfilePictureByUsername($username);
    }

    public function getNumberOfFriends(string $username): int
    {
        return $this->friendList->getCountOfFriendsByUsername($username);
    }

    public function getNumberOfReceivedRequests(string $username): int
    {
        return $this->friendInvitation->getCountOfReceivedInvitations($username);
    }

    public function getNumberOfSentRequests(string $username): int
    {
        return $this->friendInvitation->getCountOfSentInvitations($username);
    }

    public function addNameAndProfilePictureToRequests(array $requests, string $type): array
    {
        return array_map(function($request) use ($type) {
            $userType = $type === 'sent' ? 'receiver' : 'sender';
            $userInfo = $this->getNameAndProfilePicture($request[$userType . '_username']);
            $request['username'] = $request[$userType . '_username'];
            $request['name'] = $userInfo['name'];
            $request['profile_picture'] = $userInfo['profile_picture'];
            return $request;
        }, $requests);
    }
}
