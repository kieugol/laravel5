<?php

namespace App\Helpers;

class FileHelper {

    public static function create_sub_folder($folder_path, $subs) {
        if (!is_dir($folder_path)) {
            return false;
        }
        $subs = explode("/", $subs);
        $dir = $folder_path;
        foreach ($subs as $sub) {
            $dir .= DIRECTORY_SEPARATOR . $sub;
            if (!is_dir($dir)) {
                if(!mkdir($dir, 0777)) {
                    return false;
                }
            }
        }
        return $dir;
    }

}
