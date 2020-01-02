<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\TransferOrderStatusLog;
use App\Repository\BaseRepository;

class TransferOrderStatusLogRepository extends BaseRepository
{

    public function __construct(TransferOrderStatusLog $model)
    {
        parent::__construct($model);
    }

}
