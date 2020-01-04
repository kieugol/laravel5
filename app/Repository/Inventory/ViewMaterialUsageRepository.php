<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 2/19/2019
 * Time: 10:35 AM
 */

namespace App\Repository\Inventory;

use App\Model\Inventory\ViewMaterialUsage;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class ViewMaterialUsageRepository extends BaseRepository
{
    public function __construct(ViewMaterialUsage $model)
    {
        parent::__construct($model);
    }

    public function getList($param)
    {
        $query_builder = $this->model->select('*');

        if (!empty($param)) {
            if (!empty($param['fromDate'])) {
                $query_builder->whereRaw("date(order_date) >= '". $param['fromDate']."'");
            }
            if (!empty($param['toDate'])) {
                $query_builder->whereRaw("date(order_date) <= '". $param['toDate']."'");
            }
        }

        $items = $query_builder->get();
        return $items;
    }
    
    public function callSpViewMaterialUsage($param)
    {
        $start_date = $param['fromDate'];
        $end_date = $param['toDate'];
        return DB::select("call sp_view_material_usage('$start_date', '$end_date', 0 , 0, 0)"); //get all
    }
}
