<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\ReturnOrder;
use App\Repository\BaseRepository;

class ReturnOrderRepository extends BaseRepository
{

    public function __construct(ReturnOrder $model)
    {
        parent::__construct($model);
    }

}
