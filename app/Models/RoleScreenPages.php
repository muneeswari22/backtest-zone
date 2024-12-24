<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class RoleScreenPages extends Model
{
    use Notifiable;

    protected $table = 'tbl_role_screen_pages';
    protected $fillable = ['company_id','linkScreenId','linkRoleID', 'pageView', 'created_by', 'updated_by', 'pageAdd', 'pageEdit', 'pageDelete', 'pageDetail', 'pagePrint','is_comments_history'];

    public static $PERMISSIONS = 'Permissions';
}
