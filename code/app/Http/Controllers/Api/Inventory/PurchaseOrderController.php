<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Helpers\ConfigHelp;
use App\Helpers\PosHelper;
use App\Libraries\Api;
use App\Repository\Inventory\{MasterPCCRepository,
    PurchaseOrderRepository,
    PurchaseOrderDetailRepository,
    MasterSupplierRepository,
    MasterMaterialDetailRepository,
    PurchaseOrderStatusLogRepository,
    ReceiveOrderRepository};
use App\Admin\Controllers\BaseController;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Response;

class PurchaseOrderController extends BaseController
{
    use ModelForm;

    private $supplierRep = null;
    private $purchaseRep = null;
    private $purchaseDetailRep = null;
    private $materialDetailRep = null;
    private $pccRep = null;
    private $purchaseStatusLogRep = null;
    private $receiveRep = null;
    private $storeCode;

    public function __construct(
        PurchaseOrderRepository $purchaseRep,
        PurchaseOrderDetailRepository $purchaseDetailRepRep,
        MasterSupplierRepository $supplierRep,
        MasterMaterialDetailRepository $materialDetailRep,
        MasterPCCRepository $pccRep,
        PurchaseOrderStatusLogRepository $purchaseOrderStatusLogRepository,
        ReceiveOrderRepository $receiveOrderRepository
    )
    {
        parent::__construct();
        $this->supplierRep          = $supplierRep;
        $this->purchaseRep          = $purchaseRep;
        $this->purchaseDetailRep    = $purchaseDetailRepRep;
        $this->materialDetailRep    = $materialDetailRep;
        $this->pccRep               = $pccRep;
        $this->purchaseStatusLogRep = $purchaseOrderStatusLogRepository;
        $this->receiveRep           = $receiveOrderRepository;
        $this->storeCode            = ConfigHelp::get("outlet_code");
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getById()
    {
        $this->validate($this->request, [
            "purchase_id" => 'required|exists:inventory_purchase,id'
        ]);
        $data     = $this->request->all();
        $purchase = $this->purchaseRep->find($data['purchase_id']);

        return response([
            'message' => '',
            'status'  => true,
            'data'    => $purchase->supplier,
        ], Response::HTTP_OK);

    }

    /**
     * @param $purchase_id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getDetailByPurchaseId()
    {
        $this->validate($this->request, [
            "purchase_id" => 'required|exists:inventory_purchase,id'
        ]);
        $data   = $this->request->all();
        $return = [
            'data'   => [
                'material_details' => [],
                'accounts'         => []
            ],
            'status' => STATUS_TRUE
        ];
        $data   = $this->purchaseDetailRep->getByPOId($data['purchase_id']);
        foreach ($data as $key=>$item) {
            $uoms = [
                $item->supplier_uom_id => $item->supplier_uom_name,
                $item->smaller_uom_id  => $item->smaller_uom_name
            ];

            $return['data']['material_details'][] = [
                'order'                       => $key,
                'material_id'                 => $item->material_id,
                'id'                          => $item->id,
                'code'                        => $item->code,
                'name'                        => $item->name,
                'smaller_uom_detail_name'     => $item->smaller_uom_detail_name,
                'smaller_uom_id'              => $item->smaller_uom_id,
                'conversion_rate'             => $item->conversion_rate,
                'supplier_uom_name'           => $item->supplier_uom_name,
                'supplier_uom_id'             => $item->supplier_uom_id,
                'uom_id'                      => $item->uom_id,
                'account_id'                  => $item->account_id,
                'account_code'                => $item->account_code,
                'account_name'                => $item->account_name,
                'quantity'                    => $item->quantity,
                'price'                       => 0,
                'total'                       => 0,
                'uoms'                        => $uoms,
                'smaller_uom_conversion_rate' => $item->smaller_uom_conversion_rate
            ];

            if (!array_key_exists($item->account_code, $return['data']['accounts'])) {
                $return['data']['accounts'][$item->account_code] = [
                    'account_id'   => $item->account_id,
                    'account_code' => $item->account_code,
                    'account_name' => $item->account_name,
                    'total'        => 0,
                ];
            }
        }
        return response()->json($return);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {

        $option  = $this->request->all();
        $filters = [];
        if (isset($option['supplier_id'])) {
            $filters['supplier_id'] = $option['supplier_id'];
        }
        if (isset($option['pcc_id'])) {
            $filters['purchase_id'] = $option['purchase_id'];
        }
        if (isset($option['code'])) {
            $filters['code'] = $option['code'];
        }

        $period = [];
        if (isset($option['created_date'])) {
            $period = $option['created_date'];
        }

        $searches = [];
        if (!empty($option['search_key']) && !empty($option['search_value'])) {
            $arr_search_key = explode(',', $option['search_key']);
            foreach ($arr_search_key as $search_key) {
                $searches[$search_key] = $option['search_value'];
            }
        }

        $sort = [];
        if (!empty($option['field']) && !empty($option['type'])) {
            $sort[$option['field']] = $option['type'];
        }
        $limit = isset($option['limit']) ? $option['limit'] : 10;
        $offset = isset($option['offset']) ? $option['offset'] : 0;
        $is_received  = isset($option['is_received']) ? $option['is_received'] : 'all';
        $data         = $this->purchaseRep->getListPurchase($filters, $searches, $sort, $period, $limit, $offset, $is_received);
        foreach ($data['items'] as &$item) {
            unset($item->receive);
            $item->supplier_name = $item->supplier->name;
            $item->pcc_name      = $item->master_pcc->name;
            $item->is_import_do  = $item->supplier->is_import_do;
            if ($item->receive_id) {
                $item->receive = [
                  'invoice_number' => $item->invoice_number,
                  'receive_id'     => $item->receive_id,
                  'status_id'      => $item->receive_status_id
                ];
            } else {
                $item->receive = null;
            }
            unset($item->invoice_number);
            unset($item->receive_id);
            unset($item->receive_status_id);
            unset($item->supplier);
            unset($item->master_pcc);
        }
        
        $response     = [
            'message' => 'success',
            'data'    => $data,
        ];
        return Api::response($response);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($id)
    {
        try {
            $purchase         = $this->purchaseRep->find($id);
            $purchase_details = $this->purchaseDetailRep->getByPOId($id);

            foreach ($purchase_details as &$item) {
                $item->uoms = [
                    'supplier_uom_id'   => $item->supplier_uom_id,
                    'supplier_uom_name' => $item->supplier_uom_name,
                    'outlet_uom_id'     => $item->outlet_uom_id,
                    'outlet_uom_outlet' => $item->outlet_uom_name
                ];
            }
            $data = [
                'supplier_name'    => $purchase->supplier->name,
                'code'             => $purchase->code,
//                'pcc_name'        => $purchase->master_pcc->name,
                'delivery_date'    => $purchase->delivery_date,
                'status_name'      => TRANSACTION_ORDER_STATUS[$purchase->status_id],
                'created_date'     => $purchase->created_date->format('Y-m-d H:i:s'),
                'updated_date'     => $purchase->updated_date->format('Y-m-d H:i:s'),
                'created_by'       => $purchase->user_admin->username,
                'updated_by'       => isset($purchase->user_admin_updated) ? $purchase->user_admin_updated->username : '',
                'purchase_details' => $purchase_details->toArray()
            ];

            $response = [
                'message' => 'success',
                'data'    => $data
            ];
            return Api::response($response);
        } catch (\Exception $e) {
            $response = [
                'message' => 'fail',
                'status'  => STATUS_FALSE
            ];
            return Api::response($response);
        }
    }

    public function updateStatus($id)
    {
        $this->validate($this->request, [
            'status' => 'required|in:' . implode(',', [TRANSACTION_ORDER_STATUS_APPROVED])
        ], []);

        $po = $this->purchaseRep->find($id);

        if ($po && $po->status_id == TRANSACTION_ORDER_STATUS_PENDING) {
            $this->purchaseDetailRep->deleteEmptyQtyByPOId($id);
            $this->createCSVAndSendFtp($id);
            $this->createLogStatus($id, $this->request['status']);
        }

        $response = [
            'message' => 'success',
            'data'    => ''
        ];
        return Api::response($response);
    }

    public function createCSVAndSendFtp($po_id)
    {
        // Export csv file
        $file = $this->purchaseDetailRep->createCSV($po_id, $this->storeCode);
        // Update receive again
        $this->purchaseRep->update([
            'status_id'    => TRANSACTION_ORDER_STATUS_APPROVED,
            'path'         => $file['path'],
            'file_name'    => $file['file_name'],
            'updated_by'   => PosHelper::getCurrentUser('id'),
            'updated_date' => date('Y-m-d H:i:s')
        ], $po_id);
        // Update material detail for has transaction
        $material_detail_ids = $this->purchaseDetailRep->findByAttributes(['purchase_id' => $po_id])->pluck('material_detail_id')->toArray();
        $this->materialDetailRep->UpdateByIds(['has_transaction' => 1], $material_detail_ids);
    }

    protected function createLogStatus($purchase_id, $status)
    {
        $purchase_status_log = $this->purchaseStatusLogRep->getQueryByAttributes(['purchase_id' => $purchase_id])->first();
        $this->purchaseStatusLogRep->insert([
            'purchase_id'  => $purchase_id,
            'status_old'   => $purchase_status_log->status_new ?? '',
            'status_new'   => $status,
            'created_date' => date('Y-m-d H:i:s'),
            'created_by'   => PosHelper::getCurrentUser('id')
        ]);
    }

}
