<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\CurrentStock;
use App\Model\Inventory\CurrentStockRecipe;
use App\Repository\BaseRepository;

class CurrentStockRepository extends BaseRepository
{

    public function __construct(CurrentStock $model)
    {
        parent::__construct($model);
    }
    
    public function getAllByMaterialIds($materialIds)
    {
        return $this->model
            ->select("*")
            ->whereIn(CurrentStockRecipe::getCol('material_id'), $materialIds)
            ->get();
    }
}
