<?php

/**
 * Created by PhpStorm.
 * User: ThieuPham
 * Date: 02/03/18
 * Time: 3:44 PM
 */

namespace App\Repository\Api;

use App\Repository\BaseRepository;
use App\Model\Order;
use Illuminate\Support\Facades\DB;

class SyncOrderRepository extends BaseRepository {

    public function __construct(Order $model) {
        parent::__construct($model);
    }

    /*
     *
     * @Params $order_id
     * @Response object order
     * */
    public function getOrderByOrderId($order_id = null) {
        return DB::table("order")
            ->where("id", $order_id)
            ->first();
    }

    /*
     *
     * @Params $order_id
     * @Response list array order detail
     * */
    public function getOrderDetailByOrderId($order_id = null) {
        return DB::table("order_detail")
            ->where("order_id", $order_id)
            ->get()
            ->toArray();
    }

    /*
     *
     * @Params $order_id
     * @Response array orders payment
     * */
    public function getOrderPaymentByOrderId($order_id = null) {
        return DB::table("order_payment")
            ->where("order_id", $order_id)
            ->get()
            ->toArray();
    }

    /*
     *
     * @Params $order_id
     * @Response object order
     * */
    public function getOrderDeliveryByOrderId($order_id = null) {
        return DB::table("order_delivery")
            ->where("order_id", $order_id)
            ->first();
    }

    /*
     *
     * @Params $order_id
     * @Response object order log time
     * */
    public function getOrderLogTimeByOrderId($order_id = null) {
        return DB::table("order_log_time")
            ->where("order_id", $order_id)
            ->first();
    }

}
