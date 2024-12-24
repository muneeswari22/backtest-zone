<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersistentLogins extends Model {

    protected $table = 'persistent_logins';
    protected $primaryKey = 'token';
    protected $fillable = [
         'username', 'series', 'token', 'expiry_date', 'company_id'
    ];
    protected $hidden = [
    ];
    public $incrementing = false;
    public $timestamps = false;

}
