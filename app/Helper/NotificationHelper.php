<?php

namespace App\Helper;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;


class NotificationHelper {

    public static function sendNotification($userDetails, $message, $current_user) {

        $cur_date = Carbon::now();
        $cur_date = $cur_date->format('Y-m-d');
        $notification = new Notification;
        $notification->date = $cur_date;
        $notification->notification_message = $message;
        $notification->to_user_id = $userDetails->id;
        $notification->from_user_id = $current_user->id;
        $notification->name = $current_user->fname;
        $notification->read_or_not = 0;
        $notification->details = '';
        $notification->is_active = 1;
        $notification->created_by = $current_user->created_by;
        $notification->updated_by = $current_user->created_by;
        $notification->save();
    }


}
