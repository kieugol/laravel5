<?php

namespace App\Admin\Controllers\Inventory;

use App\Admin\Controllers\BaseController;
use App\Helpers\{ConfigHelp, PosHelper};
use App\Model\Inventory\ReceiveOrder;
use App\Repository\Inventory\{MasterMaterialDetailRepository,
    MasterMaterialDetailSupplierRepository,
    MasterSupplierRepository,
    MasterPCCRepository,
    MasterUomRepository,
    PurchaseOrderDetailRepository,
    PurchaseOrderRepository,
    ReceiveOrderDetailRepository,
    ReceiveOrderRepository,
    ReceiveOrderStatusLogRepository
};
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReceiveOrderController extends BaseController
{
    use ModelForm;

    CONST COLUMN_CSV = [
        'OUTLET_CODE'   => 0,
        'GENSTORE_CODE' => 1,
        'PRODUCT_CODE'  => 2,
        'QUANTITY'      => 3,
        'UOM'           => 4,
        'PRICE'         => 5
    ];
    private $supplierRep;
    private $purchaseRep;
    private $receiveRep;
    private $receiveDetailRep;
    private $materialDetailRep;
    private $purchaseDetailRep;
    private $pccRep;
    private $receiveStatusLogRep;
    private $uomRep;
    private $storeCode;
    private $materialDetailSupplierRep;

    public function __construct(
        MasterSupplierRepository $supplierRepository,
        PurchaseOrderRepository $purchaseRepository,
        ReceiveOrderRepository $receiveRepository,
        ReceiveOrderDetailRepository $receiveDetailRepository,
        PurchaseOrderDetailRepository $purchaseDetailRepository,
        MasterMaterialDetailRepository $masterMaterialDetailRepository,
        MasterPCCRepository $pccRepository,
        ReceiveOrderStatusLogRepository $receiveStatusLogRep,
        MasterUomRepository $uomRep,
        MasterMaterialDetailSupplierRepository $materialDetailSupplierRep
    )
    {
        parent::__construct();
        $this->supplierRep         = $supplierRepository;
        $this->purchaseRep         = $purchaseRepository;
        $this->receiveRep          = $receiveRepository;
        $this->receiveDetailRep    = $receiveDetailRepository;
        $this->purchaseDetailRep   = $purchaseDetailRepository;
        $this->materialDetailRep   = $masterMaterialDetailRepository;
        $this->pccRep              = $pccRepository;
        $this->receiveStatusLogRep = $receiveStatusLogRep;
        $this->uomRep              = $uomRep;
        $this->storeCode           = ConfigHelp::get("outlet_code");
        $this->materialDetailSupplierRep = $materialDetailSupplierRep;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Receive Order');
            $content->description('List');
            $content->body($this->grid());

        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(ReceiveOrder::class, function (Grid $grid) {
            $grid->disableRowSelector();
            $grid->disableExport();

            $grid->model()->orderBy("id", "desc");
            $grid->invoice_number('Receive Invoice Number')->sortable();
            $grid->column('purchase.code', 'PO#')->display(function ($purchase_code) {
                return $purchase_code;
            })->sortable();
            $grid->column("total", "Total")->sortable();
            $grid->column('status_id', 'Status')->display(function ($status_id) {
                return TRANSACTION_ORDER_STATUS[$status_id] ?? '';
            });
            $grid->column('supplier.name', 'Supplier')->display(function ($supplierName) {
                return $supplierName;
            })->sortable();
            $grid->column('user_admin.name', 'Created By')->sortable();
            $grid->created_date()->sortable();
            $grid->column('user_admin_updated.name', 'Updated By')->sortable();
            $grid->updated_date()->sortable();
            $grid->column('path', 'Download File')->display(function ($path) {
                if (empty($path)) {
                    return '';
                }
                $path = "'" . route('download_receive') . "?path={$path}" . "&file_name=" . $this->file_name . "'";
                return '<button class="btn btn-xs btn-success" onclick="download_file(' . $path . ')"><i class="fa fa-download" aria-hidden="true"></i></i>&nbsp;&nbsp;Download</button>';
            });
            $grid->actions(function ($actions) {
                $actions->disableEdit();
                $actions->disableDelete();
            });


            // Filter
            $grid->filter(function ($filter) {
                //$filter->like('code', 'PO Code');
                $filter->between('created_date', 'Create date')->date([
                    'format'      => 'YYYY-MM-DD',
                    'defaultDate' => date('Y-m-d')
                ]);
            });
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('Receive Order');
            $content->description('Create');

            $content->body($this->formCreate());
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function formCreate()
    {
        $purchase_id   = $this->request['purchase_id'] ?? 0;
        $purchase      = $this->purchaseRep->find($purchase_id);
        $purchase_list = $this->purchaseRep->findByAttributes([
            'is_active' => STATUS_ACTIVE,
            'status_id' => TRANSACTION_ORDER_STATUS_APPROVED
        ]);
        $data          = [
            'action'        => ACTION_CREATE,
            'purchase_id'   => $purchase_id,
            'purchase_list' => $purchase_list,
            'delivery_date' => $purchase->delivery_date
        ];
        return view("inventory.receive_order.form", $data);
    }

    /**
     * @param
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function save()
    {
        $this->validate($this->request, [
            "supplier_id" => 'required|numeric|exists:inventory_master_supplier,id'
        ]);
        $supplier_id = $this->request->supplier_id;
        $supplier    = $this->supplierRep->find($supplier_id);
        if (!$supplier->is_import_do) {
            $invoice_validate = 'required';
            $status           = TRANSACTION_ORDER_STATUS_APPROVED;
        } else {
            $invoice_validate = '';
            $status           = TRANSACTION_ORDER_STATUS_PENDING;
        }
        $this->validate($this->request, [
            "purchase_id"                           => 'required|numeric|unique:inventory_receive',
            "invoice_number"                        => $invoice_validate,
            //"receive_date"                          => 'required|date_format:"Y-m-d"',
//            "is_returnable"                         => 'required',
            "total"                                 => 'required|numeric',
            "material_details.*.material_id"        => 'required|numeric|exists:inventory_master_material,id',
            "material_details.*.material_detail_id" => 'required|numeric|exists:inventory_master_material_detail,id',
            "material_details.*.account_id"         => 'required|numeric|exists:inventory_master_account,id',
            "material_details.*.quantity"           => 'required|numeric',
            "material_details.*.price"              => 'required|numeric',
            "material_details.*.total"              => 'required|numeric'
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
            //'transaction_date' => $data['receive_date'],
            'description'      => '',
            'total'            => $data['total'],
            'status_id'        => $status,
            'is_active'        => STATUS_ACTIVE,
//            'is_returnable'    => $data['is_returnable'],
            'created_by'       => $user_id
        ]);
        foreach ($data['material_details'] as $item) {

            // Get price and quantity
            $material_detail = $this->materialDetailRep->find($item['material_detail_id']);

            $this->receiveDetailRep->create([
                'receive_id'         => $receive->id,
                'material_id'        => $item['material_id'],
                'material_detail_id' => $item['material_detail_id'],
                'uom_id'             => $item['uom_id'],
                'account_id'         => $item['account_id'],
                'price_in_outlet'    => $material_detail->price,
                'quantity_in_outlet' => 0,
                'price_in_recipe'    => $material_detail->price,
                'quantity_in_recipe' => 0,
                'avg_price'          => $material_detail->price,
                'quantity'           => $item['quantity'],
                'price'              => $item['price'],
                'total'              => $item['total'],
                'is_active'          => STATUS_ACTIVE,
                'created_by'         => $user_id,
            ]);
        }

        if (!$supplier->is_import_do) {
            $this->createCSVAndSendFtp($receive->id);
        }

        $this->updateStatus($receive->id, $status);

        DB::commit();
        return response([
            'message' => 'Save receive success',
            'status'  => true,
        ], Response::HTTP_OK);
    }

    /**
     * @param
     * @return \Illuminate\Http\JsonResponse
     */
    public function readCsv()
    {
        $this->validate($this->request, [
            "file" => 'required',
            "supplier_code" => 'required',
            "purchase_id" => 'required|exists:inventory_purchase,id'
        ]);

        $file     = $this->request->file('file');
        $supplier_code = $this->request->supplier_code;
        $purchase_id = $this->request->purchase_id;

        $tempPath = $file->getRealPath();
        $data     = array_map('str_getcsv', file($tempPath));

        $purchase_data = $this->purchaseDetailRep->getByPOId($purchase_id);
        $purchase_data = $purchase_data->toArray();

        $purchase_items = array();// the list of purchase items
        $do_items = array();// the list of DO items

        $not_available_message = array();// show list of items not available on system
        $compare_message = array();// compare the difference item and quantity

        // get list of imported product code
        $list_product_codes = array();
        foreach ($data as $item) {
            $list_product_codes[] = $item[self::COLUMN_CSV['PRODUCT_CODE']];
        }

        // get list of imported material
        $material_with_code = $this->materialDetailRep->getByCodes($list_product_codes)->keyBy('code');
        foreach ($data as $idx => $item) {
            if ($item[self::COLUMN_CSV['OUTLET_CODE']] != $this->storeCode || $item[self::COLUMN_CSV['GENSTORE_CODE']] != $supplier_code) {
                return response()->json(['message' => 'Outlet code or supplier code look like wrong!'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // check if material code existed in inventory
            $product_code = $item[self::COLUMN_CSV['PRODUCT_CODE']];

            if (!isset($material_with_code[$product_code])) {
                $not_available_message[] = $product_code . ' - Item is not available on system!';
                continue;
            }

            $material = $material_with_code[$product_code];
            $material_name = $material->name;

            // get the DO item
            $do_items[$item[self::COLUMN_CSV['PRODUCT_CODE']]] = $material_name;

            // check the difference between purchase order and DO receive
            foreach ($purchase_data as $purchase) {
                // get the purchase item
                $purchase_items[$purchase['code']] = $purchase['name'];

                if ($purchase['code'] == $item[self::COLUMN_CSV['PRODUCT_CODE']]) {
                    // compare the quantity
                    if ($purchase['quantity'] < $item[self::COLUMN_CSV['QUANTITY']]) {
                        $compare_message[] = $product_code.' - '.$material_name.' - Qty Added';
                    }
                    if ($purchase['quantity'] > $item[self::COLUMN_CSV['QUANTITY']]) {
                        $compare_message[] = $product_code.' - '.$material_name.' - Qty Removed';
                    }
                }
            }

            $material_detail_codes[] = $item[self::COLUMN_CSV['PRODUCT_CODE']];
        }

        // show items not available on system
        if (!empty($not_available_message)) {
            return response()->json(['message' => (implode('<br/>', $not_available_message)).'<br/>'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // compare the difference between purchase items and DO items
        foreach ($purchase_items as $purchase_code => $purchase_name) {
            if (array_key_exists($purchase_code, $do_items)) {
                unset($purchase_items[$purchase_code]);
                unset($do_items[$purchase_code]);
            } else {
                // show the missing item from purchase order
                $compare_message[] = $purchase_code.' - '.$purchase_name.' - Item Removed';
            }
        }

        if (!empty($do_items)) {
            foreach ($do_items as $do_code => $do_name) {
                // show the added item from DO
                $compare_message[] = $do_code.' - '.$do_name.' - Item Added';
            }
        }

        try {
            // Get material details by codes
            $uoms_query       = $this->uomRep->all();
            $uoms             = PosHelper::object_key_column($uoms_query, 'id');
            $material_details = $this->materialDetailRep->getMaterialByCodes($material_detail_codes);
            $material_details = PosHelper::object_key_column($material_details, 'code');
            $return           = [
                'data'   => [
                    'material_details' => [],
                    'accounts'         => []
                ],
                'status' => STATUS_TRUE
            ];
            foreach ($data as $key=>$item) {
                $code     = $item[self::COLUMN_CSV['PRODUCT_CODE']];
                $quantity = $item[self::COLUMN_CSV['QUANTITY']];
                $price    = $item[self::COLUMN_CSV['PRICE']];
                $uom      = $item[self::COLUMN_CSV['UOM']];
                $total    = $quantity * $price;
                if (empty($material_details[$code])) {
                    continue;
                }

                $item_material_detail = $material_details[$code];

                $return['data']['material_details'][] = [
                    'order'                   => $key,
                    'id'                      => $item_material_detail->id,
                    'material_id'             => $item_material_detail->material_id,
                    'code'                    => $item_material_detail->code,
                    'name'                    => $item_material_detail->name,
                    'smaller_uom_detail_name' => $item_material_detail->smaller_uom_detail->name,
                    'uom_name'                => $uoms[$uom]->name ?? '',
                    'uom_id'                  => $uom,
                    'account_id'              => $item_material_detail->material->account->id,
                    'account_code'            => $item_material_detail->material->account->code,
                    'account_name'            => $item_material_detail->material->account->name,
                    'quantity'                => $quantity,
                    'price'                   => $price,
                    'total'                   => $total,
                ];

                if (!array_key_exists($item_material_detail->material->account->code, $return['data']['accounts'])) {
                    $return['data']['accounts'][$item_material_detail->material->account->code] = [
                        'account_id'   => $item_material_detail->material->account->id,
                        'account_code' => $item_material_detail->material->account->code,
                        'account_name' => $item_material_detail->material->account->name,
                        'total'        => $total,
                    ];
                } else {
                    $return['data']['accounts'][$item_material_detail->material->account->code]['total'] += $total;
                }
            }

            // show the difference item and quantity
            if (!empty($compare_message))
            {
                $return['show_notice'] = true;
                $return['notice_message'] = (implode('<br/>', $compare_message)).'<br/>';
            }

            $return['message'] = 'Added Material detail successfully';
        } catch (\Exception $exc) {
            return response()->json(['message' => 'File import not correct format. (' . $exc->getMessage() . ')'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($return);
    }

    /**
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('Receive Order');
            $content->description('Edit');

            $content->body($this->formEdit($id));
        });
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function formEdit($id)
    {
        $total           = 0;
        $receive         = $this->receiveRep->find($id);
        $receive_details = $this->receiveDetailRep->getByReceiveId($receive->id);
        $purchase_list   = $this->purchaseRep->findByAttributes([
            'is_active' => STATUS_ACTIVE,
            'status_id' => TRANSACTION_ORDER_STATUS_APPROVED
        ]);
        $data            = [
            'action'          => ACTION_VIEW,
            'receive'         => $receive,
            'purchase_id'     => $receive->purchase_id,
            'receive_details' => $receive_details,
            'purchase_list'   => $purchase_list,
            'accounts'        => [],
            'total'           => $total
        ];
        foreach ($receive_details as $receive_detail) {
            $total += $receive_detail->total;
            if (!array_key_exists($receive_detail->account_code, $data['accounts'])) {
                $data['accounts'][$receive_detail->account_code] = [
                    'account_id'   => $receive_detail->account_id,
                    'account_code' => $receive_detail->account_code,
                    'account_name' => $receive_detail->account_name,
                    'total'        => $receive_detail->total,
                ];
            } else {
                $data['accounts'][$receive_detail->account_code]['total'] += $receive_detail->total;
            }
        }
        $data['total'] = $total;
        return view("inventory.receive_order.form", $data);
    }

    /**
     * @param
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function update()
    {
        $this->validate($this->request, [
            "receive_id"                            => 'required|numeric|exists:inventory_receive,id',
            "supplier_id"                           => 'required|numeric|exists:inventory_master_supplier,id',
            "purchase_id"                           => 'required|numeric',
            "invoice_number"                        => 'required',
            "is_returnable"                         => 'required',
            "total"                                 => 'required|numeric',
            "material_details.*.material_id"        => 'required|numeric|exists:inventory_master_material,id',
            "material_details.*.material_detail_id" => 'required|numeric|exists:inventory_master_material_detail,id',
            "material_details.*.account_id"         => 'required|numeric|exists:inventory_master_account,id',
            "material_details.*.quantity"           => 'required|numeric',
            "material_details.*.price"              => 'required|numeric',
            "material_details.*.total"              => 'required|numeric'
        ]);

        $user_id = PosHelper::getCurrentUser('id');
        $data    = $this->request->all();
        DB::beginTransaction();

        $this->receiveRep->update([
            'supplier_id'    => $data['supplier_id'],
            'purchase_id'    => $data['purchase_id'],
            'invoice_number' => $data['invoice_number'],
            'is_returnable'  => $data['is_returnable'],
            'total'          => $data['total'],
            'updated_by'     => $user_id
        ], $data['receive_id']);

        $this->receiveDetailRep->deleteByReceiveId($data['receive_id']);
        foreach ($data['material_details'] as $item) {

            // Get price and quantity
            $material_detail = $this->materialDetailRep->find($item['material_detail_id']);

            $this->receiveDetailRep->create([
                'receive_id'         => $data['receive_id'],
                'material_id'        => $item['material_id'],
                'material_detail_id' => $item['material_detail_id'],
                'account_id'         => $item['account_id'],
                'price_in_outlet'    => $material_detail->price,
                'quantity_in_outlet' => 0,
                'price_in_recipe'    => $material_detail->price,
                'quantity_in_recipe' => 0,
                'avg_price'          => $material_detail->price,
                'quantity'           => $item['quantity'],
                'price'              => $item['price'],
                'total'              => $item['total'],
                'is_active'          => STATUS_ACTIVE,
                'updated_by'         => $user_id
            ]);
        }
        DB::commit();

        return response([
            'message' => 'Updated Receive successfully',
            'status'  => true,
        ], Response::HTTP_OK);
    }

    /**
     * compare the latest receive order with new receive order when approve
     * show notice if quantity or price of new receive order is too high
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function compareLatest()
    {
        $id = $this->request->id;

        $message = array();

        $material_detail_uoms = array();
        $material_detail_ids = array();
        $data_material_details = array();
        $receive_details = $this->receiveDetailRep->findByAttributes(['receive_id' => $id]);

        foreach ($receive_details as $item) {
            $material_detail_uoms[$item->material_detail_id] = $item->uom_id;
            $material_detail_ids[] = $item->material_detail_id;
        }

        $list_receive_detail = $this->receiveDetailRep->getLastestPriceAndQty($id, $material_detail_ids);
        foreach ($list_receive_detail as $key => &$item) {
            if ($material_detail_uoms[$item->material_detail_id] == $item->uom_id) {
                if (!isset($data_material_details[$item->material_detail_id])) {
                    $data_material_details[$item->material_detail_id] = [
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'uom_id' => $item->uom_id,
                        'code' => $item->material_detail->code,
                        'name' => $item->material_detail->name
                    ];
                }
            }
        }

        foreach ($receive_details as &$receive_detail) {
            $latest = isset($data_material_details[$receive_detail->material_detail_id]) ? $data_material_details[$receive_detail->material_detail_id] : null;

            if ($latest) {
                $price = $receive_detail->price;
                $quantity = $receive_detail->quantity;

                $latest_price = $latest['price'];
                $latest_quantity = $latest['quantity'];

                // compare the difference price, quantity
                // notice if the price is more than 20% the latest price
                // avoid division by 0 error, latest price should bigger than 0
                if ($latest_price > 0) {
                    $price_diff = (($price - $latest_price)/$latest_price)*100;
                    if ($price_diff > 20) {
                        $message[] = $latest['code'].' - '.$latest['name'].' - price is '.(number_format($price_diff, 2)).'% more than the latest receive';
                    }
                }

                // notice if the quantity is more than 50% the latest quantity
                // avoid division by 0 error, latest quantity should bigger than 0
                if ($latest_quantity > 0) {
                    $quantity_diff = (($quantity - $latest_quantity)/$latest_quantity)*100;
                    if ($quantity_diff > 50) {
                        $message[] =  $latest['code'].' - '.$latest['name'].' - quantity is '.(number_format($quantity_diff, 2)).'% more than the latest receive';
                    }
                }
            }

        }

        return response([
            'message' => implode('<br/>', $message),
        ], Response::HTTP_OK);
    }

    public function confirmStatus()
    {
        $this->validate($this->request, [
            "status_id"      => 'required|numeric',
            "id"             => 'required|numeric|exists:inventory_receive,id',
            "invoice_number" => 'required|numeric'
        ]);
        $data = $this->request->all();
        $ro   = $this->receiveRep->find($data['id']);
        $this->receiveRep->update([
            'invoice_number' => $data['invoice_number']
        ], $ro->id);
        $this->createCSVAndSendFtp($ro->id);
        $this->updateStatus($ro->id, $data['status_id']);
        return response([
            'status'  => true,
            'message' => "Approved successfully.",
            'data'    => '',
        ], Response::HTTP_OK);
    }

    public function createCSVAndSendFtp($receive_id)
    {
        // Export csv file
        $file = $this->receiveDetailRep->createCSV($receive_id, $this->storeCode);
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
        // Update receive again
        $this->receiveRep->update([
            'status_id'    => TRANSACTION_ORDER_STATUS_APPROVED,
            'path'         => $file['path'],
            'file_name'    => $file['file_name'],
            'updated_by'   => PosHelper::getCurrentUser('id'),
            'updated_date' => date('Y-m-d H:i:s')
        ], $receive_id);
        // Update material detail for has transaction
        $material_detail_ids = $this->receiveDetailRep->findByAttributes(['receive_id' => $receive_id])->pluck('material_detail_id')->toArray();
        $this->materialDetailRep->UpdateByIds(['has_transaction' => 1], $material_detail_ids);
    }

    function updateStatus($receive_id, $status)
    {
        $receive_status_log = $this->receiveStatusLogRep->getQueryByAttributes(['receive_id' => $receive_id])->first();
        $this->receiveStatusLogRep->insert([
            "receive_id"   => $receive_id,
            "status_old"   => $receive_status_log->status_new ?? '',
            "status_new"   => $status,
            'created_date' => date('Y-m-d H:i:s'),
            'created_by'   => PosHelper::getCurrentUser('id')
        ]);
    }

}
