<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\FriendService;

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
            'user' => $user
        ];

        return $this->view('friends/find', $data);
    }
}