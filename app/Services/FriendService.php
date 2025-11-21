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
                return null;
            }

            $this->addFriend($username, $targetUsername);

            $existingIncomingFriend = $this->findExistingFriend($targetUsername, $username);
            $existingIncomingInvitation = $this->friendInvitation->findInvitation($targetUsername, $username);
            $existingOutgoingInvitation = $this->friendInvitation->findInvitation($username, $targetUsername);
            if ($existingIncomingFriend === null && $existingIncomingInvitation === null && $existingOutgoingInvitation === null) {
                $this->sendInvitation($username, $targetUsername);
            }

            if($existingIncomingInvitation !== null){
                $this->friendInvitation->deleteInvitation($existingIncomingInvitation['invitation_id']);
            }

            $this->friendList->commit();

            return ['status' => 'success'];
        } catch (\Exception $e) {
            $this->friendList->rollback();
            //throw $e;
            return null;
        }
    }

    public function removeFriend(string $username, string $targetUsername): void
    {
        $this->friendList->beginTransaction();

        try {
            $this->friendList->deleteFriend($username, $targetUsername);
            $existingOutgoingInvitation = $this->friendInvitation->findInvitation($username, $targetUsername);
            if ($existingOutgoingInvitation !== null) {
                $this->friendInvitation->deleteInvitation($existingOutgoingInvitation['invitation_id']);
            }
            $this->friendList->commit();
        } catch (\Exception $e) {
            $this->friendList->rollback();
            throw $e;
        }
    }

    public function getUserFriendList(string $username): array
    {
        $allUsers = User::findProfileForAll();
        $allUsers = array_filter($allUsers, function($u) use ($username) {
            return $u['username'] !== $username;
        });

        $friendList = array_filter($allUsers, function ($friend) use ($username) {
            $existingFriend = $this->findExistingFriend($username, $friend['username']);
            return $existingFriend !== null;
        });

        return $friendList;
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
        $allUsers = User::findProfileForAll();
        $allUsers = array_filter($allUsers, function($u) use ($username) {
            return $u['username'] !== $username;
        });
        $filteredUsers = array_values($this->filterUsers($allUsers, $searchTerm));

        return $filteredUsers;
    }

    public function searchUsersAndCategorize(string $username, string $searchTerm): array
    {
        $allUsers = $this->searchForUsers($username, $searchTerm);

        $friendList = array_filter($allUsers, function ($friend) use ($username) {
            $existingFriend = $this->findExistingFriend($username, $friend['username']);
            return $existingFriend !== null;
        });

        $newFriends = array_filter($allUsers, function ($friend) use ($username) {
            $existingFriend = $this->findExistingFriend($username, $friend['username']);
            return $existingFriend === null;
        });

        $newFriends = array_map(function($friend) use ($username) {
            $hasAddedUser = $this->friendList->findFriendByUsername($friend['username'], $username);
            $friend['has_added_user'] = $hasAddedUser !== null;
            return $friend;
        }, $newFriends);

        $receivedInvitations = [];

        return [$allUsers, $friendList, $newFriends, $receivedInvitations];
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
        $filteredUsers = array_filter($users, function($user) use ($searchLower) {
            $name = strtolower($user['name'] ?? '');
            $username = strtolower($user['username'] ?? '');
            
            return strpos($name, $searchLower) !== false ||
                   strpos($username, $searchLower) !== false;
        });

        usort($filteredUsers, function($a, $b) use ($searchLower) {
            $aName = strtolower($a['name'] ?? '');
            $aUsername = strtolower($a['username'] ?? '');
            $bName = strtolower($b['name'] ?? '');
            $bUsername = strtolower($b['username'] ?? '');

            $aNamePos = strpos($aName, $searchLower);
            $aUsernamePos = strpos($aUsername, $searchLower);
            $bNamePos = strpos($bName, $searchLower);
            $bUsernamePos = strpos($bUsername, $searchLower);

            $aPos = ($aNamePos !== false) ? $aNamePos : (($aUsernamePos !== false) ? $aUsernamePos + 1000 : PHP_INT_MAX);
            $bPos = ($bNamePos !== false) ? $bNamePos : (($bUsernamePos !== false) ? $bUsernamePos + 1000 : PHP_INT_MAX);

            return $aPos - $bPos;
        });
        
        return $filteredUsers;
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

    public function findExistingInvitation(string $username, string $targetUsername): ?array
    {
        return $this->friendInvitation->findInvitation($username, $targetUsername);
    }

    public function declineInvitation(string $targetUsername, string $username): void
    {
        $existingInvitation = $this->friendInvitation->findInvitation($targetUsername, $username);
        if ($existingInvitation !== null) {
            $this->friendInvitation->deleteInvitation($existingInvitation['invitation_id']);
        }
    }
}
