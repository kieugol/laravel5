<?php

if (!function_exists('division')) {

    function division($top, $bot, $decimal = 2) {
        if ($bot == 0) {
            return 0;
        }
        return round($top / $bot, $decimal);
    }

}
if (!function_exists('before_tax')) {

    function before_tax($price) {
        return round($price/TAX_RATE);
    }

}

if (!function_exists('show_status_sync')) {

    function show_status_sync($status, $text_success = 'Sent', $text_unsuccess = 'Resend') {
        return $status ?
            '<div class="btn btn-xs btn-default"><i class="fa fa-check text-success"></i>&nbsp;&nbsp;'.$text_success.'</div>'
            : '<button class="btn btn-xs btn-warning"><i class="fa fa-undo"></i>&nbsp;&nbsp;'.$text_unsuccess.'</button>';
    }

}


if (!function_exists('object_column')) {
    function object_column($objects, $column)
    {
        $arr = array();
        foreach ($objects as $object) {
            $arr[] = $object->$column;
        }
        return $arr;
    }
}

if (!function_exists('object_key_column')) {
    function object_key_column($objects, $column)
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
}

if (!function_exists('rename_unique')) {
    function rename_unique($path, $filename)
    {
        $pathFile = "$path/$filename";
        if (!file_exists($pathFile)) {
            return $filename;
        }

        $fileNameNotExisted = pathinfo($filename, PATHINFO_FILENAME);
        $ext                = pathinfo($filename, PATHINFO_EXTENSION);

        $i = 1;
        while (file_exists("$path/$fileNameNotExisted($i).$ext")) {
            $i++;
        }

        return "$fileNameNotExisted($i).$ext";
    }
}

if (!function_exists('join_files')) {
    function join_files(array $files, $result)
    {
        if (!is_array($files)) {
            throw new Exception('`$files` must be an array');
        }

        $wH = fopen($result, "w+");

        foreach ($files as $file) {
            $fh = fopen($file, "r");
            while (!feof($fh)) {
                fwrite($wH, fgets($fh));
            }
            fclose($fh);
            unset($fh);
            fwrite($wH, "\n"); //usually last line doesn't have a newline
        }
        fclose($wH);
        unset($wH);
    }
}

if (!function_exists('remove_blank_row_csv')) {
    function remove_blank_row_csv($source, $destination)
    {
        if (file_exists($destination)) {
            unlink($destination);
        }
        $handle = fopen($source, 'r'); //your csv file
        $clean = fopen($destination, 'a+'); //new file with no empty rows

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            if($num > 1)
                fputcsv($clean, $data ,",");
        }
        fclose($handle);
        fclose($clean);
    }
}

if (!function_exists('format_excel_number')) {
    function format_excel_number($value, $character = '', $isParseFloat = true)
    {
        $value = $isParseFloat ? floatval($value) : intval($value);
        return $value == 0 ? ($character != '' ? $character : $value) : $value;
    }
}
