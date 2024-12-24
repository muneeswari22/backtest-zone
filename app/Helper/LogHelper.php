<?php

namespace App\Helper;
use Google\Cloud\Logging\LoggingClient;

class LogHelper {
    public static function info($string) {
        //$logger = LoggingClient::psrBatchLogger('Chitly');
        //$logger->info($string . " REQUEST_METHOD = " . $_SERVER["REQUEST_METHOD"]);
     }

    public static function info1($string, $request) {
        //$logger = LoggingClient::psrBatchLogger('Chitly');
        //$logger->info($string . " REQUEST_METHOD = " . $_SERVER["REQUEST_METHOD"], $request);
    }

    public static function warningInfo($string, $request) {
        //$logger = LoggingClient::psrBatchLogger('Chitly');
        //$logger->info($string . " REQUEST_METHOD = " . $_SERVER["REQUEST_METHOD"], $request);
    }
    public static function infoObj($string, $request) {
        $logger = LoggingClient::psrBatchLogger('Backtest-zone');
        $logger->info($string . " REQUEST_METHOD = " . $_SERVER["REQUEST_METHOD"], $request);
    }

}
