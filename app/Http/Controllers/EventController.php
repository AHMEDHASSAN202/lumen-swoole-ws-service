<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class EventController extends Controller
{
    public function emit(Request $request)
    {
        $result = app(\App\Repositories\WebSocketRepository::class)->emit($request);

        if ($result instanceof MessageBag) {
            return response()->json($result->messages(), 400);
        }

        return response()->json(['status' => 'OK'], 200);
    }
}