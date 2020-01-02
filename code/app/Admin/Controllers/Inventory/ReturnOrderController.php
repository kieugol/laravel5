<?php
/**
 * Created by PhpStorm.
 * User: DELL-PC
 * Date: 1/21/2019
 * Time: 2:56 PM
 */

namespace App\Admin\Controllers\Inventory;

use App\Admin\Controllers\BaseController;
use App\Helpers\{ConfigHelp, FileHelper, PosHelper};
use App\Model\Inventory\{MasterSupplier, ReturnOrder};
use App\Repository\Inventory\{MasterPCCRepository,
    MasterUomRepository,
    ReturnOrderRepository,
    ReturnOrderDetailRepository,
    ReceiveOrderRepository,
    MasterMaterialDetailRepository,
    MasterSupplierRepository,
    ReturnOrderStatusLogRepository};
use App\Rules\CheckMaterialDetailIsBelongReceive;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ReturnOrderController extends BaseController
{
    use ModelForm;

    CONST COLUMN_CSV = [
        'OUTLET_CODE'          => 0,
        'SUPPLIER_CODE'        => 1,
        'MATERIAL_DETAIL_CODE' => 2,
        'QUANTITY'             => 3,
        'UOM'                  => 4,
        'PRICE'                => 5
    ];

    private $return_repository;
    private $return_detail_repository;
    private $receive_repository;
    private $master_material_detail_repository;
    private $master_supplier_repository;
    private $pcc_repository;
    private $store_code;
    private $return_status_log_repository;
    private $master_uom_repository;

    public function __construct(
        ReturnOrderRepository $return_repository,
        ReturnOrderDetailRepository $return_detail_repository,
        ReceiveOrderRepository $receive_repository,
        MasterMaterialDetailRepository $master_material_detail_repository,
        MasterSupplierRepository $master_supplier_repository,
        MasterPCCRepository $pcc_repository,
        ReturnOrderStatusLogRepository $return_status_log_repository,
        MasterUomRepository $master_uom_repository
    )
    {
        parent::__construct();
        $this->return_repository                 = $return_repository;
        $this->return_detail_repository          = $return_detail_repository;
        $this->receive_repository                = $receive_repository;
        $this->master_material_detail_repository = $master_material_detail_repository;
        $this->master_supplier_repository        = $master_supplier_repository;
        $this->pcc_repository                    = $pcc_repository;
        $this->return_status_log_repository      = $return_status_log_repository;
        $this->master_uom_repository             = $master_uom_repository;
        $this->store_code                        = ConfigHelp::get("outlet_code");
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Return');
            $content->description('List');
            $content->body("<style>span .btn-group{display:none !important}</style>");
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
        return Admin::grid(ReturnOrder::class, function (Grid $grid) {
            $grid->disableRowSelector();
            $grid->disableExport();
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('receive.invoice_number', 'Receive invoice number')->sortable();
            $grid->invoice_number('Return invoice number')->sortable();
            $grid->column('supplier.name', 'Supplier')->sortable();
            $grid->total('Total')->sortable();
            $grid->column('user_admin.name', 'Created By')->sortable();
            $grid->created_date()->sortable();
//            $grid->column('path', 'Download File')->display(function ($path) {
//                if (empty($path)) {
//                    return '';
//                }
//                $path = "'" . route('download_return') . "?path={$path}" . "&file_name=" . $this->file_name . "'";
//                return '<button class="btn btn-xs btn-success" onclick="download_file(' . $path . ')"><i class="fa fa-download" aria-hidden="true"></i></i>&nbsp;&nbsp;Download</button>';
//            });
            $grid->actions(function ($actions) {
                $actions->disableEdit();
                $actions->disableDelete();
            });
            $grid->filter(function ($filter) {
                $filter->like('supplier.id', 'Supplier')->select(MasterSupplier::all()->pluck('name', 'id'));
                $filter->between('created_date', 'Created date')->datetime();
            });
        });
    }

    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('Return Order');
            $content->description('Create');
            $receives = $this->receive_repository->getReturnableOrder();

            $data = [
                'action'   => ACTION_CREATE,
                'receives' => $receives
            ];
            $view = view("inventory.return_order.form", $data);
            $content->body($view);
        });

    }

    public function readCsv()
    {
        $this->validate($this->request, [
            "file" => 'required',
            "receive_id" => 'required'
        ]);

        $file     = $this->request->file('file');
        $receive_id = $this->request->receive_id;
        $receive  = $this->receive_repository->find($receive_id);
        $tempPath = $file->getRealPath();
        $data     = array_map('str_getcsv', file($tempPath));
        foreach ($data as $item) {
            if ($item[self::COLUMN_CSV['OUTLET_CODE']] != $this->store_code || $item[self::COLUMN_CSV['SUPPLIER_CODE']] != $receive->supplier->code) {
                return response()->json(['message' => 'Outlet code or supplier code look like wrong!'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $material_detail_codes[] = $item[self::COLUMN_CSV['MATERIAL_DETAIL_CODE']];
        }

        try {
            // Get material details by codes
            $uoms_query       = $this->master_uom_repository->all();
            $uoms             = PosHelper::object_key_column($uoms_query, 'id');
            $material_details = $this->master_material_detail_repository->getMaterialByCodes($material_detail_codes);
            $material_details = PosHelper::object_key_column($material_details, 'code');
            $return           = [
                'data'   => [
                    'material_details' => [],
                    'accounts'         => [],
                    'supplier_code'    => '',
                ],
                'status' => STATUS_TRUE
            ];
            foreach ($data as $key=>$item) {
                $code     = $item[self::COLUMN_CSV['MATERIAL_DETAIL_CODE']];
                $quantity = $item[self::COLUMN_CSV['QUANTITY']];
                $price    = $item[self::COLUMN_CSV['PRICE']];
                $uom      = $item[self::COLUMN_CSV['UOM']];
                $total    = $quantity * $price;
                if (isset($material_details[$code])) {
                    $item_material_detail = $material_details[$code];

                    $return['data']['supplier_code']      = $item[self::COLUMN_CSV['SUPPLIER_CODE']];
                    $return['data']['material_details'][] = [
                        'order'                   => $key,
                        'material_id'             => $item_material_detail->material_id,
                        'material_detail_id'      => $item_material_detail->id,
                        'material_detail_code'    => $item_material_detail->code,
                        'material_detail_name'    => $item_material_detail->name,
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
                            'account_id'   => $item_material_detail->material->account->code,
                            'account_code' => $item_material_detail->material->account->code,
                            'account_name' => $item_material_detail->material->account->name,
                            'total'        => $total,
                        ];
                    } else {
                        $return['data']['accounts'][$item_material_detail->material->account->code]['total'] += $total;
                    }
                }
            }
            $return['message'] = 'Added Material detail successfully';
        } catch (\Exception $exc) {
            return response()->json(['message' => 'File import not correct format. (' . $exc->getMessage() . ')'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json($return);
    }

    public function save()
    {
        $this->validate($this->request, [
            "supplier_code"                         => 'required|exists:inventory_master_supplier,code',
            "receive_id"                            => 'required|numeric|exists:inventory_receive,id',
            "invoice_number"                        => 'required',
            //"return_date"                           => 'required|date_format:"Y-m-d"',
            "total"                                 => 'required|numeric',
            "material_details.*.material_detail_id" => 'required|numeric|exists:inventory_master_material_detail,id',
            "material_details.*.account_id"         => 'required|numeric|exists:inventory_master_account,id',
            "material_details.*.quantity"           => 'required|numeric',
            "material_details.*.price"              => 'required|numeric',
            "material_details.*.total"              => 'required|numeric'
        ]);
        $user_id          = PosHelper::getCurrentUser('id');
        $data             = $this->request->all();
        $supplier_code    = $data['supplier_code'];
        $receive_id       = $data['receive_id'];
        $invoice_number   = $data['invoice_number'];
        //$transaction_date = $data['return_date'];
        $material_details = $data['material_details'];
        $total            = $data['total'];

        $supplier = $this->master_supplier_repository->getByCode($supplier_code);
        $pcc      = $this->pcc_repository->getByCurrentDate();

        $return = [
            'store_code'       => $this->store_code,
            'supplier_id'      => $supplier->id,
            'receive_id'       => $receive_id,
            'pcc_id'           => $pcc->id,
            'invoice_number'   => $invoice_number,
            //'transaction_date' => $transaction_date,
            'description'      => '',
            'total'            => $total,
            'status_id'        => STATUS_ACTIVE,
            'is_active'        => STATUS_ACTIVE,
            'created_date'     => now(),
            'created_by'       => $user_id
        ];
        DB::beginTransaction();
        $return_order = $this->return_repository->create($return);

        foreach ($material_details as $item) {
            $return_detail = [
                'return_id'          => $return_order->id,
                'material_id'        => $item['material_id'],
                'material_detail_id' => $item['material_detail_id'],
                'uom_id'             => $item['uom_id'],
                'account_id'         => $item['account_id'],
                'quantity'           => $item['quantity'],
                'price'              => $item['price'],
                'total'              => $item['total'],
                'is_active'          => STATUS_ACTIVE,
                'created_date'       => now(),
                'created_by'         => $user_id
            ];
            if ($item['quantity'] > 0) {
                $this->return_detail_repository->insert($return_detail);
            }
        }

        // Export csv file
        $file = $this->return_detail_repository->createCSV($return_order->id, $this->store_code);
        // Send Ftp
        /*$destination_ftp_server = FTP_TRANSACTION_FOLDER_TRANSFER . DIRECTORY_SEPARATOR . $file['file_name'];
        PosHelper::push_fpt(
            $destination_ftp_server,
            $file['path']. DIRECTORY_SEPARATOR . $file['file_name'],
            FTP_HOST,
            FTP_PORT,
            FTP_TIMEOUT,
            FTP_TRANSACTION_USER_NAME,
            FTP_TRANSACTION_PASSWORD
        );*/
        // Update return again
        $this->return_repository->update([
            'path'      => $file['path'],
            'file_name' => $file['file_name']
        ], $return_order->id);

        $this->updateStatus($return_order->id, TRANSACTION_ORDER_STATUS_PENDING);
        // Update material detail for has transaction
        $material_detail_ids = $this->return_detail_repository->findByAttributes(['return_id' => $return_order->id])->pluck('material_detail_id')->toArray();
        $this->master_material_detail_repository->UpdateByIds(['has_transaction' => 1], $material_detail_ids);

        DB::commit();
        $data = [
            'status'  => STATUS_TRUE,
            'message' => 'Save return success',
        ];
        return response()->json($data);
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('Return');
            $content->description('Edit');
            $return         = $this->return_repository->find($id);
            $return_details = $this->return_detail_repository->getByReturnId($return->id);
            $receives       = $this->receive_repository->findByAttributes([
                'is_returnable' => STATUS_ACTIVE
            ]);;
            $return_accounts = [];

            // Group account
            foreach ($return_details as $return_detail) {
                $total = $return_detail->quantity * $return_detail->price;
                if (!array_key_exists($return_detail->account_code, $return_accounts)) {
                    $return_accounts[$return_detail->account_code] = [
                        'account_id' => $return_detail->account_id,
                        'code'       => $return_detail->account_code,
                        'name'       => $return_detail->account_name,
                        'total'      => $total
                    ];
                } else {
                    $return_accounts[$return_detail->account_code]['total'] += $total;
                }
            }
            $data = [
                'action'          => ACTION_VIEW,
                'receives'        => $receives,
                'return'          => $return,
                'return_details'  => $return_details,
                'return_accounts' => $return_accounts
            ];
            $view = view("inventory.return_order.form", $data);
            $content->body($view);
        });

    }

    function updateStatus($return_id, $status)
    {
        $return_status_log = $this->return_status_log_repository->getQueryByAttributes(['return_id' => $return_id])->first();
        $this->return_status_log_repository->insert([
            "return_id"    => $return_id,
            "status_old"   => $return_status_log->status_new ?? '',
            "status_new"   => $status,
            'created_date' => date('Y-m-d H:i:s'),
            'created_by'   => PosHelper::getCurrentUser('id')
        ]);
    }

}
