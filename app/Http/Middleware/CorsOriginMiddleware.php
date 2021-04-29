<?php

namespace App\Http\Middleware;

use Closure;

class CorsOriginMiddleware
{
    /**
     * Handle CORS 
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $headers = [
            'Access-Control-Allow-Origin'       =>  env('MAIN_DOMAIN'),
            'Access-Control-Allow-Credentials'  =>  'true',
            'Access-Control-Allow-Methods'      =>  'GET, POST, PATCH, PUT, DELETE, OPTIONS, HEAD',
            'Access-Control-Allow-Headers'      =>  '*',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json('{"method":"OPTIONS"}', 200, $headers);
        }

        $response = $next($request);

        foreach($headers as $key => $value) {
            $response->header($key, $value);
        }

        return $response;
    }
}
