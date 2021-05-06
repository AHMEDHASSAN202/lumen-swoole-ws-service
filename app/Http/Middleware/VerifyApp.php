<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyApp
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
        $secretKey = $request->header('x-app-secret-key');

        if (!$secretKey) {
            return response('app secret key is required', 403);
        }

        if ($secretKey != env('APP_SECRET_KEY')) {
            return response('app secret key is invalid', 403);
        }

        return $next($request);
    }
}
