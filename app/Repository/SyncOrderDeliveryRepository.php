<?php

namespace App\Repository;


use App\Model\SyncOrderDelivery;

class SyncOrderDeliveryRepository extends BaseRepository
{
    const LIMIT_ADD_TO_QUEUE = 72000; // About 20 Mins

    public function __construct(SyncOrderDelivery $model)
    {
        parent::__construct($model);
    }

    public function findByOrderId($oderId)
    {
        return $this->model->where(SyncOrderDelivery::getColumnName('order_id'), $oderId)->first();
    }

    public function getListOrderIsNotSync()
    {
        return $this->model
            ->select(SyncOrderDelivery::getColumnName('*'))
            ->where(SyncOrderDelivery::getColumnName('is_sync'), STATUS_INACTIVE)
            ->where(SyncOrderDelivery::getColumnName('number_of_retry'), '<=', self::LIMIT_ADD_TO_QUEUE)
            ->get();
    }
}
