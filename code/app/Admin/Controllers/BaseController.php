<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;

class BaseController extends Controller {
    
    protected $request = null;
    
    public function __construct()
    {
        $this->request = app('request');
    }

    protected function getParamFilter($request) {
        $param = $request->all();

        if (!empty($param['fromTime']) && !empty($param['toTime'])) {
            $param['period'] = date('Y/m/d H:i:s', strtotime($param['fromTime'])) . ' - ' . date('Y/m/d H:i:s', strtotime($param['toTime']));
        } else {
            if (empty($param['fromDate']) && empty($param['toDate'])) {
                $param['fromDate'] = date("Y-m-d");
                $param['toDate'] = date("Y-m-d");
            }
            $param['period'] = date('Y/m/d', strtotime($param['fromDate'])) . ' - ' . date('Y/m/d', strtotime($param['toDate']));
        }

        return $param;
    }

    function downloadFile(Request $request, $path = false, $file_name = false)
    {
        if (!$path && !$file_name) {
            $path      = $request->get('path', '');
            $file_name = $request->get('file_name', '');
        }

        if (!empty($path) && !empty($file_name)) {
            $path_file_zip = $path . DIRECTORY_SEPARATOR . $file_name;
            $zip_file      = glob($path_file_zip);
            if ($zip_file) {
                return response()->download($path_file_zip);
            } else {
                $files = glob($path . DIRECTORY_SEPARATOR . '*.csv');
                if ($files) {
                    \Zipper::make($file_name)->add($files)->close();
                    return response()->download($path_file_zip);
                }
            }
        }
        return null;
    }
}
