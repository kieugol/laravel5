<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 3/12/2019
 * Time: 2:43 PM
 */

namespace App\Http\Controllers\Api\Inventory;


use App\Admin\Controllers\BaseController;
use App\Helpers\ConfigHelp;
use App\Helpers\PosHelper;
use App\Libraries\Api;
use App\Repository\Inventory\CurrentStockRepository;
use App\Repository\Inventory\MasterMaterialDetailRepository;
use App\Repository\Inventory\MasterPCCRepository;
use App\Repository\Inventory\ReceiveOrderDetailRepository;
use App\Repository\Inventory\ReceiveOrderRepository;
use App\Repository\Inventory\ReceiveOrderStatusLogRepository;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReceiveOrderController extends BaseController
{
    use ModelForm;

    private $receiveRep = null;
    private $receiveDetailRep = null;
    private $materialDetailSupplierRep = null;
    private $receiveStatusLogRep = null;
    private $materialDetailRep = null;
    private $storeCode;
    private $currentStockRep;
    private $pccRep;

    public function __construct(ReceiveOrderRepository $receiveOrderRepository,
                                ReceiveOrderDetailRepository $receiveOrderDetailRepository,
                                MasterMaterialDetailRepository $masterMaterialDetailRepository,
                                ReceiveOrderStatusLogRepository $receiveOrderStatusLogRepository,
                                CurrentStockRepository $currentStockRepository,
                                MasterPCCRepository $masterPCCRepository)
    {
        parent::__construct();
        $this->receiveRep          = $receiveOrderRepository;
        $this->receiveDetailRep    = $receiveOrderDetailRepository;
        $this->receiveStatusLogRep = $receiveOrderStatusLogRepository;
        $this->materialDetailRep   = $masterMaterialDetailRepository;
        $this->currentStockRep     = $currentStockRepository;
        $this->pccRep              = $masterPCCRepository;
        $this->storeCode           = ConfigHelp::get("outlet_code");
    }

    public function getReturnByReceiveId()
    {
        try {
            $this->validate($this->request, [
                "receive_id" => 'required|exists:inventory_receive,id'
            ]);
            $data            = $this->request->all();
            $receive         = $this->receiveRep->find($data['receive_id']);
            $supplier_code   = $receive->supplier->code;
            $receive_details = $this->receiveDetailRep->getByReceiveId($data['receive_id']);
            $accounts        = array();

            foreach ($receive_details as $key=>&$item) {
                $item->order = $key;
                $uoms = [
                    $item->supplier_uom_id => $item->supplier_uom_name,
                    $item->smaller_uom_id   => $item->smaller_uom_name
                ];
                $item->smaller_uom_detail_name = $item->smaller_uom_detail_name;
                $item->supplier_uom            = $item->supplier_uom_name;
                $item->quantity                = 0;  //set qty default is 0
                $item->total                   = 0;  //set total default is 0
                $item->uoms = $uoms;

                if (!array_key_exists($item->account_code, $accounts)) {
                    $accounts[$item->account_code] = [
                        'account_id' => $item->account_id,
                        'account_code'       => $item->account_code,
                        'account_name'       => $item->account_name,
                        'total'      => 0
                    ];
                } else {
                    $accounts[$item->account_code]['total'] += 0;
                }
            }

            return response([
                'message' => 'Get material detail success',
                'data'    => [
                    'material_details' => $receive_details,
                    'accounts'         => $accounts,
                    'supplier_code'    => $supplier_code
                ],
                'status'  => true,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $response = [
                'message' => 'fail',
                'status'  => STATUS_FALSE
            ];
            return Api::response($response);
        }

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getList()
    {
        try {
            $option  = $this->request->all();
            $filters = [];
            if (isset($option['supplier_id'])) {
                $filters['supplier_id'] = $option['supplier_id'];
            }
            if (isset($option['purchase_id'])) {
                $filters['purchase_id'] = $option['purchase_id'];
            }
            if (isset($option['invoice_number'])) {
                $filters['invoice_number'] = $option['invoice_number'];
            }
            if (isset($option['status_id'])) {
                $filters['status_id'] = $option['status_id'];
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

            $data     = $this->receiveRep->getDataTableApi($filters, $searches, $sort, $period);
            $response = [
                'message' => 'success',
                'data'    => $data,
            ];
            foreach ($data['items'] as &$item) {
                $item->supplier_name = $item->supplier->name;
                $item->purchase_name = $item->purchase->name;
                $item->purchase_code = $item->purchase->code;
                $item->pcc_name      = $item->master_pcc->name;
                unset($item->supplier);
                unset($item->purchase);
                unset($item->master_pcc);
            }

            return Api::response($response);
        } catch (\Exception $e) {
            $response = [
                'message' => 'fail',
                'status'  => STATUS_FALSE
            ];
            return Api::response($response);
        }

    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($id)
    {
        try {
            $accounts        = array();
            $account_group   = array();
            $material_detail_uoms = array();
            $material_detail_ids = array();
            $data_material_details = array();
            $receive         = $this->receiveRep->find($id);
            $receive_details = $this->receiveDetailRep->findByAttributes(['receive_id' => $id]);
    
            foreach ($receive_details as $item) {
                $material_detail_uoms[$item->material_detail_id] = $item->uom_id;
                $material_detail_ids[] = $item->material_detail_id;
            }
            // Get all receive detail
            $list_receive_detail = $this->receiveDetailRep->getLastestPriceAndQty($id, $material_detail_ids);
            foreach ($list_receive_detail as $key => &$item) {
                if ($material_detail_uoms[$item->material_detail_id] == $item->uom_id) {
                    if (!isset($data_material_details[$item->material_detail_id])) {
                        $data_material_details[$item->material_detail_id] = [
                            'price' => $item->price,
                            'quantity' => $item->quantity
                        ];
                    }
                }
            }
            
            foreach ($receive_details as &$item) {
                $item->material_detail_code    = $item->material_detail->code;
                $item->material_detail_name    = $item->material_detail->name;
                $item->smaller_uom_detail_name = $item->material_detail->smaller_uom_detail->name;
                $item->supplier_uom_name       = $item->material_detail->supplier_uom->name;
                if (isset($data_material_details[$item->material_detail_id])) {
                    $item->lastest_price_and_qty = $data_material_details[$item->material_detail_id];
                } else {
                    $item->lastest_price_and_qty = null;
                }

                if (!array_key_exists($item->account->code, $account_group)) {
                    $account_group[$item->account->code] = [
                        'account_id'   => $item->account->id,
                        'account_code' => $item->account->code,
                        'account_name' => $item->account->name,
                        'total'        => $item->total,
                    ];
                } else {
                    $account_group[$item->account->code]['total'] += $item->total;
                }

                unset($item->id);
                unset($item->account);
                unset($item->account_id);
                unset($item->receive_id);
                unset($item->material_id);
                unset($item->material_detail);
                unset($item->material_detail_id);
                unset($item->price_in_outlet);
                unset($item->quantity_in_outlet);
                unset($item->price_in_recipe);
                unset($item->quantity_in_recipe);
                unset($item->avg_price);
                unset($item->is_active);
                unset($item->created_date);
                unset($item->updated_date);
                unset($item->created_by);
                unset($item->updated_by);
            }
            foreach ($account_group as $key => $val) {
                $accounts[] = $val;
            }
            $data = [
                'supplier_name'   => $receive->supplier->name,
                'purchase_name'   => $receive->purchase->name,
                'purchase_code'   => $receive->purchase->code,
                'pcc_name'        => $receive->master_pcc->name,
                'invoice_number'  => $receive->invoice_number,
                'total'           => $receive->total,
                'status_id'       => $receive->status_id,
                'confirmed_date'  => $receive->confirmed_date,
                'updated_date'    => $receive->updated_date,
                'created_by'      => $receive->created_by,
                'receive_details' => $receive_details,
                'accounts'        => $accounts
            ];

            unset($account_group);
            unset($receive->supplier);
            unset($receive->purchase);
            unset($receive->master_pcc);
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

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function confirmStatus()
    {
        try {
            $this->validate($this->request, [
                "status_id"      => 'required|numeric',
                "id"             => 'required|numeric|exists:inventory_receive,id',
                "invoice_number" => 'required|numeric'
            ]);
            $data   = $this->request->all();
            $ro     = $this->receiveRep->find($data['id']);
            $result = false;

            if ($ro->status_id == TRANSACTION_ORDER_STATUS_PENDING) {
                $result = $this->receiveRep->update([
                    'status_id'      => $data['status_id'],
                    'invoice_number' => $data['invoice_number'],
                    'updated_by'     => PosHelper::getCurrentUser('id'),
                    'confirmed_date' => date('Y-m-d H:i:s')
                ], $data['id']);
                $this->updateStatus($ro->id, $data['status_id']);
            }

            if ($data['status_id'] == TRANSACTION_ORDER_STATUS_APPROVED) {
                // Export csv file
                $file = $this->receiveDetailRep->createCSV($ro->id, $this->storeCode);
                // Send Ftp
                /*$destination_ftp_server = FTP_TRANSACTION_FOLDER_TRANSFER . DIRECTORY_SEPARATOR . $resultDir['file_name'];
                PosHelper::push_fpt(
                    $destination_ftp_server,
                    $resultDir['path']. DIRECTORY_SEPARATOR . $resultDir['file_name'],
                    FTP_HOST,
                    FTP_PORT,
                    FTP_TIMEOUT,
                    FTP_TRANSACTION_USER_NAME,
                    FTP_TRANSACTION_PASSWORD
                );*/
                // Update return again
                $this->receiveRep->update([
                    'status_id' => TRANSACTION_ORDER_STATUS_APPROVED,
                    'path'      => $file['path'],
                    'file_name' => $file['file_name']
                ], $ro->id);
                // Update material detail for has transaction
                $material_detail_ids = $this->receiveDetailRep->findByAttributes(['receive_id' => $ro->id])->pluck('material_detail_id')->toArray();
                $this->materialDetailRep->UpdateByIds(['has_transaction' => 1], $material_detail_ids);
            }
            return response([
                'status'  => STATUS_TRUE,
                'message' => $result ? "Confirmed successfully." : "Confirmed Failed.",
                'data'    => '',
            ], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $response = [
                'message' => 'fail',
                'status'  => STATUS_FALSE
            ];
            return Api::response($response);
        }

    }

    /**
     * @param $receive_id
     * @param $status
     */
    function updateStatus($receive_id, $status)
    {
        try {
            $receive_status_log = $this->receiveStatusLogRep->getQueryByAttributes(['receive_id' => $receive_id])->first();
            $this->receiveStatusLogRep->insert([
                "receive_id"   => $receive_id,
                "status_old"   => $receive_status_log->status_new ?? '',
                "status_new"   => $status,
                'created_date' => date('Y-m-d H:i:s'),
                'created_by'   => PosHelper::getCurrentUser('id')
            ]);
        } catch (\Exception $e) {
            $response = [
                'message' => 'fail',
                'status'  => STATUS_FALSE
            ];
            return Api::response($response);
        }
    }

    public function create()
    {
        $this->validate($this->request, [
            "supplier_id" => 'required|numeric|exists:inventory_master_supplier,id'
        ]);

        $this->validate($this->request, [
            "purchase_id"                           => 'required|numeric|unique:inventory_receive',
            "invoice_number"                        => 'required',
            "receive_date"                          => 'required|date_format:"Y-m-d"',
            "is_returnable"                         => 'required',
            "total"                                 => 'required|numeric'
        ]);

        $user_id = PosHelper::getCurrentUser('id');
        $data    = $this->request->all();
        $pcc     = $this->pccRep->getByCurrentDate();
        DB::beginTransaction();
        $receive = $this->receiveRep->create([
            'store_code'       => $this->storeCode,
            'supplier_id'      => $data['supplier_id'],
            'purchase_id'      => $data['purchase_id'],
            'pcc_id'           => $pcc->id,
            'invoice_number'   => $data['invoice_number'],
            'transaction_date' => $data['receive_date'],
            'description'      => '',
            'total'            => $data['total'],
            'status_id'        => TRANSACTION_ORDER_STATUS_APPROVED,
            'is_active'        => STATUS_ACTIVE,
            'is_returnable'    => $data['is_returnable'],
            'created_by'       => $user_id,
            'updated_by'       => 0
        ]);
        foreach ($data['material_details'] as $item) {
            // Get price and quantity
            $current_stock   = $this->currentStockRep->getByMaterialDetailId($item['material_detail_id']);
            $material_detail = $this->materialDetailRep->find($item['material_detail_id']);

            $this->receiveDetailRep->create([
                'receive_id'         => $receive->id,
                'material_id'        => $item['material_id'],
                'material_detail_id' => $item['material_detail_id'],
                'uom_id'             => $item['uom_id'],
                'account_id'         => $item['account_id'],
                'price_in_outlet'    => $material_detail->price,
                'quantity_in_outlet' => !empty($current_stock) ? $current_stock->quantity_store : 0,
                'price_in_recipe'    => $material_detail->price,
                'quantity_in_recipe' => !empty($current_stock) ? $current_stock->quantity_store : 0,
                'avg_price'          => $material_detail->price,
                'quantity'           => $item['quantity'],
                'price'              => $item['price'],
                'total'              => $item['total'],
                'is_active'          => STATUS_ACTIVE,
                'created_by'         => $user_id,
                'updated_by'         => 0
            ]);
        }

        $this->updateStatus($receive->id, TRANSACTION_ORDER_STATUS_APPROVED);

        DB::commit();
        return response([
            'message' => 'Save receive successfully',
            'status'  => true,
        ], Response::HTTP_OK);
    }
}
