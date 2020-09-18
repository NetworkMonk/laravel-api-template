<?php

namespace App\Http\Middleware;

use Closure;
use \Firebase\JWT\JWT;
use Exception;

class VerifyJWT
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
        $cookieDomain = config('app.jwt_refresh_cookie_domain', '');
        $cookieSecure = config('app.jwt_refresh_cookie_secure', false);

        if (($key == '') || ($refreshKey == '') || ($algo == ''))
        {
            abort(500, 'JWT not configured');
        }

        // Attempt to load the bearer token from the request
        $jwt = $request->header('Authorization', '');
        if (!$jwt || ($jwt == ''))
        {
            abort(401, 'Authorization invalid');
        }

        $jwt = \str_replace('Bearer ', '', $jwt);

        try {
            $decoded = JWT::decode($jwt, $key, array($algo));
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
        if ($decoded['iss'] !== $url)
        {
            abort(401, 'Authorization invalid');
        }

        // All authorized, attach the token to the request
        $request->authorizationToken = $decoded;

        return $next($request);
    }
}
