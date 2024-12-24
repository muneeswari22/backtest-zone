<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Role extends Model
{
    use Notifiable;

    protected $table = 'tbl_role';
    protected $fillable = ['name', 'company_id', 'description', 'created_by', 'updated_by', 'is_active', 'comments'];

    public static $ACTIVITY_ADD = 'role_add';
    public static $ACTIVITY_EDIT = 'role_edit';
    public static $ACTIVITY_DELETE = 'role_delete';
}
