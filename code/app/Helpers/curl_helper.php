<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class CurlHelp {

    private static $curl_info = null;

    public static function getResponseCode() {
        return self::$curl_info['http_code'];
    }

    public static function getCurlInfo() {
        self::$curl_info;
    }

    public static function get($url, $params = array(), $options = array()) {
        try {
            $s = curl_init();

            $newurl = $params ? $url . "?" . http_build_query($params) : $url;
            curl_setopt($s, CURLOPT_URL, $newurl);
            curl_setopt($s, CURLOPT_RETURNTRANSFER, true);

            foreach ($options as $key => $value) {
                curl_setopt($s, $key, $value);
            }

            $result = curl_exec($s);
            self::$curl_info = curl_getinfo($s);

            self::insertLog($url, "get", $params, $result, self::getResponseCode(), curl_error($s));

            curl_close($s);
            return $result;
        } catch (Exception $ex) {
            self::insertLog($url, "get", $params, null, 500, $ex->getMessage());
            return null;
        }
    }

    public static function post($url, $params = array(), $options = array()) {
        try {
            $s = curl_init();
            $json = json_encode($params);
            curl_setopt($s, CURLOPT_URL, $url);
            curl_setopt($s, CURLOPT_POST, true);
            curl_setopt($s, CURLOPT_POSTFIELDS, $json);
            curl_setopt($s, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($s, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($s, CURLOPT_RETURNTRANSFER, true);

            foreach ($options as $key => $value) {
                curl_setopt($s, $key, $value);
            }

            $result = curl_exec($s);

            self::$curl_info = curl_getinfo($s);
            self::insertLog($url, "post", $params, $result, self::getResponseCode(), curl_error($s));

            curl_close($s);
            return $result;
        } catch (Exception $ex) {
            self::insertLog($url, "post", $params, null, 500, $ex->getMessage());
            return false;
        }
    }

    public static function insertLog($url, $method, $params, $response, $httpcode, $error = null) {
        DB::table('log_curl')->insert(array(
            'url' => $url,
            'method' => $method,
            'params' => (is_array($params) || is_object($params)) ? json_encode($params) : $params,
            'response' => (is_array($response) || is_object($response)) ? json_encode($response) : $response,
            'http_code' => $httpcode,
            'error' => $error,
            'created_date' => Date("Y-m-d H:i:s")
        ));
    }

}
