<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\{StockOpnameRecipe};
use App\Repository\BaseRepository;

class StockOpnameRecipeRepository extends BaseRepository
{
    
    public function __construct(StockOpnameRecipe $model)
    {
        parent::__construct($model);
    }
    
    public function filterDataCreate($data)
    {
        $result = [];
        
        foreach ($data as $row) {
            $isValid = ($row['material_detail_id'] ?? 0);
            if ($isValid) {
                $result[] = [
                    'recipe_id'           => $row['recipe_id'],
                    'recipe_detail_id'    => $row['recipe_detail_id'],
                    'recipe_qty'          => $row['recipe_qty'],
                    'material_id'         => $row['material_id'],
                    'material_detail_id'  => $row['material_detail_id'],
                    'material_detail_qty' => $row['material_detail_qty'],
                ];
            }
        }
        return $result;
    }
    
    public function getMappingRecipeDataByStockOpnameId($stockOpnameId)
    {
        $result = [];
        $arrRecipe = [];
        
        $data = $this->findByAttributes(['stock_opname_id' => $stockOpnameId]);
        
        foreach ($data as $row) {
            $result[$row['recipe_id']][$row['recipe_detail_id']][$row['material_id']] = [
                'material_detail_id'  => $row['material_detail_id'],
                'material_detail_qty' => $row['material_detail_qty'],
                'recipe_qty'          => $row['recipe_qty'],
            ];
            $arrRecipe[$row['recipe_id']] = $row['recipe_id'];
        }
    
        $dataTmp = $this->model->whereNotIn(StockOpnameRecipe::getCol('recipe_id'), $arrRecipe)->get();
        foreach ($dataTmp as $row) {
            $result[$row['recipe_id']][$row['recipe_detail_id']][$row['material_id']] = [
                'material_detail_id'  => $row['material_detail_id'],
                'material_detail_qty' => 0,
                'recipe_qty'          => 0,
            ];
        }
        
        
        return $result;
    }
    
    public function deleteByStockOpnameId($id)
    {
        return $this->model->where('stock_opname_id', $id)->delete();
    }
}
