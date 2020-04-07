<?php

namespace App\Http\Middleware;

use Closure;
use App\Client;
use App\Oauth\Oauth;

class AccessTokenVerifier
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $token = $request->header('Authorization');
        $isValid = (new Oauth())->verifyAccessToken($token);

        if (!$isValid) {
            return response()->json(['message' => "Invalid Request"], 401);
        }

        return $next($request);
    }
}
