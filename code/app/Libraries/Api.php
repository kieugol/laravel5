<?php

namespace App\Libraries;
use Illuminate\Http\Response;

class Api
{
    
    public static function response($result = null, $statusCode = Response::HTTP_OK)
    {
        $dataReturn = [
            'status'  => $statusCode == Response::HTTP_OK ? STATUS_TRUE : STATUS_FALSE,
            'message' => $result['message'] ?? '',
            'data'    => $result['data'] ?? '',
        ];
        return response()->json($dataReturn, $statusCode);
    }
}
