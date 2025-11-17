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

        $userFriendships = $this->friendService->getUserFriendships($user['username']);
        $userFriendRequests = $this->friendService->getUserFriendRequests($user['username']);
        $sentFriendRequests = $this->friendService->getSentFriendRequests($user['username']);

        if(count($userFriendships) === 0 && count($userFriendRequests) === 0 && count($sentFriendRequests) === 0){
            return $this->redirect('/add-friends/find');
        }
        
        $data = [
            'title' => 'Add Friends',
            'user' => $user,
            'friendships' => $userFriendships,
            'friendRequests' => $userFriendRequests,
            'sentFriendRequests' => $sentFriendRequests
        ];

        return $this->view('friends/index', $data);
    }

    public function find(): Response
    {
        $user = $this->auth();

        $data = [
            'title' => 'Add Friends | Find',
            'user' => $user,
            'customStyles' =>
                '#container { max-width: 700px; margin: clamp(20px, 4vw, 50px) auto; }'
        ];

        return $this->view('friends/find', $data);
    }

    public function search(): void
    {
        
        $searchTerm = trim($this->request->input('search', ''));
        
        // Apply search filter if provided
        if (empty($searchTerm)) {
            $this->response->json([
                'status' => 'success',
                'message' => 'No search term provided'
            ], 200)->send();
            return;
        }

        $user = $this->auth();

        // Get all users
        $allUsers = FriendService::searchForRequests($user['username'], $searchTerm);        

        // Generate HTML for table rows only
        $tableHtml = FriendRenderService::generateUserSearchResults($allUsers);
                
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

    public function sendFriendRequest(): Response
    {
        $user = $this->auth();
        $targetUsername = trim($this->request->input('target_username', ''));

        if (empty($targetUsername)) {
            return $this->json([
                'status' => 'error',
                'message' => 'No username provided'
            ], 400);
        }

        $existingRequest = $this->friendService->findExistingFriendRequest($user['username'], $targetUsername);
        if ($existingRequest !== null) {
            return $this->json([
                'status' => 'error',
                'message' => 'A pending friend request already exists between you and this user'
            ], 400);
        }

        $result = $this->friendService->sendFriendRequest($user['username'], $targetUsername);

        if($result !== null){
            return $this->json([
                'status' => 'success',
                'message' => 'Friend request sent successfully',
                'data' => $result
            ], 200);
        }
        
        return $this->json([
            'status' => 'error',
            'message' => 'Cannot send friend request to yourself'
        ], 400);
    }
}