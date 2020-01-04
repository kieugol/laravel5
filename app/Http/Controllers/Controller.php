<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Validator;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function validateRequest($input, $rules, $messages = [], $is_format_mgs = true)
    {
        $errMgs = [];
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errMgs = $errors->all();
        }

        if ($is_format_mgs) {
            $errMgs = implode('<br>', $errMgs);
        }

        return $errMgs;
    }
}
