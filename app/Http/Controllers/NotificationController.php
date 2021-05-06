<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class NotificationController extends Controller
{
    public function notifyUsers(Request $request)
    {
        $result = app(\App\Repositories\WebSocketRepository::class)->notifyUsers($request);

        if ($result instanceof MessageBag) {
            return response()->json($result->messages(), 400);
        }

        return response()->json(['status' => 'OK'], 200);
    }
}