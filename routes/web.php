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
    echo '<pre>';
    print_r(app('db')->table('users')->select('user_id')->get());
    echo '</pre>';
    return $router->app->version();
});

//chat routes
$router->group(['middleware' => 'auth', 'prefix' => 'chat'], function () use ($router) {
    $router->get('get-users-and-groups', ['middleware' => ['permissions:chats-browse'], 'uses' => 'ChatController@getUsersAndGroups']);
    $router->get('get-messages', ['middleware' => ['permissions:chats-browse'], 'uses' => 'ChatController@getMessages']);
});

//Groups Routes
$router->group(['middleware' => 'auth', 'prefix' => 'groups'], function () use ($router) {
    $router->get('', ['middleware' => ['permissions:chat_groups-browse'], 'uses' => 'GroupsController@getAllGroups']);
    $router->get('{group_id}/members', ['middleware' => ['permissions:chat_groups-browse'], 'uses' => 'GroupsController@getGroupMembers']);
    $router->post('', ['middleware' => ['permissions:chat_groups-create'], 'uses' => 'GroupsController@storeGroup']);
    $router->put('{group_id}', ['middleware' => ['permissions:chat_groups-create'], 'uses' => 'GroupsController@updateGroup']);
    $router->delete('{group_id}', ['middleware' => ['permissions:chat_groups-create'], 'uses' => 'GroupsController@deleteGroup']);
});

//get online users
$router->group(['middleware' => 'auth', 'prefix' => 'users'], function () use ($router) {
    $router->get('online', ['middleware' => ['permissions:users-browse'], 'uses' => 'UsersController@getOnlineUsers']);
});

//app api
//this apis send it from backend
$router->group(['middleware' => 'verifyApp'], function () use ($router) {
    //userId => array of user id => notify users
    //data  => notification content
    //we well emit notification event to users with data
    $router->post('notifications', ['uses' => 'NotificationController@notifyUsers']);

    //event_name => string
    //data => content
    //userId => array of user id -> optional default all online users
    //we well emit this event name with data to users
    $router->post('event/emit', ['uses' => 'EventController@emit']);
});