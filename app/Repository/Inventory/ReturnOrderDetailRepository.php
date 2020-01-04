<?php

namespace App\Repository\Inventory;

use App\Helpers\FileHelper;
use App\Model\Inventory\MasterAccount;
use App\Model\Inventory\MasterMaterialDetail;
use App\Model\Inventory\MasterSupplier;
use App\Model\Inventory\MasterUom;
use App\Model\Inventory\MasterUomDetail;
use App\Model\Inventory\ReturnOrder;
use App\Model\Inventory\ReturnOrderDetail;
use App\Model\Outlet;
use App\Repository\BaseRepository;

class ReturnOrderDetailRepository extends BaseRepository
{

    public function __construct(ReturnOrderDetail $model)
    {
        parent::__construct($model);
    }

    public function getByReturnId($return_id)
    {
        $returnDetailTbl   = ReturnOrderDetail::getTbl();
        $returnTbl         = ReturnOrder::getTbl();
        $materialDetailTbl = MasterMaterialDetail::getTbl();
        $uomTbl            = MasterUom::getTbl();
        $uomDetailTbl      = MasterUomDetail::getTbl();
        $accountTbl        = MasterAccount::getTbl();
        $supplierTbl       = MasterSupplier::getTbl();

        return $this->model->from($returnDetailTbl)
            ->select(
                "$returnTbl.id as return_id",
                "$returnTbl.invoice_number",
                "$returnTbl.total as return_total",
                "$returnTbl.created_date as created_date",
                "$returnDetailTbl.quantity",
                "$returnDetailTbl.price",
                "$returnDetailTbl.total",
                "$returnDetailTbl.account_id",
                "$materialDetailTbl.code as material_detail_code",
                "$materialDetailTbl.name as material_detail_name",
                "$uomDetailTbl.name as smaller_uom_detail_name",
                "$uomDetailTbl.id as smaller_uom_detail_id",
                "$uomTbl.id as uom_id",
                "$uomTbl.name as uom_name",
                "$accountTbl.code as account_code",
                "$accountTbl.name as account_name",
                "$supplierTbl.name as supplier_name",
                "$supplierTbl.code as supplier_code"
            )
            ->join($returnTbl, "$returnDetailTbl.return_id", "=", "$returnTbl.id")
            ->join($materialDetailTbl, "$returnDetailTbl.material_detail_id", "=", "$materialDetailTbl.id")
            ->join($uomDetailTbl, "$materialDetailTbl.smaller_uom_detail_id", "=", "$uomDetailTbl.id")
            ->join($uomTbl, "$returnDetailTbl.uom_id", "=", "$uomTbl.id")
            ->join($accountTbl, "$returnDetailTbl.account_id", "=", "$accountTbl.id")
            ->join($supplierTbl, "$returnTbl.supplier_id", "=", "$supplierTbl.id")
            ->where("$returnDetailTbl.return_id", $return_id)
            ->get();
    }

    public function createCSV($return_id, $store_code)
    {
        $return         = [];
        $sub_path       = date("Y/m/d");
        $flag_folder    = FileHelper::create_sub_folder(INVENTORY_DIR_RETURN_ORDER_CSV, $sub_path);
        $return_details = $this->getByReturnId($return_id);
        $file_name      = rename_unique(INVENTORY_DIR_RETURN_ORDER_CSV . "/$sub_path", $store_code . CSV_NAME_RETURN);
        $arr_csv        = [];
        foreach ($return_details as $return_detail) {

            $material_code = substr($return_detail->material_detail_code, 0, -1);
            $code          = substr($return_detail->material_detail_code, -1);

            $arr_csv[$return_detail->material_detail_code] = array(
                'store_code'           => $store_code,
                'id'                   => $return_detail->return_id,
                'date'                 => strtotime(date('Y-m-d h:i:s', strtotime(date('Y-m-d', strtotime($return_detail->created_date))))),
                'supplier_code'        => $return_detail->supplier_code,
                'invoice_number'       => $return_detail->invoice_number,
                'material_code'        => $material_code,
                'material_detail_code' => $code,
                'quantity'             => $return_detail->quantity,
                'unit'                 => $return_detail->uom_id,
                'price'                => $return_detail->price
            );

        }
        $fp = fopen($flag_folder . '/' . $file_name, "w");
        foreach ($arr_csv as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        $return['path']      = $flag_folder;
        $return['file_name'] = $file_name;
        return $return;
    }

}
