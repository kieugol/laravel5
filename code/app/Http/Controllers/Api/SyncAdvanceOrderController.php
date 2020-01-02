<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 10/17/2018
 * Time: 11:44 AM
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncAdvanceOrderToPosApi;
use App\Repository\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SyncAdvanceOrderController extends Controller
{
    const SUCCESS = true;
    const NAME_QUEUE_ADVANCE_ORDER = 'sync_advance_order_to_posapi';
    private $order_repository;

    public function __construct(OrderRepository $order_repository) {
        $this->order_repository = $order_repository;
    }

    /**
     * Post advance_order to POSAPI.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $data = [];
        if (!empty($request->order_id)) {
            $time          = time();
            $order_id      = $request->order_id;
            $item          = $this->order_repository->getOrderByID($order_id);
            $kds_time      = empty($item->kds_time) ? $time : $item->kds_time;
            $kds_timestamp = strtotime($kds_time);

            $wait_time = $kds_timestamp - $time;
            $wait_time = ($wait_time > 0) ? $wait_time : 0;

            Log::info('Sync advance order to posapi - Added into queue for advance_order : ' . $order_id);
            // Get information order
            SyncAdvanceOrderToPosApi::dispatch($order_id)
                ->delay($wait_time)
                ->onQueue(self::NAME_QUEUE_ADVANCE_ORDER);
            $data['status'] = self::SUCCESS;
        }
        return response()->json($data);
    }
}
