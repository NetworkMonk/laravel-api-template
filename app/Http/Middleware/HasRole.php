<?php

namespace App\Http\Middleware;

use Closure;

class HasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (!$request->authorizationToken || !isset($request->authorizationToken['roles']))
        {
            abort(403, 'Access Denied');
        }

        if (!$role || ($role == ''))
        {
            abort(403, 'Access Denied');
        }

        if (!\in_array($role, $request->authorizationToken['roles']))
        {
            abort(403, 'Access Denied');
        }

        return $next($request);
    }
}
