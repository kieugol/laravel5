<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 4/1/2019
 * Time: 11:41 AM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\ViewCurrentStockRecipe;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class ViewCurrentStockRecipeRepository extends BaseRepository
{
    public function __construct(ViewCurrentStockRecipe $model)
    {
        parent::__construct($model);
    }

    public function getList($param)
    {
        $query_builder = DB::table("view_current_stock_recipe");

        if (!empty($param['search_value'])) {
            $query_builder->where(function ($subQuery) use ($param) {
                $column_names = ['recipe_code', 'name'];
                foreach ($column_names as $field) {
                    $subQuery->orWhere($field, 'like', "%" . $param['search_value'] . "%");
                }
            });
        }
        $items = $query_builder->get();

        return $items;
    }
}