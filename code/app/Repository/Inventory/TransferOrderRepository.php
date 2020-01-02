<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\TransferOrder;
use App\Repository\BaseRepository;

class TransferOrderRepository extends BaseRepository
{

    public function __construct(TransferOrder $model)
    {
        parent::__construct($model);
    }

}
