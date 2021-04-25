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
}