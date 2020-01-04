<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\MasterMaterialUsageReport;
use App\Repository\BaseRepository;

class MasterMaterialUsageReportRepository extends BaseRepository
{
    public function __construct(MasterMaterialUsageReport $model)
    {
        parent::__construct($model);
    }

    /**
     * get list based on params
     * @param $param
     * @return mixed
     */
    public function getList($param)
    {
        $query_builder = $this->model->select('*');

        if (!empty($param)) {
            if (!empty($param['fromDate'])) {
                $query_builder->whereRaw("date(created_date) >= '". $param['fromDate']."'");
            }
            if (!empty($param['toDate'])) {
                $query_builder->whereRaw("date(created_date) <= '". $param['toDate']."'");
            }
        }

        $query_builder->where('is_report', 1);

        $query_builder->orderBy('created_date', 'desc');

        $items = $query_builder->get();
        return $items;
    }

}
