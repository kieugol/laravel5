<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\MasterMaterialUsage;
use App\Model\Inventory\MasterPCC;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class MasterMaterialUsageRepository extends BaseRepository
{
    public function __construct(MasterMaterialUsage $model)
    {
        parent::__construct($model);
    }

    public function getTotalUsageByMaterialIds($materialIds)
    {
        $data = $this->model
            ->select([
                MasterMaterialUsage::getCol('material_id'),
                DB::raw("SUM(" . MasterMaterialUsage::getCol('usage') . ") AS total_usage"),
            ])
            ->whereIn(MasterMaterialUsage::getCol('material_id'), $materialIds)
            ->groupBy(MasterMaterialUsage::getCol('material_id'))
            ->get();

        $result = [];

        foreach ($data as $row) {
            $result[$row->material_id] = floatval($row->total_usage);
        }

        return $result;
    }

    public function getTotalUsageByMaterialIdsAndGroupByPeriod($materialIds)
    {
        $data = $this->model
            ->select([
                MasterMaterialUsage::getCol('material_id'),
                DB::raw("SUM(" . MasterMaterialUsage::getCol('usage') . ") AS total_usage"),
                MasterMaterialUsage::getCol('master_pcc_id')
            ])
            ->join(MasterPCC::getTbl(), MasterPCC::getCol('id'), "=", MasterMaterialUsage::getCol('master_pcc_id'))
            ->whereIn(MasterMaterialUsage::getCol('material_id'), $materialIds)
            ->groupBy(MasterMaterialUsage::getCol('material_id'), MasterMaterialUsage::getCol('master_pcc_id'))
            ->get();

        return $data;
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

        $items = $query_builder->get();
        return $items;
    }

}
