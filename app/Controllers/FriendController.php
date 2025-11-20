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
            return $this->redirect('/add-friends/find');
        }
        
        $data = [
            'title' => 'Add Friends',
            'user' => $user,
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

    public function search(): void
    {
        
        $searchTerm = trim($this->request->input('search', ''));

        $isAddFriendsPage = filter_var($this->request->input('isAddFriendsPage', false), FILTER_VALIDATE_BOOLEAN);

        $user = $this->auth();
        
        // Apply search filter if provided
        if (empty($searchTerm)) {
            if (!$isAddFriendsPage) {
                $this->response->json([
                    'status' => 'success',
                    'message' => 'No search term provided'
                ], 200)->send();
            } else {
                $friendList = $this->friendService->getUserFriendList($user['username']);
                $receivedInvitations = $this->friendService->getFriendInvitations($user['username']);
                ob_start();
                $type = 'friend';
                require __DIR__ . '/../../views/components/friends-results.php';
                $tableHtml = ob_get_clean();

                header('Content-Type: application/json');
                header('Cache-Control: no-cache, must-revalidate');

                $this->response->json([
                    'status' => 'success',
                    'message' => 'No search term provided',
                    'html' => $tableHtml,
                    'totalRows' => count($friendList) + count($receivedInvitations)
                ], 200)->send();
            }
            exit;
        }


        // Get all users
        $allUsers = $this->friendService->searchForUsers($user['username'], $searchTerm);

        $friendList = array_filter($allUsers, function ($friend) use ($user) {
            $existingFriend = $this->friendService->findExistingFriend($user['username'], $friend['username']);
            return $existingFriend !== null;
        });

        $newFriends = array_filter($allUsers, function ($friend) use ($user) {
            $existingFriend = $this->friendService->findExistingFriend($user['username'], $friend['username']);
            return $existingFriend === null;
        });

        $receivedInvitations = [];

        // Generate HTML for table rows only
        ob_start();
        $type = 'search';
        require __DIR__ . '/../../views/components/friends-results.php';
        $tableHtml = ob_get_clean();
                
        // Clear any output buffering first
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers and output JSON directly
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        
        $jsonData = [
            'status' => 'success',
            'message' => 'Users loaded successfully',
            'html' => $tableHtml,
            'totalRows' => count($allUsers)
        ];
        
        echo json_encode($jsonData);
        flush();
        exit;
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
                'message' => 'A pending friend request already exists between you and this user'
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

}