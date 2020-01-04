<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\PurchaseOrderStatusLog;
use App\Repository\BaseRepository;

class PurchaseOrderStatusLogRepository extends BaseRepository
{

    public function __construct(PurchaseOrderStatusLog $model)
    {
        parent::__construct($model);
    }

}
