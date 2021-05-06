<?php

namespace App\Http\Controllers;


class UsersController extends Controller
{
    public function getOnlineUsers()
    {
        $onlineUsersIds = app(\App\Repositories\WebSocketRepository::class)->getOnlineUsers();
        $onlineUsers = app(\App\Repositories\UserRepository::class)->getUsersById($onlineUsersIds);

        return response()->json(compact('onlineUsers'));
    }
}