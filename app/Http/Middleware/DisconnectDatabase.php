<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DisconnectDatabase
{
    /**
     * Handle User Permissions
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        app('db')->disconnect(env('DB_CONNECTION', 'mysql'));

        return $response;
    }
}
