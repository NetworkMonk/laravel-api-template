<?php

namespace App\Http\Middleware;

use Closure;
use \Firebase\JWT\JWT;
use Exception;

class VerifyJWTRefresh
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Get JWT configuration
        $key = config('app.jwt_key', '');
        $refreshKey = config('app.jwt_refresh_key', '');
        $algo = config('app.jwt_algo', '');
        $url = config('app.url', '');
        $cookieName = config('app.jwt_refresh_cookie_name', '');

        if (($key == '') || ($refreshKey == '') || ($algo == ''))
        {
            abort(500, 'JWT not configured');
        }

        if (!isset($_COOKIE[$cookieName]))
        {
            abort(401, 'Authorization invalid');
        }

        // Attempt to load the bearer token from the request
        $jwt = $_COOKIE[$cookieName];
        $jwt = \str_replace('Bearer ', '', $jwt);

        if (!$jwt)
        {
            abort(401, 'Authorization invalid');
        }

        try {
            $decoded = JWT::decode($jwt, $refreshKey, array($algo));
        } catch(Exception $e) {
            abort(401, 'Authorization invalid');
        }
        if (!$decoded)
        {
            abort(401, 'Authorization invalid');
        }

        $decoded = (array) $decoded;

        // Additional checks
        
        // Ensure issuer is correct
        if (!isset($decoded['iss']) || ($decoded['iss'] !== $url))
        {
            abort(401, 'Authorization invalid');
        }

        // Ensure audience is correct
        $audCmp = $url . '/refresh';
        if (!isset($decoded['aud']) || ($decoded['aud'] !== $audCmp))
        {
            abort(401, 'Authorization invalid');
        }

        // All authorized, attach the token to the request
        $request->refreshToken = $decoded;

        return $next($request);
    }
}
