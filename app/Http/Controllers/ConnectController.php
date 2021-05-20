<?php

namespace App\Http\Controllers;

use SwooleTW\Http\Websocket\Facades\Room;
use SwooleTW\Http\Websocket\Facades\Websocket;

class ConnectController extends Controller
{
    /**
     * Main Room For Online Users
     *
     */
    const GLOBAL_ROOM = 'onlineUsers';

    /**
     * When websocket connect
     * - we will login current user in this websocket
     * - join current user to main room (onlineUsers)
     * - add user_id and websocket to users table swoole
     * - emit onlineUsers event with online users
     *
     * @param $websocket
     * @param $request
     */
    public function connect($websocket, $request)
    {
        if ($user = $request->user()) {
            Websocket::loginUsing($user);
            Websocket::join(self::GLOBAL_ROOM);
            app(\App\Repositories\WebSocketRepository::class)->addToUsersTable($user,  $websocket->getSender());
            $this->joinToGroups($user->user_id);
            $this->emitOnlineUsers();
            $this->emitTotalUnreadMessages();
        }

        $this->disconnectDatabase();
    }

    /**
     * When disconnect
     * - leave main room
     * - remove this user from online users swoole table
     * - emit onlineUsers event with new online users
     */
    public function disConnect()
    {
        app(\App\Repositories\WebSocketRepository::class)->removeFromUsersTable(Websocket::getUserId());
        $this->leaveAllRooms();
        $this->emitOnlineUsers();

        $this->disconnectDatabase();
    }

    /**
     * Emit onlineUsers Event
     * With Current Online Users
     *
     */
    private function emitOnlineUsers()
    {
        $onlineUsers = app(\App\Repositories\WebSocketRepository::class)->getOnlineUsers();

        Websocket::to(self::GLOBAL_ROOM)->emit('onlineUsers', $onlineUsers);
    }

    /**
     * Join Current User To Own Groups
     *
     * @param $user_id
     * @return void
     */
    private function joinToGroups($user_id)
    {
        $groupsIds = app(\App\Repositories\ChatRepository::class)->getUserGroups($user_id);

        Websocket::join($groupsIds);
    }

    /**
     * Leave All Rooms When disconnect
     *
     */
    private function leaveAllRooms()
    {
        $rooms = Room::getRooms(Websocket::getSender());

        Websocket::leave($rooms);
    }

    /**
     * Emit total unread messages event
     * 
     */
    private function emitTotalUnreadMessages()
    {
        return app(\App\Repositories\WebSocketRepository::class)->emitTotalUnreadMessage();
    }
}
