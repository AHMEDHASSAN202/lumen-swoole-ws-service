<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('chat/get-users-and-groups', [
    'middleware' => ['auth', 'permissions:chats-browse'],
    'uses' => 'ChatController@getUsersAndGroups'
]);

$router->get('chat/get-messages', [
    'middleware' => ['auth', 'permissions:chats-browse'],
    'uses' => 'ChatController@getMessages'
]);
