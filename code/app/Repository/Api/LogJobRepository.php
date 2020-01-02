<?php

/**
 * Created by PhpStorm.
 * User: ThieuPham
 * Date: 02/03/18
 * Time: 3:44 PM
 */

namespace App\Repository\Api;

use App\Repository\BaseRepository;
use App\Model\LogJob;
use Illuminate\Support\Facades\DB;

class LogJobRepository extends BaseRepository {

    public function __construct(LogJob $model) {
        parent::__construct($model);
    }

    public function getOrderDeliverySyncOnline($orderId)
    {
        return $this->model
            ->where(LogJob::getColumnName('order_id'), $orderId)
            ->where(LogJob::getColumnName('url'), env('CC_JUA_API_SYNC_ORDER_FOR_ONLINE'))
            ->first();
    }

}
