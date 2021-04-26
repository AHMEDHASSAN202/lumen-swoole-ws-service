<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function getUsersAndGroups(Request $request)
    {
        $usersAndGroups = app(\App\Repositories\ChatRepository::class)->getUsersAndGroups($request);
  
        return response()->json(compact('usersAndGroups'));
    }

    public function getMessages(Request $request)
    {
        $messages = app(\App\Repositories\ChatRepository::class)->getMessages($request);

        return response()->json(['messages' => $messages]);
    }
}