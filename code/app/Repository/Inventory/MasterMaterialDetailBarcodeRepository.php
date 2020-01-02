<?php

namespace App\Repository\Inventory;

use App\Model\Inventory\MasterMaterialDetail;
use App\Model\Inventory\MasterMaterialDetailBarcode;
use App\Repository\BaseRepository;

class MasterMaterialDetailBarcodeRepository extends BaseRepository
{
    public function __construct(MasterMaterialDetailBarcode $model)
    {
        parent::__construct($model);
    }

    public function getByBarcode($barcode)
    {
        $query = $this->model
            ->select([
                MasterMaterialDetail::getCol('id'),
                MasterMaterialDetail::getCol('code'),
                MasterMaterialDetail::getCol('name')
            ])
            ->join(MasterMaterialDetail::getTbl(), MasterMaterialDetail::getCol('id'), MasterMaterialDetailBarcode::getCol('material_detail_id'))
            ->where(MasterMaterialDetailBarcode::getCol('is_active'), STATUS_ACTIVE)
            ->where(MasterMaterialDetailBarcode::getCol('barcode'), $barcode);
            return $query->first();
    }

}
