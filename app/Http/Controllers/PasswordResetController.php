<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Helper\GeneralHelper;
use App\Helper\MailHelper;
use Illuminate\Support\Facades\Hash;
use App\Models\User;



class PasswordResetController extends Controller
{
    public function mailCodeSet(Request $request)
    {


        $email = $request->input('email');
        $app_login = $request->input('app_login', '');
        $web_login = $request->input('web_login', '');

        $resVal = array();
        $userInfos = DB::table('tbl_user')
            ->select('*')
            ->where('username', '=', $email)
            ->where('is_active', '=', 1)
            ->first();

        if (!empty($userInfos) && $userInfos != null) {
            /* if ($web_login != $userInfos->is_web_login || $app_login != $userInfos->is_app_login) {
                $error['message'] = "You are not allowed to perform this";
                $error['success'] = false;
                return $error;
            } else {
                $error['message'] = "Mode is required";
                $error['success'] = false;
                return $error;
            } */

            do {
                $userCode = GeneralHelper::random_strings(8);
                $userCollection = User::where('user_code', '=', $userCode)->get();
                $userCount1 = count($userCollection);
            } while ($userCount1 > 0);

            DB::table('tbl_user')->where('username', $email)->update(['user_code' => $userCode]);

            $mail_obj = new MailHelper;
            $mail_obj->sendOtp($userInfos, $userCode);
            $resVal['message'] = "Mail sent successfully";
                $resVal['success'] = true;
                return $resVal;
        } else {
            $resVal['message'] = "Please Enter Correct Mail";
                $resVal['success'] = false;
                return $resVal;
        }
    }

    public function checkCode(Request $request, $code)
    {
        $resVal = array();
        $resVal['success'] = true;

        $user = DB::table('tbl_user')
            ->select('*')
            ->where('user_code', '=', $code)
            ->where('is_active',1)
            ->first();

        $resVal['data'] = $user;

        return $resVal;
    }

    public function passwordUpdate(Request $request, $code) {

        $resVal = array();

        $error = array();
        $userDetail = DB::table('tbl_user')
        ->where('user_code', '=', $code)->where('is_active', 1)->first();

        if (!empty($userDetail) && $userDetail != null) {
            $error = array();
            $pattern = "/^(?=.*[0-9])(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{5,}$/";

            if ((preg_match_all($pattern, $request->input('password'), $matches) != true)) {
                $error['password'] = "Must contain at least one number and one uppercase and lowercase letter and a special character";
                $resVal['success'] = false;
                $resVal['message'] = $error['password'];
                return $resVal;
            }
            if ($request->input('password') != $request->input('confirm_password')) {
                $error['password'] = 'Password Mismatch';
                $resVal['success'] = false;
                $resVal['message'] = $error['password'];
                return $resVal;
            }

            if (count($error) > 0) {
                $resVal['success'] = false;
                $resVal['message'] = $error;
                return $resVal;
            }


            $user = User::findOrFail($userDetail->id);
            $user->updated_by = $user->id;
            $user->password = Hash::make($request->input('password'));
            $user->user_code = "";
            $user->save();

            $resVal['success'] = true;
            $resVal['message'] = "Password reset successfully";
            return $resVal;
        } else {
            $resVal['success'] = false;
            $resVal['message'] = "User not found";
            return $resVal;
        }
    }

}
