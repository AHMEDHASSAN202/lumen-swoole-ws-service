<?php

namespace App\Services;

use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebSocketHandler implements WebSocketHandlerInterface
{
    public function __construct ()
    {
        // The constructor cannot be omitted even if it is empty
    }

    // Triggered when the connection is established
    public function onOpen (Server $server, Request $request)
    {
        var_dump('onOpen');
    }

    // Triggered when a message is received
    public function onMessage (Server $server, Frame $frame)
    {
        var_dump('onMessage');
    }

    // Triggered when the connection is closed
    public function onClose (Server $server, $fd, $reactorId)
    {
        var_dump('onClose');
    }
}