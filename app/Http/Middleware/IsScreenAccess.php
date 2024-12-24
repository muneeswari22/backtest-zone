<?php

namespace App\Http\Middleware;

use DB;
use Illuminate\Support\Facades\Auth;
use Closure;

class IsScreenAccess {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $screenCode, $screenAction) {
        $screenAccess = 0;

        $is_need_list = $request->header('isNeedList');
        if ($is_need_list == 1) {
            $screenAccess = 1;
        } else {
            $request->headers->set('screen_code',  $screenCode);
            if (isset($screenCode)) {
                $screen = $this->getScreenList($screenCode);
                if ($screen != '') {
                    $currentuser = Auth::user();
                    $screenAccess = $this->getRoleAccess($screen, $currentuser, $screenAction, $request);
                } else {
                    $screenAccess = 0;
                }
            }
        }

        if ($screenAccess == 1) {
            return $next($request);
        } else {
            $result = array();
            $result["success"] = FALSE;
            $result["message"] = "Access Denied";
            return response($result);
        }
    }

    private function getScreenList($screenCode) {

        $screen = DB::table('tbl_master_screen')
                ->select('id')
                ->where('screenURL', '=', $screenCode)
                ->first();
        return $screen;
    }

    private function getRoleAccess($screen, $currentuser, $screenAction, $request) {

        $screenAccess = 0;
        $roleAccess = DB::table('tbl_role_screen_pages')
                ->select('*')
                ->where('linkScreenId', '=', $screen->id)
                ->where('linkRoleID', '=', $currentuser->role_id)
                ->where('company_id', '=', $currentuser->company_id)
                ->first();

        if (!empty($roleAccess) && $roleAccess != null) {
            $request->headers->set('screen_id', $screen->id);
            if ((!empty($screenAction)) && $screenAction == 'view' && ($roleAccess->pageView == 1 || $roleAccess->pageDetail == 1)) {
                $screenAccess = 1;
            }
            if ((!empty($screenAction) && $screenAction == 'add') && $roleAccess->pageAdd == 1) {
                $screenAccess = 1;
            }
            if ((!empty($screenAction) && $screenAction == 'edit') && $roleAccess->pageEdit == 1) {
                $screenAccess = 1;
            }
            if ((!empty($screenAction) && $screenAction == 'delete') && $roleAccess->pageDelete == 1) {
                $screenAccess = 1;
            }
            if ((!empty($screenAction) && $screenAction == 'detail') && $roleAccess->pageDetail == 1) {
                $screenAccess = 1;
            }
        }
        return $screenAccess;
    }

}
