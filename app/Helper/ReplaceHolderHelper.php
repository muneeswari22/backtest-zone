<?php

namespace App\Helper;

use DB;

class ReplaceHolderHelper {

    public static function getThosandSeperatorFormat() {
        $thous_sep = 'dd,dd,ddd';
        $result = DB::table('tbl_app_config')->where('setting', 'thousand_seperator')->first();
//        while ($result != null){
        if (!empty($result)) {
            $thous_sep = $result->value;
        }
        return $thous_sep;
    }

    public static function getDateFormat() {
        $dateFormat = '';
        $result = DB::table('tbl_app_config')->where('setting', 'date_format')->first();
//        while ($result != null){
        if (!empty($result)) {
            $dateFormat = $result->value;
        }
        return $dateFormat;
    }

    public static function formatDate($date, $format) {
        if (empty($date) || empty($format)) {

            return $date;
        }
        $new_format = str_replace("YYYY", "Y", $format);
        $new_format = str_replace("yyyy", "y", $new_format);
        $new_format = str_replace("MM", "M", $new_format);
        $new_format = str_replace("mm", "m", $new_format);
        $new_format = str_replace("DD", "D", $new_format);
        $new_format = str_replace("dd", "d", $new_format);
        $new_date = date_create($date);
        return date_format($new_date, $new_format);
    }

    public static function getCurrencySymbol() {
        $symbol = '';
        $result = DB::table('tbl_app_config')->where('setting', 'currency')->first();
        if ($result != null)
            $symbol = $result->value;
        return $symbol;
    }

    public static function getReceiptNo() {
        $receiptNo = '';
        $result = DB::table('tbl_app_config')->where('setting', 'receipt_no')->first();
        if ($result != null)
            $receiptNo = $result->value;

        return $receiptNo;
    }

    public static function convertNumberFormat($number, $thous_sep) {
        $number = number_format((float) $number, 2, '.', '');
        list($whole, $decimal) = explode('.', $number);
        $number = (float) $number;
        if ($thous_sep == 'd,dddd,dddd') {
            $number = preg_replace("/\B(?=(?:\d{4})+(?!\d))/", ",", $whole);
            $number = $number . "." . $decimal;
        } else if ($thous_sep == 'd dddd dddd') {
            $number = preg_replace("/\B(?=(?:\d{4})+(?!\d))/", " ", $whole);
            $number = $number . "." . $decimal;
        } else if ($thous_sep == 'dd,dd,dd,ddd') {
            if (strlen($whole) < 4) {
                $number = $whole;
            } else {
                $tail = substr($whole, -3);
                $head = substr($whole, 0, -3);
                $head = preg_replace("/\B(?=(?:\d{2})+(?!\d))/", ",", $head);
                $whole = $head . "," . $tail;
            }
            $number = $whole . "." . $decimal;
        } else if ($thous_sep == 'ddd ddd ddd') {
            $number = preg_replace("/\B(?=(?:\d{3})+(?!\d))/", " ", $whole);
            $number = $number . "." . $decimal;
        } else if ($thous_sep == 'ddd,ddd,ddd') {
            $number = preg_replace("/\B(?=(?:\d{3})+(?!\d))/", ",", $whole);
            $number = $number . "." . $decimal;
        }
        return $number;
    }

    public static function convertNumberToWord($num) {
        global $ones, $tens, $triplets;
        $ones = array(
            '',
            ' One',
            ' Two',
            ' Three',
            ' Four',
            ' Five',
            ' Six',
            ' Seven',
            ' Eight',
            ' Nine',
            ' Ten',
            ' Eleven',
            ' Twelve',
            ' Thirteen',
            ' Fourteen',
            ' Fifteen',
            ' Sixteen',
            ' Seventeen',
            ' Eighteen',
            ' Nineteen'
        );
        $tens = array(
            '',
            '',
            ' Twenty',
            ' Thirty',
            ' Fourty',
            ' Fifty',
            ' Sixty',
            ' Seventy',
            ' Eighty',
            ' Ninety'
        );

        $triplets = array(
            '',
            ' Thousand',
            ' Million',
            ' Billion',
            ' Trillion',
            ' Quadrillion',
            ' Quintillion',
            ' Sextillion',
            ' Septillion',
            ' Octillion',
            ' Nonillion'
        );
        return $this->convertNum($num);
    }

    public static function extractString($string, $start, $end) {
        $string = " " . $string;
        $ini = strpos($string, $start);
        if ($ini == 0)
            return "";
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

    public static function numberTowords($number) {
        $no = round($number);
//        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array('0' => '', '1' => 'One', '2' => 'Two',
            '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
            '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
            '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
            '13' => 'Thirteen', '14' => 'Fourteen',
            '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
            '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
            '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
            '60' => 'Sixty', '70' => 'Seventy',
            '80' => 'Eighty', '90' => 'Ninety');
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] .
                        " " . $digits[$counter] . $plural . " " . $hundred :
                        $words[floor($number / 10) * 10]
                        . " " . $words[$number % 10] . " "
                        . $digits[$counter] . $plural . " " . $hundred;
            } else
                $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        //unused local variable $points
//        $points = ($point) ?
//                "." . $words[$point / 10] . " " .
//                $words[$point = $point % 10] : '';
        return $result;
    }

    public static function forWordinPaise($number) {

        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'One', 2 => 'Two',
            3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six',
            7 => 'Seven', 8 => 'Eight', 9 => 'Nine',
            10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve',
            13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen',
            19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty',
            40 => 'Fourty', 50 => 'Fifty', 60 => 'Sixty',
            70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety');
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else
                $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));

        $float_decimal = $decimal;
        $int_decimal = (int) $decimal;
        if ($float_decimal != $int_decimal) {
            $decimal = "$decimal";
            $decimal = (int) $decimal;
            ;
        }

        $paise = ($decimal < 21) ? "and " . $words[$decimal] . " " . ' Paise' : '';
        $paise = ($decimal) ? ($paise ?: "and" . " " . ($words[$decimal - ($decimal % 10)]) . " " . ($words[$decimal % 10]) . ' Paise' ) : '';
        return ($Rupees ? $Rupees . 'Rupees ' : '') . $paise;
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

?>
