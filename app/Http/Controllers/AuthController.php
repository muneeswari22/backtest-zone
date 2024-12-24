<?php

namespace App\Http\Controllers;

use App\Models\PersistentLogins;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helper\LogHelper;
use Illuminate\Support\Str;


class AuthController extends Controller
{

    public function login(Request $request)
    {

        $resVal = array();
        $resVal['success'] = false;
        $resVal["code"] = 1;
        $resVal['message'] = "Please check your email/password";
        $userName = $request->input('userName', '');
        $password = $request->input('password', '');
        $web = $request->input('web', '');
        $app = $request->input('app', '');

        if((empty($web) || $web == null) && (empty($app) || $app == null)) {
            $resVal['success'] = false;
            $resVal['message'] = "Access mode required";
            return $resVal;
        }


        $user = User::where('username', $userName)->where('is_active', 1)->first();
        if (!empty($user) && $user != null) {

            if ($app == 1 && ($user->is_app_login == 0 || $user->is_app_login == null)) {
                $resVal['success'] = false;
                $resVal['message'] = "You are not allowed to login";
                return $resVal;
            } else {
                if ($web == 1 && ($user->is_web_login == 0 || $user->is_web_login == null)) {
                    $resVal['success'] = false;
                    $resVal['message'] = "You are not allowed to login";
                    return $resVal;
                }
            }

            if (!Hash::check($password, $user->password)) {
                $resVal['success'] = false;
                $resVal['message'] = "Password is invalid";
                return $resVal;
            }

            $resVal['success'] = true;
            $usersInfo = $this->userInfo($userName);
            $resVal['userDetails'] = $usersInfo;
            Auth::login($user);
            $persistentToken = $this->savePersistentLogins($user);
            $resVal['apiToken'] = $persistentToken;
            $resVal['message'] = "Login successfull!";
        } else {
            $resVal['success'] = false;
            $resVal['message'] = "Username doesnot exits";
            return $resVal;
        }

        return $resVal;
    }
    public static function userInfo($userName){
        $userDetails = DB::table('tbl_user as u')
            ->leftjoin('tbl_role as r','r.id','=','u.role_id')
            ->select('u.id','u.company_id','fname', 'lname', 'email', 'u.role_id','r.name as role_name', 'username', 'phone', 'otp', 'employee_id','is_app_login','is_web_login', 'user_code',
            'u.is_active','u.created_by','u.updated_by', 'u.comments','user_code')
            ->where('username', $userName)->where('u.is_active', 1)->first();
        return $userDetails;
    }
    public function getCurrentUser()
    {
        $currentuser = Auth::user();
        $resVal['success'] = true;
        $resVal["code"] = 1;
        $resVal['userDetails'] = $this->userInfo($currentuser->username);
        return $resVal;
    }

    public function logout(Request $request)
    {
        $token = $request->header('apiToken', '');
        $resVal = array();
        $resVal["code"] = 1;
        if ($token) {
            if (Auth::check() && !empty($token)) {
                $tokenDet = PersistentLogins::where('token', $token)->get();
                if (!$tokenDet->isEmpty()) {
                    $persistentLogin = $tokenDet->first();
                    $persistentLogin->delete();
                    Auth::guard()->logout();
                }
                $resVal['message'] = "Logged out successfully";
                $resVal['success'] = true;
            } else {
                $resVal['message'] = "Failed to logout";
                $resVal['success'] = false;
            }
        } else {
            $resVal['message'] = "Logged unsuccessful";
            $resVal['success'] = false;
        }
        return $resVal;
    }

    public static function savePersistentLogins($user)
    {
        do {
            $token = (string) Str::orderedUuid();
            $persistentLoginCollection = PersistentLogins::where('token', '=', $token)->get();
            $persistentLoginCount = count($persistentLoginCollection);
        } while ($persistentLoginCount > 0);

        $persistentLogin = new PersistentLogins;
        $persistentLogin->username = $user->email;
        $persistentLogin->token = $token;
        $persistentLogin->expiry_date = Carbon::now();
        $persistentLogin->company_id = $user->company_id;
        $persistentLogin->save();

        return $token;
    }


    public function imageBasicInfo(Request $request)
    {

        $config = config('app');

        $resVal = array();
        $resVal['success'] = true;
        $resVal["code"] = 1;

        $resVal['IMAGE_CLOUD_URL'] = $config['IMAGE_CLOUD_URL'];
        $resVal['BUCKET_NAME'] = $config['BUCKET_NAME'];
        $resVal['FOLDER_NAME'] = $config['FOLDER_NAME'];

        return $resVal;
    }
}
