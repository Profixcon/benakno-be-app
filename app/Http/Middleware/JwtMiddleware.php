<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $authModel = (object) JWTAuth::parseToken()->getPayload()->get('user');
            // Set the authenticated user in the request
            $request->user = $authModel;
            return $next($request);
        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->error(['Invalid TOKEN'], 403);
            } elseif ($e instanceof TokenExpiredException) {
                return response()->error(['Token already expired'], 403);
            } else {
                return response()->error(['Please login first. ' . $e->getMessage()], 403);
            }
        }
    }
}
