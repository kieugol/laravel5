<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\ViewDailyReceiving;
use App\Repository\BaseRepository;

class ViewDailyReceivingRepository extends BaseRepository
{
    public function __construct(ViewDailyReceiving $model)
    {
        parent::__construct($model);
    }

    public function getListReport($param)
    {
        $query_builder = $this->model->select('*');

        if (!empty($param['fromDate']) && $param['toDate']) {
            $query_builder->whereRaw("date_time >= '". $param['fromDate']."'");
            $query_builder->whereRaw("date_time <= '". $param['toDate']."'");
        }

        $items = $query_builder->get();
        return $items;
    }
}
