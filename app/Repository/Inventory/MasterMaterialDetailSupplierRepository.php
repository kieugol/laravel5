<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\MasterAccount;
use App\Model\Inventory\MasterMaterial;
use App\Model\Inventory\MasterMaterialDetail;
use App\Model\Inventory\MasterMaterialDetailSupplier;
use App\Model\Inventory\MasterSupplier;
use App\Model\Inventory\MasterUom;
use App\Model\Inventory\MasterUomDetail;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class MasterMaterialDetailSupplierRepository extends BaseRepository
{
    public function __construct(MasterMaterialDetailSupplier $model)
    {
        parent::__construct($model);
    }

    public function getBySupplierId($id = null)
    {
        $query = $this->model
            ->select([
                MasterMaterialDetail::getCol('id'),
                MasterMaterialDetail::getCol('code'),
                MasterMaterialDetail::getCol('name'),
                MasterMaterialDetail::getCol('price'),
                MasterMaterialDetail::getCol('material_id'),
                MasterUomDetail::getCol('name AS smaller_uom_detail_name'),
                MasterUomDetail::getCol('conversion_rate as smaller_uom_conversion_rate'),
                DB::raw('0 as quantity'),
                'supplier_uom.id AS supplier_uom_id',
                'smaller_uom.id AS smaller_uom_id',
                'supplier_uom.name AS supplier_uom_name',
                'smaller_uom.name AS smaller_uom_name',
                MasterAccount::getCol('id AS account_id'),
                MasterAccount::getCol('code AS account_code'),
                MasterAccount::getCol('name AS account_name')
            ])
            ->join(MasterMaterialDetail::getTbl(), MasterMaterialDetail::getCol('id'), MasterMaterialDetailSupplier::getCol('material_detail_id'))
            ->join(MasterMaterial::getTbl(), MasterMaterial::getCol('id'), MasterMaterialDetail::getCol('material_id'))
            ->join(MasterAccount::getTbl(), MasterAccount::getCol('id'), MasterMaterial::getCol('account_id'))
            ->join(MasterSupplier::getTbl(), MasterSupplier::getCol('id'), MasterMaterialDetail::getCol('supplier_id'))
            ->join(MasterUom::getTbl() . ' as supplier_uom', 'supplier_uom.id',  MasterMaterialDetail::getCol('supplier_uom_id'))
            ->join(MasterUom::getTbl() . ' as smaller_uom', 'smaller_uom.id',  MasterMaterialDetail::getCol('smaller_uom_id'))
            ->join(MasterUomDetail::getTbl(), MasterUomDetail::getCol('id'), MasterMaterialDetail::getCol('smaller_uom_detail_id'))
            ->where(MasterMaterialDetail::getCol('is_active'), STATUS_ACTIVE);
        if (empty($id)) {
            $query->groupBy(MasterMaterialDetail::getCol('id'));
            return $query->get();
        } else {
            return $query->where(MasterMaterialDetailSupplier::getCol('supplier_id'), $id)->get();
        }
    }

}
