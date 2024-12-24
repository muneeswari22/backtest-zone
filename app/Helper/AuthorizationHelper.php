<?php

namespace App\Helper;

use App\Models\PersistentLogins;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthorizationHelper
{

    public static function getUser($request)
    {

        $apiToken = $request->header('apiToken');

        if (!empty($apiToken) && $apiToken != null) {
            $persistentLogin = DB::table('persistent_logins')->where('token', '=', $apiToken)
                ->first();

            if (!empty($persistentLogin->username) && $persistentLogin->username != null) {
                $user = User::where('username', '=', $persistentLogin->username)->where('is_active','=',value: 1)->first();
                if (!empty($user))
                    return $user;
            }
        }
        return null;
    }

}
