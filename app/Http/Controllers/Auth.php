<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use \Firebase\JWT\JWT;
use \Carbon\Carbon;

class Auth extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Attempt to login with supplied credentials and authenticate current session

        // Validate request data, we need a username and password
        $request->validate([
            'username' => 'required|max:250|email',
            'password' => 'required|min:8|max:250',
        ]);

        // Attempt to load user specified
        $user = \App\User::where('username', '=', $request->input('username'))->first();
        if (!$user)
        {
            abort(400, 'Invalid Credentials');
        }

        // Verify Password
        if (!$user->verifyPassword($request->input('password')))
        {
            abort(400, 'Invalid Credentials');
        }

        // Is user account enabled
        if (!$user->enabled)
        {
            abort(400, 'User Account Not Enabled');
        }

        // TODO We need to check if MFA has been enabled and set the session as appropriate

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

        // Create a JWT that we return with user details
        $payload = array(
            'csrfToken' => \App\Util\UUID::generate(),
            'roles' => explode(',', $user->roles),
            'username' => $user->username,
            'given_name' => $user->first_name,
            'family_name' => $user->last_name,
            'iss' => $url,
            'iat' => Carbon::now()->unix(),
            'exp' => Carbon::now()->addMinutes(15)->unix(),
            'sub' => $user->uuid,
        );
        $jwt = JWT::encode($payload, $key, $algo);

        // Create a JWT refresh token
        $refreshPayload = array(
            'iss' => $url,
            'aud' => $url . '/refresh',
            'iat' => Carbon::now()->unix(),
            'nbf' => Carbon::now()->unix(),
            'exp' => Carbon::now()->addHours(24)->unix(),
            'sub' => $user->uuid,
        );
        $refreshJwt = JWT::encode($refreshPayload, $refreshKey, $algo);

        // Set the refresh token as a http only cookie
        $domain = $cookieDomain !== 'localhost' ? $cookieDomain : false;
        \setcookie($cookieName, 'Bearer ' . $refreshJwt, 0, '/', $domain, $cookieSecure, true);

        // Return auth token
        return response()->json([
            'accessToken' => $jwt,
            'tokenType' => 'bearer',
            'username' => $user->username,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'expires' => $payload['exp'],
        ]);
    }
}
