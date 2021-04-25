<?php

namespace App\Http\Controllers;

use SwooleTW\Http\Websocket\Facades\Room;
use SwooleTW\Http\Websocket\Facades\Websocket;
use SwooleTW\Http\Table\Facades\SwooleTable as Table;

class ConnectController extends Controller
{
    const GLOBAL_ROOM = 'onlineUsers';

    public function connect($websocket, $request)
    {
        if ($user = $request->user()) {
            Websocket::loginUsing($user);
            Websocket::join(self::GLOBAL_ROOM);
            app(\App\Repositories\WebSocketRepository::class)->addToUsersTable($user,  $websocket->getSender());
        }
    }

    public function disConnect()
    {
        Websocket::leave(self::GLOBAL_ROOM);
        app(\App\Repositories\WebSocketRepository::class)->removeFromUsersTable(Websocket::getUserId());
    }
}
