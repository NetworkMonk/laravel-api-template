<?php

namespace App\Http\Middleware;

use Closure;

class Recaptcha
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
        // Check supplied data
        $request->validate([
            'recaptcha' => 'required',
        ]);

        $post_data = http_build_query(
            array(
                'secret' => config('app.recaptcha_secret_key', ''),
                'response' => $request->input('recaptcha', ''),
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $post_data
            )
        );
        $context  = stream_context_create($opts);
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $result = json_decode($response);
        if (!$result->success) {
            abort(401, 'Invalid recaptcha');
        }

        return $next($request);
    }
}
