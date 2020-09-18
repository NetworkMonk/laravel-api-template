<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Firebase\JWT\JWT;
use \Carbon\Carbon;

class Refresh extends Controller
{

    /**
     * Create a new auth token using the available refresh token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Create a new authorization token from the refresh token
        if (!isset($request->refreshToken))
        {
            abort(401, 'Authorization invalid');
        }

        $refreshToken = $request->refreshToken;

        // Attempt to load user from token subject
        $user = \App\User::where('uuid', '=', $refreshToken['sub'])->first();
        if (!$user)
        {
            abort(401, 'Authorization invalid');
        }

        // Is user account enabled
        if (!$user->enabled)
        {
            abort(401, 'Authorization invalid');
        }
        $key = config('app.jwt_key', '');
        $algo = config('app.jwt_algo', '');
        $url = config('app.url', '');

        if (($key == '') || ($algo == ''))
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
