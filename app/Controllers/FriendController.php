<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\FriendService;
use App\Models\User;
use App\Services\FriendRenderService;

class FriendController extends Controller
{
    public function __construct(
        private FriendService $friendService = new FriendService(),
    )
    {
        parent::__construct();
    }

    public function index(): Response
    {
        
        $user = $this->auth();

        $friendList = $this->friendService->getUserFriendList($user['username']);
        $sentInvitations = $this->friendService->getSentFriendInvitations($user['username']);
        $receivedInvitations = $this->friendService->getFriendInvitations($user['username']);

        if(count($friendList) === 0 && count($receivedInvitations) === 0){
            $searchUrlParam = $this->request->input('search', '');
            return $this->redirect('/add-friends/find' . (!empty($searchUrlParam) ? '?search=' . urlencode($searchUrlParam) : ''));
        }
        
        $data = [
            'title' => 'Add Friends',
            'user' => $user,
            'friendService' => $this->friendService,
            'friendList' => $friendList,
            'receivedInvitations' => $receivedInvitations,
            'sentInvitations' => $sentInvitations,
            'customStyles' =>
                '#container { max-width: 700px; margin: clamp(20px, 4vw, 50px) auto; }'
        ];

        return $this->view('friends/index', $data);
    }

    public function find(): Response
    {
        $user = $this->auth();

        $data = [
            'title' => 'Add Friends | Find',
            'user' => $user,
            'friendService' => $this->friendService,
            'customStyles' =>
                '#container { max-width: 700px; margin: clamp(20px, 4vw, 50px) auto; }'
        ];

        return $this->view('friends/find', $data);
    }

    public function search(): Response
    {
        
        $searchTerm = trim($this->request->input('search', ''));

        $isAddFriendsPage = filter_var($this->request->input('isAddFriendsPage', false), FILTER_VALIDATE_BOOLEAN);

        $user = $this->auth();
        
        // Apply search filter if provided
        if (empty($searchTerm)) {
            if (!$isAddFriendsPage) {
                return $this->json([
                    'status' => 'success',
                    'message' => 'No search term provided'
                ], 200);
            } else {
                $friendList = $this->friendService->getUserFriendList($user['username']);
                $receivedInvitations = $this->friendService->getFriendInvitations($user['username']);
                ob_start();
                require __DIR__ . '/../../views/components/friends-results.php';
                $tableHtml = ob_get_clean();

                return $this->json([
                    'status' => 'success',
                    'message' => 'No search term provided',
                    'html' => $tableHtml,
                    'totalRows' => count($friendList) + count($receivedInvitations)
                ], 200);
            }
        }


        // Get all users
        list($allUsers, $friendList, $newFriends, $receivedInvitations) = $this->friendService->searchUsersAndCategorize($user['username'], $searchTerm);

        // Generate HTML for table rows only
        ob_start();
        require __DIR__ . '/../../views/components/friends-results.php';
        $tableHtml = ob_get_clean();
                                
        return $this->json([
            'status' => 'success',
            'message' => 'Users loaded successfully',
            'html' => $tableHtml,
            'totalRows' => count($allUsers)
        ], 200);
    }

    public function addFriend(): Response
    {
        $user = $this->auth();
        $targetUsername = trim($this->request->input('target_username', ''));

        if (empty($targetUsername)) {
            return $this->json([
                'status' => 'error',
                'message' => 'No username provided'
            ], 400);
        }

        $existingFriend = $this->friendService->findExistingFriend($user['username'], $targetUsername);
        if ($existingFriend !== null) {
            return $this->json([
                'status' => 'error',
                'message' => 'You are already friends with this user'
            ], 400);
        }

        try {
            $result = $this->friendService->addFriendAndSendInvitation($user['username'], $targetUsername);

            if($result !== null){
                return $this->json([
                    'status' => 'success',
                    'message' => 'Friend request sent successfully',
                    'data' => $result
                ], 200);
            }
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
        
        return $this->json([
            'status' => 'error',
            'message' => 'Something went wrong while adding friend to database'
        ], 400);
    }

    public function removeFriend(): Response
    {
        $user = $this->auth();
        $targetUsername = trim($this->request->input('target_username', ''));

        if (empty($targetUsername)) {
            return $this->json([
                'status' => 'error',
                'message' => 'No username provided'
            ], 400);
        }

        $existingFriend = $this->friendService->findExistingFriend($user['username'], $targetUsername);
        if ($existingFriend === null) {
            return $this->json([
                'status' => 'error',
                'message' => 'You are not friends with this user'
            ], 400);
        }

        try {
            $this->friendService->removeFriend($user['username'], $targetUsername);

            return $this->json([
                'status' => 'success',
                'message' => 'Friend removed successfully'
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function declineInvitation(): Response
    {
        $user = $this->auth();
        $targetUsername = trim($this->request->input('target_username', ''));

        if (empty($targetUsername)) {
            return $this->json([
                'status' => 'error',
                'message' => 'No username provided'
            ], 400);
        }

        $existingInvitation = $this->friendService->findExistingInvitation($targetUsername, $user['username']);
        if ($existingInvitation === null) {
            return $this->json([
                'status' => 'error',
                'message' => 'No pending invitation from this user'
            ], 400);
        }

        try {
            $this->friendService->declineInvitation($targetUsername, $user['username']);

            return $this->json([
                'status' => 'success',
                'message' => 'Invitation declined successfully'
            ], 200);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}