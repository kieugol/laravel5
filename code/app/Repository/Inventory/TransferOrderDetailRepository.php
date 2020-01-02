<?php

namespace App\Repository\Inventory;

use App\Helpers\FileHelper;
use App\Model\Inventory\MasterAccount;
use App\Model\Inventory\MasterMaterialDetail;
use App\Model\Inventory\MasterSupplier;
use App\Model\Inventory\MasterUom;
use App\Model\Inventory\MasterUomDetail;
use App\Model\Inventory\TransferOrder;
use App\Model\Inventory\TransferOrderDetail;
use App\Model\Outlet;
use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;

class TransferOrderDetailRepository extends BaseRepository
{

    public function __construct(TransferOrderDetail $model)
    {
        parent::__construct($model);
    }

    public function getByTransferId($transfer_id)
    {
        $transferDetailTbl = TransferOrderDetail::getTbl();
        $transferTbl       = TransferOrder::getTbl();
        $materialDetailTbl = MasterMaterialDetail::getTbl();
        $uomDetailTbl      = MasterUomDetail::getTbl();
        $uomTbl            = MasterUom::getTbl();
        $accountTbl        = MasterAccount::getTbl();
        $supplierTbl       = MasterSupplier::getTbl();
        $outletTbl         = Outlet::getTbl();
        return $this->model->from($transferDetailTbl)
            ->select(
                "from_outlet.code as from_outlet_code",
                "to_outlet.code as to_outlet_code",
                "$transferTbl.id as transfer_id",
                "$transferTbl.invoice_number",
                "$transferTbl.total as transfer_total",
                "$transferTbl.created_date",
                "$transferDetailTbl.quantity",
                "$transferDetailTbl.price",
                "$transferDetailTbl.total",
                "$transferDetailTbl.account_id",
                "$materialDetailTbl.code as material_detail_code",
                "$materialDetailTbl.name as material_detail_name",
                "$materialDetailTbl.outlet_uom_id",
                "$uomDetailTbl.name as smaller_uom_detail_name",
                "$uomDetailTbl.id as smaller_uom_detail_id",
                "$accountTbl.id as account_id",
                "$accountTbl.code as account_code",
                "$accountTbl.name as account_name",
                "$supplierTbl.name as supplier_name",
                "$supplierTbl.code as supplier_code",
                "$uomTbl.name as uom_name",
                "$uomTbl.id as uom_id"
            )
            ->join($transferTbl, "$transferDetailTbl.transfer_id", "=", "$transferTbl.id")
            ->join($materialDetailTbl, "$materialDetailTbl.id", "=", "$transferDetailTbl.material_detail_id")
            ->join($uomDetailTbl, "$uomDetailTbl.id", "=", "$materialDetailTbl.outlet_uom_detail_id")
            ->join($uomTbl, "$uomTbl.id", "=", "$transferDetailTbl.uom_id")
            ->join($accountTbl, "$accountTbl.id", "=", "$transferDetailTbl.account_id")
            ->join($supplierTbl, "$supplierTbl.id", "=", "$materialDetailTbl.supplier_uom_id")
            ->join("$outletTbl as from_outlet", "from_outlet.id", "=", "$transferTbl.from_outlet_id")
            ->join("$outletTbl as to_outlet", "to_outlet.id", "=", "$transferTbl.to_outlet_id")
            ->where("$transferDetailTbl.transfer_id", $transfer_id)
            ->get();
    }

    public function getListReport($param, $transferType = 0)
    {
        $columnDate = TransferOrder::getCol('created_date');
        $query_builder = $this->model
            ->select([
                TransferOrderDetail::getCol('*'),
                DB::raw('FORMAT(SUM(' . TransferOrderDetail::getCol('total') . '), 2) AS total_transfer')
            ])
            ->join(TransferOrder::getTbl(), TransferOrder::getCol('id'), TransferOrderDetail::getCol('transfer_id'));

        if (!empty($param['fromDate'])) {
            $query_builder->whereRaw("$columnDate >= '" . $param['fromDate'] . "'");
        }
        if (!empty($param['toDate'])) {
            $query_builder->whereRaw("$columnDate <= '" . $param['toDate'] . "'");
        }

        if (in_array($transferType, TRANSFER_TYPE)) {
            $query_builder->where(TransferOrder::getCol('type'), $transferType);
        }

        $query_builder->groupBy([
            TransferOrderDetail::getCol('account_id'),
            TransferOrderDetail::getCol('transfer_id')
        ]);
        $items = $query_builder->with(['account', 'transfer'])->get();

        return $items;
    }

    public function exportCsvByTransferCosyst($transfer_id, $store_code)
    {
        $return           = [
            'path'      => '',
            'file_name' => ''
        ];
        $sub_path         = date("Y/m/d");
        $flag_folder      = FileHelper::create_sub_folder(INVENTORY_DIR_TRANSFER_ORDER_CSV, $sub_path);
        $transfer_details = $this->getByTransferId($transfer_id);
        $file_name        = rename_unique(INVENTORY_DIR_TRANSFER_ORDER_CSV . "/$sub_path", $store_code . CSV_NAME_TRANSFER_COSYST);
        $arr_csv          = [];
        if (!$transfer_details->isEmpty()) {
            foreach ($transfer_details as $transfer_detail) {

                $material_code = substr($transfer_detail->material_detail_code, 0, -1);
                $code          = substr($transfer_detail->material_detail_code, -1);

                $arr_csv[$transfer_detail->material_detail_code] = array(
                    'store_code'           => $store_code,
                    'from_outlet'          => $transfer_detail->from_outlet_code,
                    'id'                   => $transfer_detail->transfer_id,
                    'date'                 => strtotime(date('Y-m-d h:i:s', strtotime(date('Y-m-d', strtotime($transfer_detail->created_date))))),
                    'to_outlet'            => $transfer_detail->to_outlet_code,
                    'invoice_number'       => $transfer_detail->invoice_number,
                    'material_code'        => $material_code,
                    'material_detail_code' => $code,
                    'quantity'             => $transfer_detail->quantity,
                    'unit'                 => $transfer_detail->uom_id,
                    'price'                => $transfer_detail->price
                );

            }
            $fp = fopen($flag_folder . '/' . $file_name, "w");
            foreach ($arr_csv as $fields) {
                fputcsv($fp, $fields);
            }
            fclose($fp);

            $return['path']      = $flag_folder;
            $return['file_name'] = $file_name;
        }
        return $return;
    }

    public function exportCsvByTransfer($transfer_id, $transfer_invoice_number)
    {
        $return           = [
            'path'      => '',
            'file_name' => ''
        ];
        $sub_path         = date("Y/m/d");
        $flag_folder      = FileHelper::create_sub_folder(INVENTORY_DIR_TRANSFER_ORDER_CSV, $sub_path);
        $transfer_details = $this->getByTransferId($transfer_id);

        if (!$transfer_details->isEmpty()) {
            $file_name = rename_unique(
                INVENTORY_DIR_TRANSFER_ORDER_CSV . "/$sub_path",
                'Transfer-' . $transfer_details[0]->from_outlet_code . '-' . $transfer_details[0]->to_outlet_code . '-' . date('d-m-Y') . $transfer_details[0]->transfer_id . '-' . $transfer_details[0]->invoice_number . '.csv'
            );
            $arr_csv   = [];
            foreach ($transfer_details as $transfer_detail) {

                $arr_csv[$transfer_detail->material_detail_code] = array(
                    'from_outlet'          => $transfer_detail->from_outlet_code,
                    'to_outlet'            => $transfer_detail->to_outlet_code,
                    'invoice_number'       => $transfer_invoice_number,
                    'material_detail_code' => $transfer_detail->material_detail_code,
                    'quantity'             => $transfer_detail->quantity,
                    'unit'                 => $transfer_detail->uom_id,
                    'price'                => $transfer_detail->price
                );

            }
            $fp = fopen($flag_folder . '/' . $file_name, "w");
            foreach ($arr_csv as $fields) {
                fputcsv($fp, $fields);
            }
            fclose($fp);

            $return['path']      = $flag_folder;
            $return['file_name'] = $file_name;
        }
        return $return;
    }

}
