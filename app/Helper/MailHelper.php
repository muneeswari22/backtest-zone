<?php

namespace App\Helper;

use DateTime;
use App\Models\User;
use Carbon\Carbon;
use App\Models\PersistentLogin;
use Faker\Provider\Uuid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Mail\Message;
use Swift_Message;
use DB;
use SendGrid;
use App\Helper\ReplaceHolderHelper;
use App\Helper\GeneralHelper;
use Illuminate\Support\Facades\Mail;


class MailHelper {

    public function sendOtp($user, $otp) {

        $get_template = DB::table("tbl_email_template")
                ->where('is_active', '=', 1)
                ->where('tplname', '=', "otp")
                ->first();
        $name = $user->fname.' '.$user->lname;

        $msg = $get_template->message;
        $subject_msg = $get_template->subject;

        $logo = GeneralHelper::getMyMailDetails('logo');
        $footer = GeneralHelper::getMyMailDetails('mail_footer_content');
        $heading = GeneralHelper::getMyMailDetails('heading');
        $companyDetails = GeneralHelper::getCompanyDetails($user->company_id);
        $regards_section = $companyDetails->regards_section ?? '';

        $config=config('app');
        $adminSite = $config['ADMIN_SITE'];
        $link = $config['USER_REST_PASSWORD_URL'];
        $host = $config['HOST_URL'];
        $link = $adminSite . $link . $otp;

        $request['logo'] = $host.$logo;
        $request['footer'] =$footer;
        $request['mail'] =$user->email;
        $request['otp'] =$link;
        $request['userName'] =$name;
        $request['heading'] = $heading;
        $request['regards_section'] = $regards_section;
        $request['site_link'] = $adminSite;

        $msg = ReplaceHolderHelper::replacePlaceHolderValue($request, $msg);

        $support_id = "";
        $attachment = '';

        $mail_obj = new MailHelper;
        $mail_obj->sendMail($msg, $subject_msg, $user->email, $support_id, $attachment);
    }




    public function sendMail($new_msg, $subject, $to, $cc, $attachment)
    {


        if (empty($to)) {
            return;
        }

        $app_stage = env('APP_STAGE');
        if ($app_stage == 'LIVE') {
            $data = MailHelper::msMailJson($to, $subject, $new_msg, $cc, $attachment, '');
            $mailUrl = env('MS_MAIL_URL');
            GoogleCloudTask::createTask($mailUrl, $data);
        }
    }

    public static function msMailJson($to, $subject, $msg, $cc, $attachment_path, $bcc)
    {
        $msjson = array();
        $msjson['from'] = MailHelper::getFromMail();;
        $msjson['to'] = $to;
        $msjson['reply_to'] = MailHelper::getReplyTo();
        $msjson['subject'] = $subject;
        $msjson['domain'] = $_SERVER['SERVER_NAME'];
        $msjson['cc'] = $cc;
        $msjson['attachment'] = $attachment_path;
        $msjson['bcc'] = $bcc;
        $msjson['html'] = $msg;
        $array = (json_encode($msjson));
        return $array;
    }

    public static function getReplyTo()
    {
        $get_reply_to = DB::table("tbl_app_config")
            ->where('setting', '=', 'reply_to')
            ->first();
        $reply_to = '';
        if (!empty($get_reply_to)) {
            $reply_to = $get_reply_to->value;
        }
        return $reply_to;
    }

    public static function getFromMail()
    {
        $get_from_email = DB::table("tbl_app_config")
            ->where('setting', '=', 'from_mail')
            ->first();
        $from_email = '';
        if (!empty($get_from_email)) {
            $from_email = $get_from_email->value;
        }
        return $from_email;
    }

    public static function replacePlaceHolderValue($replace_array, $msg) {

        foreach ($replace_array as $key => $value) {
            if (gettype($replace_array[$key]) == 'array') {

            } else {
                $msg = str_replace("{{" . $key . "}}", $value, $msg);
            }
        }
        return $msg;
    }
}
