<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;



class ChangePasswordController extends Controller
{
    public function changePassword(Request $request)
    {
        $error = array();
        $resVal = array();
        $currentUser = Auth::user();

        $old_pass = $request->input('oldPassword');
        $new_pass = $request->input('newPassword');
        $confirm_pass = $request->input('confirmPassword');

        $user = DB::table('tbl_user')
            ->where('id', '=', $currentUser->id)
            ->where('is_active', '=', 1)
            ->where('company_id','=',$currentUser->company_id)
            ->first();

        if (!(Hash::check($old_pass, $user->password))) {
            $error['validOldPass'] = 'Current password is wrong';
        }

        if (count($error) > 0) {
            $resVal['success'] = false;
            $resVal['message'] = $error['validOldPass'];
            return $resVal;
        } else {
            $validator = Validator::make($request->all(), [
                'newPassword' => 'required|max:255',
                'confirmPassword' => 'required|max:255',
            ]);
            if ($validator->fails()) {
                $resVal['success'] = false;
                $resVal['message'] = "Password and Conform Password Required";
                return $resVal;
            }
        }

        $uppercase = preg_match('@[A-Z]@', $new_pass);
        $lowercase = preg_match('@[a-z]@', $new_pass);
        $number = preg_match('@[0-9]@', $new_pass);
        $specialChars = preg_match('/[\'"!.;:^£$%&*()}{@#~?><>,|=_+¬-]/', $new_pass);

        if (!$uppercase || !$specialChars || !$lowercase || !$number || strlen($new_pass) < 8 || strlen($new_pass) > 20) {
            $error['validPass'] = 'Your Password Must have one Upper case, numberic and special character and length between 8 to 20';
        }

        if (count($error) > 0) {
            $resVal['success'] = false;
            $resVal['message'] = $error['validPass'];
            return $resVal;
        }

        if ($new_pass != $confirm_pass) {
            $error['errorConformPass'] = 'Confirmed password is not matched';
        }

        if (count($error) > 0) {
            $resVal['success'] = false;
            $resVal['message'] = $error['errorConformPass'];
            return $resVal;
        } else {
            $newpass = Hash::make($new_pass);
            $user = User::findOrFail($user->id);
            $user->password = $newpass;
            $user->save();

            $resVal['success'] = true;
            $resVal['message'] = "Password changed successfully";
            return $resVal;
        }
    }

}
