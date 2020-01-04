<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\{CurrentStock,
    Location,
    MasterPCC,
    MasterUom,
    MasterUomDetail,
    PCC,
    StockOpname,
    StockOpnameDetail,
    MasterMaterialDetail};
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class StockOpnameDetailRepository extends BaseRepository
{
    public function __construct(StockOpnameDetail $model)
    {
        parent::__construct($model);
    }

    public function deleteByStockOpnameId($id)
    {
        return $this->model->where('stock_opname_id', $id)->delete();
    }

    public function getDetailByStockOpnameIdForUpdate($stockOpnameId)
    {
        $result =  $this->model
            ->select([
                MasterMaterialDetail::getCol('material_id'),
                StockOpnameDetail::getCol('material_detail_id'),
                MasterMaterialDetail::getCol('code'),
                MasterMaterialDetail::getCol('name'),
                MasterMaterialDetail::getCol('supplier_uom_id'),
                MasterMaterialDetail::getCol('smaller_uom_id'),
                MasterMaterialDetail::getCol('smaller_uom_detail_id'),
                MasterMaterialDetail::getCol('outlet_uom_id'),
                MasterMaterialDetail::getCol('outlet_uom_detail_id'),
                MasterMaterialDetail::getCol('report_uom_id'),
                CurrentStock::getCol('quantity_recipe AS potential_ending'),
                'uom_net_weight.name AS net_weight_name',
                DB::raw("GROUP_CONCAT(" . Location::getCol('is_display ') . ") AS group_location_display"),
                DB::raw("GROUP_CONCAT(" . StockOpnameDetail::getCol('location_id') . ") AS group_location_id"),
                DB::raw("GROUP_CONCAT(" . StockOpnameDetail::getCol('quantity_supplier_uom') . ") AS group_quantity_supplier_uom"),
                DB::raw("GROUP_CONCAT(" . StockOpnameDetail::getCol('quantity_outlet_uom') . ") AS group_quantity_outlet_uom"),
                'uom_tmp.name AS supplier_uom_description',
                MasterUom::getCol('name as report_uom_description'),
                MasterUomDetail::getCol('conversion_rate AS conversion_rate_smaller'),
                'uom_detail_tmp.conversion_rate AS conversion_rate_outlet',
                DB::raw("group_concat(" . PCC::getCol('total_available') . ") AS total_available"),
                DB::raw("group_concat(DISTINCT(" . PCC::getCol('master_pcc_id') . ")) AS master_pcc_ids"),
            ])
            ->join(StockOpname::getTbl(), StockOpname::getCol('id'), StockOpnameDetail::getCol('stock_opname_id'))
            ->join(Location::getTbl(), Location::getCol('id'), StockOpnameDetail::getCol('location_id'))
            ->join(MasterMaterialDetail::getTbl(), MasterMaterialDetail::getCol('id'), StockOpnameDetail::getCol('material_detail_id'))
            ->join(MasterUom::getTbl(), MasterUom::getCol('id'), MasterMaterialDetail::getCol('report_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_tmp', 'uom_tmp.id', MasterMaterialDetail::getCol('supplier_uom_id'))
            ->leftJoin(PCC::getTbl(), PCC::getCol('material_detail_id'), MasterMaterialDetail::getCol('id'))
            ->join(MasterUomDetail::getTbl(), MasterUomDetail::getCol('id'), MasterMaterialDetail::getCol('smaller_uom_detail_id'))
            ->join(MasterUomDetail::getTbl() . ' AS uom_detail_tmp', 'uom_detail_tmp.id', MasterMaterialDetail::getCol('outlet_uom_detail_id'))
            ->leftJoin(CurrentStock::getTbl(), CurrentStock::getCol('material_id'), MasterMaterialDetail::getCol('material_id'))
            ->join(MasterUom::getTbl() . ' AS uom_net_weight', 'uom_net_weight.id', CurrentStock::getCol('uom_id'))
            ->where(StockOpnameDetail::getCol('stock_opname_id'), $stockOpnameId)
            ->orderBy(MasterMaterialDetail::getCol('code'), 'ASC')
            ->groupBy(StockOpnameDetail::getCol('material_detail_id'))
            ->get();
    
        return $result;
    }


    public function getDetailByStockOpnameIdForReport($stockOpnameId)
    {
        return $this->model
            ->select([
                MasterMaterialDetail::getCol('material_id'),
                StockOpnameDetail::getCol('material_detail_id'),
                MasterMaterialDetail::getCol('code'),
                MasterMaterialDetail::getCol('name'),
                StockOpname::getCol('pcc_id'),
                MasterMaterialDetail::getCol('supplier_uom_id'),
                MasterMaterialDetail::getCol('smaller_uom_id'),
                MasterMaterialDetail::getCol('outlet_uom_id'),
                MasterMaterialDetail::getCol('recipe_uom_id'),
                MasterMaterialDetail::getCol('smaller_uom_detail_id'),
                MasterMaterialDetail::getCol('outlet_uom_detail_id'),
                MasterMaterialDetail::getCol('recipe_uom_detail_id'),
                MasterMaterialDetail::getCol('report_uom_id'),
                DB::raw("GROUP_CONCAT(" . StockOpnameDetail::getCol('location_id') . ") AS group_location_id"),
                DB::raw("GROUP_CONCAT(" . Location::getCol('name') . ") AS group_location_name"),
                DB::raw("GROUP_CONCAT(" . StockOpnameDetail::getCol('quantity_convert') . ") AS group_quantity_report_uom"),
                'uom_supplier.name AS supplier_uom_description',
                'uom_smaller.name AS smaller_uom_description',
                'uom_outlet.name AS outlet_uom_description',
                'uom_recipe.name AS recipe_uom_description',
                MasterUom::getCol('name as report_uom_description'),
                MasterUomDetail::getCol('conversion_rate AS conversion_rate_smaller'),
                'uom_detail_outlet.conversion_rate AS conversion_rate_outlet'
            ])
            ->join(Location::getTbl(), Location::getCol('id'), StockOpnameDetail::getCol('location_id'))
            ->join(MasterMaterialDetail::getTbl(), MasterMaterialDetail::getCol('id'), StockOpnameDetail::getCol('material_detail_id'))
            ->join(MasterUom::getTbl(), MasterUom::getCol('id'), MasterMaterialDetail::getCol('report_uom_id'))
            ->join(StockOpname::getTbl(), StockOpname::getCol('id'), StockOpnameDetail::getCol('stock_opname_id'))
            ->join(MasterPCC::getTbl(), MasterPCC::getCol('id'), StockOpname::getCol('pcc_id'))
            ->join(MasterUom::getTbl() . ' AS uom_supplier', 'uom_supplier.id', MasterMaterialDetail::getCol('supplier_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_smaller', 'uom_smaller.id', MasterMaterialDetail::getCol('smaller_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_outlet', 'uom_outlet.id', MasterMaterialDetail::getCol('outlet_uom_id'))
            ->join(MasterUom::getTbl() . ' AS uom_recipe', 'uom_recipe.id', MasterMaterialDetail::getCol('recipe_uom_id'))
            ->join(MasterUomDetail::getTbl(), MasterUomDetail::getCol('id'), MasterMaterialDetail::getCol('smaller_uom_detail_id'))
            ->join(MasterUomDetail::getTbl() . ' AS uom_detail_outlet', 'uom_detail_outlet.id', MasterMaterialDetail::getCol('outlet_uom_detail_id'))
            ->where(StockOpnameDetail::getCol('stock_opname_id'), $stockOpnameId)
            ->orderBy(StockOpnameDetail::getCol('id'), 'ASC')
            ->groupBy(StockOpnameDetail::getCol('material_detail_id'))
            ->get();
    }
}
