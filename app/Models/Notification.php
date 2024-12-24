<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Notification extends Model {
    protected  $table ='tbl_notification';
    protected  $fillable = ['company_id','from_user_id','to_user_id','name','date','notification_message' ,'details','read_or_not','created_by','is_active','updated_by'];
    protected $dates = ['updated_at','created_at' ];

}
