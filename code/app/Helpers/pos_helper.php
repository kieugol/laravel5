<?php

namespace App\Helpers;

class PosHelper
{
    public static function post($route, $params = array(), $options = array())
    {
        $url = config("admin.pos_api_url") . $route;

        $options[CURLOPT_HTTPHEADER] = array(
            'Content-Type:application/json',
            'X-API-KEY: E75CB7887DAFA84986B6A6E657D2A'
        );
        $result = CurlHelp::post($url, $params, $options);
        $result_arr = json_decode($result, true);
        $httpcode = CurlHelp::getResponseCode();

        if ($httpcode == 200 && is_array($result_arr)) {
            $result_arr['result'] = true;
            return $result_arr;
        }
        return array('result' => false, 'error' => $result_arr['error']);
    }

    public static function beforeTax($price)
    {
        return round($price / TAX_RATE);
    }

    public static function afterTax($price)
    {
        return $price * TAX_RATE;
    }

    public static function restaurantTax($price)
    {
        return round($price / 10);
    }

    public static function push_fpt($destination, $source, $host, $port, $timeout, $username, $password)
    {
        // set up basic connection
        $conn_id = ftp_connect($host, $port, $timeout);
        if (!$conn_id) {
            return false;
        }
        // login with username and password
        $login_result = ftp_login($conn_id, $username, $password);
        if (!$login_result) {
            return false;
        }

        $result = ftp_put($conn_id, $destination, $source, FTP_BINARY);
        ftp_close($conn_id);

        return $result;
    }

    public static function get_day_from_date($date = '')
    {
        if ($date == '') {
            $date = date('Y/m/d');
        }
        $timestamp = strtotime($date);
        $day       = date('D', $timestamp);

        return $day;
    }

    public static function get_name_file_ftp($date = '')
    {
        $day = self::get_day_from_date($date);
        return ConfigHelp::get("outlet_code") . '_' . FTP_EOD_FOLDER_TRANSFER[$day][KEY_NO] . '.zip';
    }

    public static function get_extension_export_file($type = '')
    {
        return '.' . ($type == CSV_EXTENSION_NAME ? CSV_EXTENSION_NAME : EXCEL_EXTENSION_NAME);
    }

    public static function format_amount($amount)
    {
        return "Rp " . number_format($amount, 0, ',', '.');
    }

    public static function division($num1, $num2, $roundNo = 2)
    {
        if ($num1 > 0 && $num2 > 0) {
            return round(($num1 / $num2), $roundNo);
        }
        return 0;
    }
    
    public static function getDateTimeEOD($fromDate = null)
    {
        $from_date = $fromDate ?? date("Y-m-d");
        $from_time = date("H:i:s");
    
        $opening_hour = str_pad(ConfigHelp::get('start_time', DEFAULT_OPENING_HOUR), 2, '0', STR_PAD_LEFT) . ':00:00';
        $closing_hour = str_pad(ConfigHelp::get('end_time', DEFAULT_CLOSING_HOUR), 2, '0', STR_PAD_LEFT) . ':00:00';
        $opening_hour = date('H:i:s', strtotime('-' . DEFAULT_ADDITIONAL_HOURS . ' hour', strtotime($opening_hour)));
        $closing_hour = date('H:i:s', strtotime('+' . DEFAULT_ADDITIONAL_HOURS . ' hour', strtotime($closing_hour)));
        if ($from_time < $closing_hour) {
            $from_date = date('Y-m-d', strtotime('-1 day', strtotime($from_date)));
        }
        $to_date = date('Y-m-d', strtotime('+1 day', strtotime($from_date)));
    
        $data                 = [];
        $data['from_time']    = $from_date;
        $data['to_time']      = $to_date;
        $data['start_date']   = "$from_date $opening_hour";
        $data['end_date']     = "$to_date $closing_hour";
        $data['opening_hour'] = $opening_hour;
        $data['closing_hour'] = $closing_hour;
//        dd($data);
        
        return $data;
    }
    
    /**
     * @param Validator $validator
     *
     * @return null|string
     */
    public static function formatErrorsMessage($errors = [])
    {
        $errMgs = [];
        
        foreach ($errors as $key => $error) {
            $errMgs = array_merge($errMgs, $error);
        }
        
        $errMgs = array_values(array_unique($errMgs));
        
        if (!$errMgs) {
            return null;
        }
        
        return implode('<br>', $errMgs);
    }
    
    static function getCurrentUser($key = '')
    {
        $user = auth("admin")->user();
        return $key != '' ? ($user->{$key} ?? null) : $user;
    }

    public static function object_key_column($objects, $column)
    {
        if (!empty($objects)) {
            $arr = array();
            foreach ($objects as $object) {
                $arr[$object->$column] = $object;
            }
            return $arr;
        }
        return [];
    }
    
    /**
     * @param array $data
     * @param       $path
     * @param       $filename
     *
     * @return bool|string
     */
    public static function generateCSV(array $data, $path, $filename)
    {
        try {
            $fileNameUnique = rename_unique($path, $filename);
            $fp = fopen($path . '/' . $fileNameUnique, "w");
            foreach ($data as $fields) {
                fputcsv($fp, $fields);
            }
            
            fclose($fp);
            return $fileNameUnique;
            
        } catch (\Exception $ex) {
            return false;
        }
    }
}
