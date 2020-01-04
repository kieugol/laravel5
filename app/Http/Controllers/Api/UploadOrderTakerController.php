<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repository\Api\OrderTakerRepository;
use App\Jobs\DownloadOrderTaker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class UploadOrderTakerController extends Controller
{
    const NAME_QUEUE = 'download-order-taker';

    protected $orderTakerRep = '';
    protected $request = '';

    function __construct(OrderTakerRepository $orderTakerRep, Request $request)
    {
        $this->orderTakerRep = $orderTakerRep;
        $this->request = $request;
    }

    public function create()
    {
        $data = [
            'status' => true,
            'result' => '',
            'message' =>''
        ];

        $errMgs = $this->validateRequest($this->request->all(), [
            'version' => 'required',
            'path_file' => 'required',
            'description' => 'required',
            'order_taker_code' => 'required',
        ]);
        if (!empty($errMgs)) {
            $data['status'] = false;
            $data['message'] = $errMgs;
            return response()->json($data, Response::HTTP_BAD_REQUEST);
        }

        try {
            $result = $this->orderTakerRep->create([
                'order_taker_code' => $this->request['order_taker_code'],
                'version' => $this->request['version'],
                'base_url' => BASE_URL_ORDER_TAKER,
                'file_name' => pathinfo($this->request['path_file'], PATHINFO_BASENAME),
                'is_active' => STATUS_INACTIVE,
                'description' => $this->request['description']
            ]);

            $data['result'] = $result;

            // Send to queue download file
            $requestFilter = [
                'order_taker_code' => $this->request['order_taker_code'],
                'path_file' => $this->request['path_file'],
            ];
            DownloadOrderTaker::dispatch($result->id, $requestFilter)->onQueue(self::NAME_QUEUE);
        } catch (\Exception $ex) {
            $data['status'] = false;
            $data['message'] = $ex->getMessage();
            Log::error('[Error upload order taker]'.  $ex->getMessage() . ' At '. $ex->getFile() . '[' .  $ex->getLine() . ']', $this->request->all());

            return response()->json($data, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($data);
    }

    public function getOrderTakerByCode()
    {
        $order_taker_code = $this->request['order_taker_code'] ?? '';
        $data = [
            'status' => true,
            'result' => $this->orderTakerRep->getOrderTakerByCode($order_taker_code),
            'message' => ''
        ];

        return response()->json($data, Response::HTTP_OK);
    }
}
