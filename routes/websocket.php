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
