<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Get users and my groups
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersAndGroups(Request $request)
    {
        $usersAndGroups = app(\App\Repositories\ChatRepository::class)->getUsersAndGroups($request);
  
        return response()->json(compact('usersAndGroups'));
    }

    /**
     * Get messages
     * Private message (by user_id) || Group messages by (group_id)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMessages(Request $request)
    {
        $messages = app(\App\Repositories\ChatRepository::class)->getMessages($request);

        return response()->json(['messages' => $messages]);
    }

    /**
     * On Receive New Chat Message
     *
     * @param $websocket
     * @param $data
     */
    public function onMessage($websocket, $data)
    {
        app(\App\Repositories\WebSocketRepository::class)->onMessage($websocket, $data);
    }

    /**
     * On Receive New Chat Files
     *
     * @param $websocket
     * @param $data
     */
    public function onMessageFiles($websocket, $data)
    {
        app(\App\Repositories\WebSocketRepository::class)->onMessage($websocket, $data, 'file');
    }
}