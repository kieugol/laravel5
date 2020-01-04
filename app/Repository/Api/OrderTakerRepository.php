<?php

namespace App\Repository\Api;

use App\Repository\BaseRepository;
use App\Model\OrderTaker;

class OrderTakerRepository extends BaseRepository
{
    public function __construct(OrderTaker $model) {
        parent::__construct($model);
    }

    public function getOrderTakerByCode($code)
    {
        return $this->model
            ->where('order_taker_code', $code)
            ->where('is_active', STATUS_ACTIVE)
            ->first();
    }
}
