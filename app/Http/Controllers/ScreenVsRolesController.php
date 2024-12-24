<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\RoleScreenPages;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Exception;
use App\Models\ActivityLog;
use App\Helper\ActivityLogHelper;
use Carbon\Carbon;

class ScreenVsRolesController extends Controller
{
    public function save(Request $request)
    {
        $roledata = $request->all();

        $user = Auth::user();

        $response = array();
        $response['success'] = true;
        $response['message'] = "Permission Given";
        try {
            $accessList = $roledata['roleAccess'];
            foreach ($accessList as $list) {
                $rc = DB::table('tbl_role_screen_pages')
                    ->select('*')
                    ->where('linkScreenID', '=', $list['linkScreenId'])
                    ->where('linkRoleID', '=', $list['linkRoleID'])
                    ->where('company_id', '=', $user->company_id)
                    ->get();
                if (count($rc) > 0) {
                    foreach ($rc as $rcs)
                        $mrsp = RoleScreenPages::findOrFail($rcs->id);
                        $mrsp->created_by = $user->id;
                } else {
                    $mrsp = new RoleScreenPages;
                }
                $mrsp->fill($list);
                $mrsp->company_id = $user->company_id;
                $mrsp->updated_by = $user->id;
                $mrsp->save();
            }
            //$this->saveUpdateActivity($request, $additionDeduction, 'delete');
        } catch (Exception $e) {
            $response['success'] = true;
            $response['message'] = "Something went wrong";
        }

        return $response;
    }


    public function loadScreen($id)
    {
        $response = array();

        $roleScreenData = $this->loadMstMasterScreen();
        $response['success'] = true;
        $response['total'] = count($roleScreenData);
        $response['data'] = $roleScreenData;
        return $response;
    }


    public function loadMstMasterScreen()
    {
        $query = DB::table('tbl_master_screen')
            ->select('*')
            ->orderBy('sectionName')
            ->get();

        return $query;
    }


    public function loadRoleAccess($id)
    {
        $response = array();
        $roleAccessData = $this->roles_screen_data_ajax($id);
        $response['success'] = true;
        $response['total'] = count($roleAccessData);
        $response['accessList'] = $roleAccessData;

        return $response;
    }

    public function roles_screen_data_ajax($id)
    {

        $user = Auth::user();
        $roleScreenData = DB::table('tbl_role as rm')
            ->select('ms.id as screenId', 'rm.id as roleId', 'rm.name', 'ms.displayName', 'ms.screenName', 'rsp.linkRoleId', 'rsp.linkScreenId', 'rsp.pageView', 'rsp.pageAdd', 'rsp.pageEdit', 'rsp.pageDelete', 'rsp.pageDetail', 'rsp.is_comments_history', 'rsp.pagePrint')
            ->join('tbl_role_screen_pages AS rsp', 'rsp.linkRoleID', '=', 'rm.id')
            ->join('tbl_master_screen AS ms', 'rsp.linkScreenId', '=', 'ms.id')
            ->where('rsp.linkRoleID', '=', $id)
            ->where('rsp.company_id','=', $user->company_id)
            ->get();

        return $roleScreenData;
    }

    public function codeBaseAccessCheck(Request $request) {

        $code = $request->input('code','');

        $response = array();
        $response['success'] = true;
        $user = Auth::user();

        $query = DB::table('tbl_role as rm')
            ->select('ms.id as screenId', 'rm.id as roleId', 'rm.name as roleName', 'ms.displayName', 'ms.screenName', 'rsp.linkRoleId', 'rsp.linkScreenId', 'rsp.pageView', 'rsp.pageAdd', 'rsp.pageEdit', 'rsp.pageDelete', 'rsp.pageDetail', 'rsp.is_comments_history', 'rsp.pagePrint')
            ->join('tbl_role_screen_pages AS rsp', 'rsp.linkRoleID', '=', 'rm.id')
            ->join('tbl_master_screen AS ms', 'rsp.linkScreenId', '=', 'ms.id')
            ->where('ms.screenURL', '=', $code)
            ->where('rsp.linkRoleID', '=', $user->role_id)
            ->where('rsp.company_id','=', $user->company_id)
            ->first();

        $response['data'] = $query;
        return $response;
    }

}
