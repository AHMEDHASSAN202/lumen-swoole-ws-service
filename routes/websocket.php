<?php

use SwooleTW\Http\Websocket\Facades\Websocket;

/*
|--------------------------------------------------------------------------
| Websocket Routes
|--------------------------------------------------------------------------
|
| Here is where you can register Websocket events for your application.
|
*/

Websocket::on('connect', 'App\Http\Controllers\ConnectController@connect');

Websocket::on('disconnect', 'App\Http\Controllers\ConnectController@disConnect');

Websocket::on('message', 'App\Http\Controllers\ChatController@onMessage');

Websocket::on('message_files', 'App\Http\Controllers\ChatController@onMessageFiles');

Websocket::on('unread_messages', 'App\Http\Controllers\ChatController@onUnreadMessage');