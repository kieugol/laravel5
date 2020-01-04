<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\{StockOpname, StockOpnameRecipe, StockOpnameSummary};
use App\Repository\BaseRepository;

class StockOpnameSummaryRepository extends BaseRepository
{

    public function __construct(StockOpnameSummary $model)
    {
        parent::__construct($model);
    }


    public function deleteByStockOpnameId($id)
    {
        return $this->model->where('stock_opname_id', $id)->delete();
    }

    public function getDetailByStockOpnameId($stockOpnameId)
    {
        $result = $this->model
            ->select([
                StockOpnameSummary::getCol('*'),
                StockOpnameRecipe::getCol('recipe_detail_id'),
            ])
            ->leftJoin(StockOpnameRecipe::getTbl(), function ($join) use($stockOpnameId) {
                $join->on(StockOpnameRecipe::getCol('material_detail_id'), '=', StockOpnameSummary::getCol('material_detail_id'))
                    ->where(StockOpnameRecipe::getCol('stock_opname_id'), $stockOpnameId);
            })
            ->where(StockOpnameSummary::getCol('stock_opname_id'), $stockOpnameId)
            ->groupBy(StockOpnameSummary::getCol('material_detail_id'))
            ->get();

        return $result;
    }

    public function parseStockSummaryData($data)
    {
        foreach ($data as &$item) {
            $item->material_detail_code = $item->material_detail->code;
            $item->material_detail_name = $item->material_detail->name;
            unset($item->material_detail);
        }

        return !empty($data) ? $data->toArray() : [];
    }

}
