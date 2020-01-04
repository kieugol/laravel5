<?php

namespace App\Helpers;

use App\Model\Config;

class ConfigHelp
{
    static $configs = null;

    public static function getInstance()
    {
        if (self::$configs == null) {
            self::$configs = Config::where("is_active", 1)->get()->pluck("value", "key");
        }
        return self::$configs;
    }

    public static function get($key, $default = null)
    {
        $configs = self::getInstance();
        return isset($configs[$key]) ? $configs[$key] : $default;
    }
    
    public static function checkIsset($arr_key = [], $arr)
    {
        $arr_key_res = [];
        foreach ($arr_key as $key) {
            if (isset($arr[$key])) {
                $arr_key_res[] = $key;
            }
        }
        
        return $arr_key_res;
    }
}
