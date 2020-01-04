<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 2/19/2019
 * Time: 10:35 AM
 */

namespace App\Repository\Inventory;

use App\Model\Inventory\ViewMaterialUsage;
use App\Model\Inventory\ViewSummaryMaterialUsage;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class ViewSummaryMaterialUsageRepository extends BaseRepository
{
    public function __construct(ViewSummaryMaterialUsage $model)
    {
        parent::__construct($model);
    }

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
