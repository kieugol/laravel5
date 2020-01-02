<?php

namespace App\Repository\Inventory;

use App\Helpers\FileHelper;
use App\Model\Inventory\CurrentStock;
use App\Model\Inventory\MasterAccount;
use App\Model\Inventory\MasterMaterialDetail;
use App\Model\Inventory\MasterSupplier;
use App\Model\Inventory\MasterUom;
use App\Model\Inventory\MasterUomDetail;
use App\Model\Inventory\ReceiveOrder;
use App\Model\Inventory\ReceiveOrderDetail;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class ReceiveOrderDetailRepository extends BaseRepository
{
    public function __construct(ReceiveOrderDetail $model)
    {
        parent::__construct($model);
    }

    public function deleteByReceiveId($receive_id)
    {
        return $this->model->where('receive_id', $receive_id)->delete();
    }

    public function getByReceiveId($receive_id)
    {
        $receiveDetailTbl  = ReceiveOrderDetail::getTbl();
        $receiveTbl        = ReceiveOrder::getTbl();
        $materialDetailTbl = MasterMaterialDetail::getTbl();
        $uomTbl            = MasterUom::getTbl();
        $uomDetailTbl      = MasterUomDetail::getTbl();
        $accountTbl        = MasterAccount::getTbl();
        $supplierTbl       = MasterSupplier::getTbl();

        return $this->model->from($receiveDetailTbl)
            ->select(
                "$receiveTbl.id as receive_id",
                "$receiveTbl.invoice_number",
                "$receiveTbl.total as receive_total",
                "$receiveTbl.created_date as created_date",
                "$receiveDetailTbl.quantity",
                "$receiveDetailTbl.price",
                "$receiveDetailTbl.total",
                "$materialDetailTbl.material_id",
                "$materialDetailTbl.id as material_detail_id",
                "$materialDetailTbl.code as material_detail_code",
                "$materialDetailTbl.name as material_detail_name",
                'supplier_uom.id AS supplier_uom_id',
                'smaller_uom.id AS smaller_uom_id',
                'supplier_uom.name AS supplier_uom_name',
                'smaller_uom.name AS smaller_uom_name',
                'uom.id AS uom_id',
                'uom.name AS uom_name',
                "$materialDetailTbl.smaller_uom_id",
                "$uomDetailTbl.name as smaller_uom_detail_name",
                "$uomDetailTbl.id as smaller_uom_detail_id",
                "$uomDetailTbl.conversion_rate as smaller_uom_conversion_rate",
                "$accountTbl.id as account_id",
                "$accountTbl.code as account_code",
                "$accountTbl.name as account_name",
                "$supplierTbl.name as supplier_name",
                "$supplierTbl.code as supplier_code"
            )
            ->join($receiveTbl, "$receiveTbl.id", "=", "$receiveDetailTbl.receive_id")
            ->join($materialDetailTbl, "$materialDetailTbl.id", "=", "$receiveDetailTbl.material_detail_id")
            ->join($uomTbl . ' as supplier_uom', 'supplier_uom.id', "=", "$materialDetailTbl.supplier_uom_id")
            ->join($uomTbl . ' as smaller_uom', 'smaller_uom.id', "=", "$materialDetailTbl.smaller_uom_id")
            ->join($uomTbl . ' as uom', 'uom.id', "=", "$receiveDetailTbl.uom_id")
            ->join($uomDetailTbl, "$uomDetailTbl.id", "=", "$materialDetailTbl.smaller_uom_detail_id")
            ->join($accountTbl, "$accountTbl.id", "=", "$receiveDetailTbl.account_id")
            ->join($supplierTbl, "$supplierTbl.id", "=", "$receiveTbl.supplier_id")
            ->where("$receiveDetailTbl.receive_id", $receive_id)
            ->get();
    }

    public function getLatestPriceByMaterialDetailIds($material_detail_ids)
    {
        $result = DB::table('inventory_receive_detail')
            ->select('material_detail_id', 'price')
            ->whereIn('id', function ($query) use ($material_detail_ids) {
                $query->select(DB::raw('max(id)'))
                    ->from('inventory_receive_detail')
                    ->whereIn('material_detail_id', $material_detail_ids)
                    ->groupBy('material_detail_id');
            })
            ->get();
        return $result;
    }

    /**
     * get the latest approved receive order of a material
     * @param $material_detail_id
     * @return mixed
     */
    public function getLatestReceive($material_detail_id)
    {
        $receiveTbl        = ReceiveOrder::getTbl();
        $materialDetailTbl = MasterMaterialDetail::getTbl();

        $result = DB::table('inventory_receive_detail as a')
            ->select('a.price', 'a.quantity', 'c.code', 'c.name', 'a.uom_id')
            ->join($receiveTbl . ' as b', 'a.receive_id', "=", "b.id")
            ->join($materialDetailTbl . ' as c', 'a.material_detail_id', "=", "c.id")
            ->where('a.material_detail_id', $material_detail_id)
            ->where('b.status_id', TRANSACTION_ORDER_STATUS_APPROVED)
            ->orderBy('a.id', 'DESC')
            ->first();

        return $result;
    }

    public function createCSV($receive_id, $store_code)
    {
        $return          = [];
        $sub_path        = date("Y/m/d");
        $flag_folder     = FileHelper::create_sub_folder(INVENTORY_DIR_RECEIVE_ORDER_CSV, $sub_path);
        $receive_details = $this->getByReceiveId($receive_id);
        $file_name       = rename_unique(INVENTORY_DIR_RECEIVE_ORDER_CSV . "/$sub_path", $store_code . CSV_NAME_RECEIVE);
        $arr_csv         = [];
        foreach ($receive_details as $receive_detail) {

            $material_code = substr($receive_detail->material_detail_code, 0, -1);
            $code          = substr($receive_detail->material_detail_code, -1);
            if ($receive_detail->quantity > 0) {
                $arr_csv[$receive_detail->material_detail_code] = array(
                    'store_code'           => $store_code,
                    'id'                   => $receive_detail->receive_id,
                    'date'                 => strtotime(date('Y-m-d h:i:s', strtotime(date('Y-m-d', strtotime($receive_detail->created_date))))),
                    'supplier_code'        => $receive_detail->supplier_code,
                    'invoice_number'       => $receive_detail->invoice_number,
                    'material_code'        => $material_code,
                    'material_detail_code' => $code,
                    'quantity'             => $receive_detail->quantity,
                    'unit'                 => $receive_detail->uom_id,
                    'price'                => $receive_detail->price
                );
            }

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
    
    public function getLastestPriceAndQty($receive_id, $material_detail_ids)
    {
        $receiveDetailTbl  = ReceiveOrderDetail::getTbl();
        $receiveTbl        = ReceiveOrder::getTbl();
        
        return $this->model->from($receiveDetailTbl)
            ->select(
                "$receiveDetailTbl.quantity",
                "$receiveDetailTbl.material_detail_id",
                "$receiveDetailTbl.uom_id",
                "$receiveDetailTbl.price",
                "$receiveDetailTbl.total",
                "$receiveTbl.created_date as receive_created_date",
                "$receiveTbl.status_id as receive_status_id"
            )
            ->join($receiveTbl, "$receiveTbl.id", "=", "$receiveDetailTbl.receive_id")
            ->where("$receiveDetailTbl.receive_id", '!=', $receive_id)
            ->whereIn('material_detail_id', $material_detail_ids)
            ->where("$receiveTbl.status_id", TRANSACTION_ORDER_STATUS_APPROVED)
            ->orderBy("$receiveTbl.updated_date", 'DESC')
            ->get();
    }
}
