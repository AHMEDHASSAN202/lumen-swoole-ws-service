<?php

namespace App\Http\Middleware;

use App\Repositories\AuthRepository;
use Closure;
use Illuminate\Http\Request;

class Permissions
{
    /**
     * Handle User Permissions
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  $permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        //get current user
        $me = $request->user();
        //if user not has all permissions params
        if (!$me->hasPermissions($permissions)) {
            return response('permission denied.', 403);
        }
        return $next($request);
    }
}
