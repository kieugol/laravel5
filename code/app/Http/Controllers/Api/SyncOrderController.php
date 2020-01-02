<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\{SyncOrderToJumpbox, SyncOrderIntoOnlineViaCallCenter};
use Illuminate\Support\Facades\Log;

class SyncOrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $data = [];
        if (!empty($request->order_id)) {
            $order_id = $request->order_id;
            Log::info('Sync order to Jumpbox - Added into queue for order : ' . $order_id);
            // Get information order
            SyncOrderToJumpbox::dispatch($order_id)->delay(now()->addMinutes(5))->onQueue(QUEUE_SYNC_ORDER_FOR_JUMPBOX);
            $data['status'] = STATUS_TRUE;
        }
        return response()->json($data);
    }

    /**
     * Sync order delivery at pos into callCenter to driver tracker can showing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function synOrderDeliveryForOnline(Request $request) {

        $data = [];

        if (!empty($request->order_id)) {
            $order_id = $request->order_id;
            Log::info('Sync order delivery into Online via CallCenter', ['order_id' => $order_id]);
            SyncOrderIntoOnlineViaCallCenter::dispatch($order_id)->onQueue(QUEUE_SYNC_ORDER_FOR_ONLINE);
            $data['status'] = STATUS_TRUE;
        }
        return response()->json($data);
    }

}
