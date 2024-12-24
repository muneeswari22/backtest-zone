<?php
namespace App\Http\Controllers;

use App\Models\PersistentLogins;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Faker\Provider\Uuid;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Helper\LogHelper;
use Illuminate\Support\Str;
use App\Models\Media;
use App\Helper\GeneralHelper;


class CommonController extends Controller
{

    public function getHeaders(Request $request) {

        $resVal = array();
        $resVal['ipAddress'] = $request->ip();
        $resVal['ipAddresss'] = $request->ips();
        $resVal['headers'] = $request->server();
        return $resVal;
    }

    public static function loadMenu(Request $request) {
        $currentuser = Auth::user();
        $resVal = array();
        $resVal['success'] = true;

        $loadPageMenu = DB::table('tbl_role_screen_pages as rp')
                ->join('tbl_master_screen as s', 's.id', '=', 'rp.linkScreenId')
                ->select('rp.*', 's.sectionName', 's.screenURL', 's.screenName', 's.screen_code as screenCode', 's.screenSort', 's.screenIcon', 's.subSectionName', 's.masterName', 's.section_icon as sectionIcon', 's.sectionSort')
                ->where('rp.linkRoleID', $currentuser->role_id)
                ->where('s.is_display_menu', '=', 1)
                ->where('pageView','=','1')
                ->where('rp.company_id','=',$currentuser->company_id)
                ->get();

                $resVal['count'] = count(value: $loadPageMenu);
                $resVal['data'] = $loadPageMenu;

		return $resVal;

    }

    public static function getRoleName() {
        $currentuser = Auth::user();
        $roleName = DB::table('tbl_role')
                    ->select('name')
                    ->where('id',$currentuser->role_id)
                    ->where('company_id',$currentuser->company_id)
                    ->first();
        if (isset($roleName)) {
            $role_name = $roleName->name;
        } else {
            $role_name = '';
        }
        return $role_name;

    }
   

}
