<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 2/19/2019
 * Time: 11:22 AM
 */

namespace App\Repository\Inventory;

use App\Model\Inventory\ViewCurrentStock;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class ViewCurrentStockRepository extends BaseRepository
{
    public function __construct(ViewCurrentStock $model)
    {
        parent::__construct($model);
    }

    public function getList($param)
    {
        $query_builder = DB::table("view_curent_stock as a")
            ->join("inventory_master_material as b", "a.material_id", "=", "b.id")
            ->select("a.*", "b.type_id");
        if (!empty($param)) {
            if (isset($param['type'])) {
                $query_builder->where("b.type_id", "=", $param['type']);
            }
        }

        if (!empty($param['search_value'])) {
            $query_builder->where(function ($subQuery) use ($param) {
                $column_names = ['material_code', 'material_name'];
                foreach ($column_names as $field) {
                    $subQuery->orWhere($field, 'like', "%" . $param['search_value'] . "%");
                }
            });
        }
        $items = $query_builder->get();

        return $items;
    }
}