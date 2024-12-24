<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use App\Helper\AuthorizationHelper;


class Authenticate
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = null;
        $result = array();

            $user = AuthorizationHelper::getUser($request);

            if (!empty($user) && $user != null) {
                Auth::login($user);
                return $next($request);
            } else {
                $result["code"] = 2;
                $result["success"] = FALSE;
                $result["message"] = "Access denied ...";
                return response($result);
            }
        
    }
}
