<?php

namespace App\Repository\Inventory;

use App\Helpers\FileHelper;
use App\Helpers\PosHelper;
use App\Repository\BaseRepository;
use App\Model\Inventory\{
    PurchaseOrderDetail,
    PurchaseOrder,
    MasterMaterial,
    MasterMaterialDetail,
    MasterSupplier,
    MasterUom,
    MasterAccount,
    MasterUomDetail
};

class PurchaseOrderDetailRepository extends BaseRepository
{

    public function __construct(PurchaseOrderDetail $model)
    {
        parent::__construct($model);
    }

    public function getByPOId($POId)
    {
        return $this->model
            ->select([
                MasterMaterialDetail::getCol('id'),
                PurchaseOrderDetail::getCol('id AS po_id'),
                MasterMaterialDetail::getCol('code'),
                MasterMaterialDetail::getCol('name'),
                MasterMaterialDetail::getCol('material_id'),
                MasterUomDetail::getCol('name AS smaller_uom_detail_name'),
                MasterUomDetail::getCol('conversion_rate as smaller_uom_conversion_rate'),
                PurchaseOrderDetail::getCol('uom_id'),
                PurchaseOrderDetail::getCol('quantity'),
                PurchaseOrderDetail::getCol('price'),
                'supplier_uom.id AS supplier_uom_id',
                'smaller_uom.id AS smaller_uom_id',
                'supplier_uom.name AS supplier_uom_name',
                'smaller_uom.name AS smaller_uom_name',
                MasterAccount::getCol('id AS account_id'),
                MasterAccount::getCol('code AS account_code'),
                MasterAccount::getCol('name AS account_name'),
                MasterSupplier::getCol('is_import_do')
            ])
            ->join(MasterMaterial::getTbl(), MasterMaterial::getCol('id'), PurchaseOrderDetail::getCol('material_id'))
            ->join(MasterAccount::getTbl(), MasterAccount::getCol('id'), MasterMaterial::getCol('account_id'))
            ->join(MasterMaterialDetail::getTbl(), MasterMaterialDetail::getCol('id'), PurchaseOrderDetail::getCol('material_detail_id'))
            ->join(MasterUom::getTbl() . ' as supplier_uom', 'supplier_uom.id', MasterMaterialDetail::getCol('supplier_uom_id'))
            ->join(MasterUom::getTbl() . ' as smaller_uom', 'smaller_uom.id', MasterMaterialDetail::getCol('smaller_uom_id'))
            ->join(MasterUomDetail::getTbl(), MasterUomDetail::getCol('id'), MasterMaterialDetail::getCol('smaller_uom_detail_id'))
            ->join(MasterSupplier::getTbl(), MasterSupplier::getCol('id'), MasterMaterialDetail::getCol('supplier_id'))
            ->where(PurchaseOrderDetail::getCol('purchase_id'), $POId)
            ->get();
    }

    public function createCSV($id, $storeCode)
    {
        $result       = [];
        $csvData      = [];
        $supplierCode = null;
        $deliveryDate = null;
        $subPath      = date("Y/m/d");
        $flagFolder   = FileHelper::create_sub_folder(INVENTORY_DIR_PURCHASE_ORDER_CSV, $subPath);
        $poDetail     = $this->findByAttributes(['purchase_id' => $id]);

        foreach ($poDetail as $item) {
            $csvData[]    = [
                'store_code'           => $storeCode,
                'material_detail_code' => $item->material_detail->code,
                'quantity'             => $item->quantity,
                'uom_id'               => $item->uom_id,
            ];
            $supplierCode = $item->purchase->supplier->code;
            $deliveryDate = $item->purchase->delivery_date;
        }

        $fileNameCSV = $storeCode . "-" . $supplierCode . "-" . date('d-m-Y', strtotime($deliveryDate)) . $id . ".csv";
        $fileName    = PosHelper::generateCSV($csvData, $flagFolder, $fileNameCSV);

        $result['path']      = $flagFolder;
        $result['file_name'] = $fileName;

        return $result;
    }

    public function deleteByIds(array $ids)
    {

        return $this->model
            ->whereIn(PurchaseOrderDetail::getCol('id'), $ids)
            ->delete();
    }

    public function deleteEmptyQtyByPOId($id)
    {

        return $this->model
            ->where(PurchaseOrderDetail::getCol('purchase_id'), $id)
            ->where(PurchaseOrderDetail::getCol('quantity'), 0)
            ->delete();
    }
}
