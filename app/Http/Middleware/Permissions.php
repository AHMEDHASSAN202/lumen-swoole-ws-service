<?php

namespace App\Http\Middleware;

use App\Repositories\AuthRepository;
use Closure;
use Illuminate\Http\Request;

class Permissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  $permissions
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $me = $request->user();
        if (!$me->hasPermissions($permissions)) {
            return response('permission denied.', 403);
        }
        return $next($request);
    }
}
