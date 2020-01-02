<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/25/2019
 * Time: 2:21 PM
 */

namespace App\Repository\Inventory;


use App\Model\Inventory\DailyBatch;
use App\Model\Inventory\MasterRecipe;
use App\Model\Inventory\MasterUom;
use App\Repository\BaseRepository;

class DailyBatchRepository extends BaseRepository
{
    public function __construct(DailyBatch $model)
    {
        parent::__construct($model);
    }
    
    public function getUomByRecipeId()
    {
        $masterRecipe = MasterRecipe::getTbl();
        $masterUom    = MasterUom::getTbl();
        
        return $this->model
            ->select([
                DailyBatch::getCol('id AS daily_batch_id'),
                MasterRecipe::getCol('id AS recipe_id'),
                MasterRecipe::getCol('name AS recipe_name'),
                MasterUom::getCol('id AS uom_id'),
                MasterUom::getCol('name AS uom_name')
            ])
            ->join($masterRecipe, MasterRecipe::getCol('id'), '=', DailyBatch::getCol('recipe_id'))
            ->join($masterUom, MasterUom::getCol('id'), '=', MasterRecipe::getCol('uom_id'))
            ->get();
            
    }
}
