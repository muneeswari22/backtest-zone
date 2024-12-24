<?php
namespace App\Helper;

use DB;
use Google\Cloud\Storage\StorageClient;
use Carbon\Carbon;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;



class GeneralHelper {

    public  static function random_strings($length_of_string) {
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

        // Shufle the $str_result and returns substring
        // of specified length
        return substr(
            str_shuffle($str_result),
            0,
            $length_of_string
        );
    }

    public static function getMyMailDetails($setting){

        $value = "";
        $detail = DB::table("tbl_app_config")
                ->where('setting',$setting)
                ->first();
        if(!empty($detail)){
            $value = $detail->value;
        }
        return $value;
    }

    public static function uploadEnodeCloudImage($uploaded_file, $uploadFolderName) {

        $currentDirectory = getcwd();
        $uploadDirectory = "/uploads/";

        $fileNAmeToUpload = 'image_' . time() . '.png';
        $uploadPath = $currentDirectory . $uploadDirectory . $fileNAmeToUpload;

        $resVal['code'] = "3";
        $resVal['currentDirectory'] = $currentDirectory;
        $resVal['uploadDirectory'] = $uploadDirectory;
        $resVal['fileNAmeToUpload'] = $fileNAmeToUpload;
        $resVal['uploadPath'] = $uploadPath;

        try {
            if (file_put_contents($uploadPath, base64_decode($uploaded_file))) {

                $config = config('app');
                $accountName = $config['GOOGLE_PROJECT_ID'];
                $accountKeyPath = $config['GOOGLE_UPLOAD_PATH'];

                $storage = new StorageClient([
                    'projectId' => $accountName,
                    'keyFilePath' => $accountKeyPath
                ]);

                $bucket = $config['BUCKET_NAME'];
                $folder = $config['FOLDER_NAME'];

                $bucket = $storage->bucket($bucket);

                $googleCloudStoragePath =  $folder . "/" . $uploadFolderName . $fileNAmeToUpload;

                $bucket->upload(file_get_contents($uploadPath), [
                    'name' => $googleCloudStoragePath
                ]);

                $resVal['path'] = $googleCloudStoragePath;
                $resVal['code'] = "0";

                return $resVal;

            } else {
                $resVal['code'] = "2";
                LogHelper::warningInfo('Encode - image folder upload else ' . $_SERVER["REQUEST_URI"], json_decode(json_encode($resVal), true));
                return $resVal;
            }
        } catch (\Exception $e) {
            $resVal['code'] = "1";
            LogHelper::warningInfo('Encode - image folder upload catch ' . $_SERVER["REQUEST_URI"], json_decode(json_encode($resVal), true));
            return $resVal;
        }

    }


    public static function saveImageToCloud($file, $uploadFolder, $name) {

        $config = config('app');

        $accountName = $config['GOOGLE_PROJECT_ID'];
        $accountKeyPath = $config['GOOGLE_UPLOAD_PATH'];

        $storage = new StorageClient([
            'projectId' => $accountName,
            'keyFilePath' => $accountKeyPath
        ]);

        $bucket = $config['BUCKET_NAME'];
        $folder = $config['FOLDER_NAME'];

        $bucket = $storage->bucket($bucket);

        $googleCloudStoragePath =  $folder . "/" . $uploadFolder . $name;

        $bucket->upload(file_get_contents($file), [
            'name' => $googleCloudStoragePath
        ]);
        return $googleCloudStoragePath;

    }

    public static function getNotificationTemplate($request,$template,$user) {
        $value="";
        $msg='';
        $detail = DB::table("tbl_notification_template")
                    ->select('message')
                    ->where('tplname','=',$template)
                    ->where('company_id', $user->company_id)
                    ->first();

        if(!empty($detail)){
            $value = $detail->message;
            $msg = ReplaceHolderHelper::replacePlaceHolderValue($request, $value);
         }
        return $msg;
        }
        public static function getTableFirstRowsByWhereColumn($table, $where, $value) {
            $user = Auth::user();
            $table = DB::table($table)
                    ->where($where, $value)
                    ->where('is_active', 1)
                    ->where('company_id', $user->company_id)
                    ->first();

            return $table;
        }

}
