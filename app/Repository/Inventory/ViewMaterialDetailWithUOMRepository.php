<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\ViewMaterialDetailWithUOM;
use App\Repository\BaseRepository;

class ViewMaterialDetailWithUOMRepository extends BaseRepository
{
    public function __construct(ViewMaterialDetailWithUOM $model)
    {
        parent::__construct($model);
    }

    public function getListByMaterialDetailIds($materialDetailIds)
    {
        return $this->model
            ->select([
                'material_id',
                'material_code',
                'material_detail_id',
                'material_detail_code',
                'report_unit',
                'supplier_unit',
                'smaller_unit',
                'outlet_unit',
                'conversion_supplier_to_recipe',
                'conversion_smaller_to_recipe',
                'conversion_outlet_to_recipe',
            ])
            ->whereIn(ViewMaterialDetailWithUOM::getCol('material_detail_id'), $materialDetailIds)
            ->get();
        
    }
    
    
    public function GetDataConversationByMaterialDetailId($materialDetailIds)
    {
        $data = $this->getListByMaterialDetailIds($materialDetailIds);
        
        $dataFilter = [];
        foreach ($data as $row) {
            switch ($row->report_unit) {
                case $row->supplier_unit:
                    $conversationRate = $row->conversion_supplier_to_recipe;
                    break;
                case $row->smaller_unit:
                    $conversationRate = $row->conversion_smaller_to_recipe;
                    break;
                case $row->outlet_unit:
                    $conversationRate = $row->conversion_outlet_to_recipe;
                    break;
                default:
                    $conversationRate = 1;
            }
            
            $dataFilter[$row->material_detail_id] = $conversationRate;
        }
        
        return $dataFilter;
    }
}
