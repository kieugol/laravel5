<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\ReturnOrderStatusLog;
use App\Repository\BaseRepository;

class ReturnOrderStatusLogRepository extends BaseRepository
{

    public function __construct(ReturnOrderStatusLog $model)
    {
        parent::__construct($model);
    }

}
