<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\ReceiveOrderStatusLog;
use App\Repository\BaseRepository;

class ReceiveOrderStatusLogRepository extends BaseRepository
{

    public function __construct(ReceiveOrderStatusLog $model)
    {
        parent::__construct($model);
    }

}
