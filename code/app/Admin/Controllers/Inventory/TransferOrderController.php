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
use App\Model\Inventory\{TransferOrder};
use App\Repository\OutletRepository;
use App\Repository\Inventory\{MasterPCCRepository,
    MasterMaterialDetailRepository,
    MasterUomRepository,
    TransferOrderRepository,
    TransferOrderDetailRepository,
    TransferOrderStatusLogRepository};
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TransferOrderController extends BaseController
{
    use ModelForm;

    CONST COLUMN_CSV = [
        'OUTLET_FROM'          => 0,
        'OUTLET_TO'            => 1,
        'INVOICE_NUMBER'       => 2,
        'MATERIAL_DETAIL_CODE' => 3,
        'QUANTITY'             => 4,
        'UOM'                  => 5,
        'PRICE'                => 6
    ];

    private $master_material_detail_repository;
    private $config_repository;
    private $pcc_repository;
    private $outlet_repository;
    private $transfer_repository;
    private $transfer_detail_repository;
    private $store_code;
    private $transfer_status_log_repository;
    private $master_uom_repository;

    public function __construct(
        MasterMaterialDetailRepository $master_material_detail_repository,
        MasterPCCRepository $pcc_repository,
        OutletRepository $outlet_repository,
        TransferOrderRepository $transfer_repository,
        TransferOrderDetailRepository $transfer_detail_repository,
        TransferOrderStatusLogRepository $transfer_status_log_repository,
        MasterUomRepository $master_uom_repository
    )
    {
        parent::__construct();
        $this->master_material_detail_repository = $master_material_detail_repository;
        $this->pcc_repository                    = $pcc_repository;
        $this->outlet_repository                 = $outlet_repository;
        $this->transfer_repository               = $transfer_repository;
        $this->transfer_detail_repository        = $transfer_detail_repository;
        $this->transfer_status_log_repository    = $transfer_status_log_repository;
        $this->master_uom_repository             = $master_uom_repository;
        $this->store_code                        = ConfigHelp::get("outlet_code");
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Transfer');
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
        return Admin::grid(TransferOrder::class, function (Grid $grid) {
            $grid->disableRowSelector();
            $grid->disableExport();
            $grid->model()->orderBy('id', 'DESC');
            $grid->column('type', 'Transfer Type')->display(function ($type) {
                if ($type === TRANSFER_TYPE_IN) {
                    $text_type = TRANSFER_TYPE[TRANSFER_TYPE_IN];
                } else {
                    $text_type = TRANSFER_TYPE[TRANSFER_TYPE_OUT];
                }
                return '<span>' . $text_type . '</span>';
            });
            $grid->invoice_number('Transfer Number');
            $grid->column('from_outlet.name', 'Outlet From')->display(function () {
                $from_outlet = $this->from_outlet;
                return $from_outlet['name'] . ' - ' . $from_outlet['code'];
            });
            $grid->column('to_outlet.name', 'Outlet To')->display(function () {
                $to_outlet = $this->to_outlet;
                return $to_outlet['name'] . ' - ' . $to_outlet['code'];
            });
            $grid->total('Total');
            $grid->column('user_admin.name', 'Created by');
            $grid->created_date();
            $grid->column('path', 'Transfer File')->display(function ($path) {
                if (empty($path)) {
                    return '';
                }
                $path = "'" . route('download_transfer_order') . "?path={$path}" . "&file_name=" . $this->file_name . "'";
                return '<button class="btn btn-xs btn-success" onclick="download_file(' . $path . ')"><i class="fa fa-download" aria-hidden="true"></i></i>&nbsp;&nbsp;Download</button>';
            });
//            $grid->column('path_cosyst', 'Cosyst File')->display(function ($path_cosyst) {
//                if (empty($path_cosyst)) {
//                    return '';
//                }
//                $path_cosyst = "'" . route('download_transfer_order') . "?path={$path_cosyst}" . "&file_name=" . $this->file_name_cosyst . "'";
//                return '<button class="btn btn-xs btn-success" onclick="download_file(' . $path_cosyst . ')"><i class="fa fa-download" aria-hidden="true"></i></i>&nbsp;&nbsp;Download</button>';
//            });
            $grid->actions(function ($actions) {
                $actions->disableEdit();
                $actions->disableDelete();
            });
            $grid->filter(function ($filter) {
                $filter->between('created_date', 'Created date')->datetime();
            });
        });
    }

    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('Transfer Order');
            $content->description('Create');
            $outlets = $this->outlet_repository->all('ASC', 'code');

            $data = [
                'action'     => ACTION_CREATE,
                'outlets'    => $outlets,
                'store_code' => $this->store_code
            ];
            $view = view("inventory.transfer_order.form", $data);
            $content->body($view);
        });

    }


    public function readCsv()
    {
        $this->validate($this->request, [
            "file" => 'required',
        ]);

        $file     = $this->request->file('file');
        $tempPath = $file->getRealPath();
        $data     = array_map('str_getcsv', file($tempPath));
        foreach ($data as $item) {
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
                    'material_details' => []
                ],
                'status' => STATUS_TRUE
            ];
            foreach ($data as $item) {
                $code     = $item[self::COLUMN_CSV['MATERIAL_DETAIL_CODE']];
                $quantity = $item[self::COLUMN_CSV['QUANTITY']];
                $price    = $item[self::COLUMN_CSV['PRICE']];
                $uom      = $item[self::COLUMN_CSV['UOM']];
                $total    = $quantity * $price;
                if (isset($material_details[$code])) {
                    $item_material_detail = $material_details[$code];

                    $return['data']['material_details'][] = [
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
                }
            }
            $return['data']['outlet_from']    = $data[0][self::COLUMN_CSV['OUTLET_FROM']] ?? '';
            $return['data']['invoice_number'] = $data[0][self::COLUMN_CSV['INVOICE_NUMBER']] ?? '';
            $return['message']                = 'Added Material detail successfully';
        } catch (\Exception $exc) {
            return response()->json(['message' => 'File import not correct format. (' . $exc->getMessage() . ')'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        return response()->json($return);
    }

    public function save()
    {
        $this->validate($this->request, [
            "type"                          => 'required|numeric',
            "from_outlet_id"                => 'required|numeric|exists:outlet_master,id',
            "to_outlet_id"                  => 'required|numeric|exists:outlet_master,id',
            "from_outlet_code"              => 'required|exists:outlet_master,code',
            "to_outlet_code"                => 'required|exists:outlet_master,code',
            "invoice_number"                => 'required',
            //"transfer_date"                 => 'required|date_format:"Y-m-d"',
            "total"                         => 'required|numeric',
            "material_details.*.id"         => 'required|numeric',
            "material_details.*.account_id" => 'required|numeric|exists:inventory_master_account,id',
            "material_details.*.quantity"   => 'required|numeric',
            "material_details.*.price"      => 'required|numeric',
            "material_details.*.total"      => 'required|numeric'
        ]);
        $user_id          = PosHelper::getCurrentUser('id');
        $data             = $this->request->all();
        $type             = $data['type'];
        $from_outlet_id   = $data['from_outlet_id'];
        $to_outlet_id     = $data['to_outlet_id'];
        $invoice_number   = $data['invoice_number'];
        //$transaction_date = $data['transfer_date'];
        $material_details = $data['material_details'];
        $total            = $data['total'];

        $pcc    = $this->pcc_repository->getByCurrentDate();
        $return = [
            'type'             => $type,
            'from_outlet_id'   => $from_outlet_id,
            'to_outlet_id'     => $to_outlet_id,
            'pcc_id'           => $pcc->id,
            'invoice_number'   => $invoice_number,
            //'transaction_date' => $transaction_date,
            'total'            => $total,
            'status_id'        => STATUS_ACTIVE,
            'is_active'        => STATUS_ACTIVE,
            'created_date'     => now(),
            'created_by'       => $user_id
        ];
        DB::beginTransaction();
        $transfer_order = $this->transfer_repository->create($return);

        foreach ($material_details as $item) {
            if ($item['quantity'] > 0) {
                $transfer_detail = [
                    'transfer_id'        => $transfer_order->id,
                    'material_id'        => $item['material_id'],
                    'material_detail_id' => $item['id'],
                    'uom_id'             => $item['uom_id'],
                    'account_id'         => $item['account_id'],
                    'quantity'           => $item['quantity'],
                    'price'              => $item['price'],
                    'total'              => $item['total'],
                    'created_date'       => now(),
                    'created_by'         => $user_id
                ];
                $this->transfer_detail_repository->insert($transfer_detail);
            }
        }

        // Export csv file
        $data_update = [];
        if ($type == TRANSFER_TYPE_OUT) {
            $file                     = $this->transfer_detail_repository->exportCsvByTransfer($transfer_order->id, $transfer_order->invoice_number);
            $data_update['path']      = $file['path'];
            $data_update['file_name'] = $file['file_name'];
        }

        $file_cosyst = $this->transfer_detail_repository->exportCsvByTransferCosyst($transfer_order->id, $this->store_code);
        // Send Ftp
        /*$destination_ftp_server = FTP_TRANSACTION_FOLDER_TRANSFER . DIRECTORY_SEPARATOR . $file_cosyst['file_name'];
        PosHelper::push_fpt(
            $destination_ftp_server,
            $file['path']. DIRECTORY_SEPARATOR . $file_cosyst['file_name'],
            FTP_HOST,
            FTP_PORT,
            FTP_TIMEOUT,
            FTP_TRANSACTION_USER_NAME,
            FTP_TRANSACTION_PASSWORD
        );*/

        // Update transfer again
        $data_update['path_cosyst']      = $file_cosyst['path'];
        $data_update['file_name_cosyst'] = $file_cosyst['file_name'];
        // Update transfer again
        $this->transfer_repository->update($data_update, $transfer_order->id);

        $this->updateStatus($transfer_order->id, TRANSACTION_ORDER_STATUS_PENDING);

        $material_detail_ids = $this->transfer_detail_repository->findByAttributes(['transfer_id' => $transfer_order->id])->pluck('material_detail_id')->toArray();
        $this->master_material_detail_repository->UpdateByIds(['has_transaction' => 1], $material_detail_ids);

        DB::commit();
        $data = [
            'status'  => STATUS_TRUE,
            'message' => 'Save transfer success',
        ];
        return response()->json($data);
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('Transfer');
            $content->description('Edit');
            $outlets           = $this->outlet_repository->all();
            $transfer          = $this->transfer_repository->find($id);
            $transfer_details  = $this->transfer_detail_repository->getByTransferId($transfer->id);
            $transfer_accounts = [];

            // Group account
            foreach ($transfer_details as $transfer_detail) {
                $total = $transfer_detail->quantity * $transfer_detail->price;
                if (!array_key_exists($transfer_detail->account_code, $transfer_accounts)) {
                    $transfer_accounts[$transfer_detail->account_code] = [
                        'account_id' => $transfer_detail->account_id,
                        'code'       => $transfer_detail->account_code,
                        'name'       => $transfer_detail->account_name,
                        'total'      => $total
                    ];
                } else {
                    $transfer_accounts[$transfer_detail->account_code]['total'] += $total;
                }
            }
            $data = [
                'action'            => ACTION_VIEW,
                'outlets'           => $outlets,
                'store_code'        => $this->store_code,
                'transfer'          => $transfer,
                'transfer_details'  => $transfer_details,
                'transfer_accounts' => $transfer_accounts,
            ];
            $view = view("inventory.transfer_order.form", $data);
            $content->body($view);
        });

    }

    function updateStatus($transfer_id, $status)
    {
        $transfer_status_log = $this->transfer_status_log_repository->getQueryByAttributes(['transfer_id' => $transfer_id])->first();
        $this->transfer_status_log_repository->insert([
            "transfer_id"  => $transfer_id,
            "status_old"   => $transfer_status_log->status_new ?? '',
            "status_new"   => $status,
            'created_date' => date('Y-m-d H:i:s'),
            'created_by'   => PosHelper::getCurrentUser('id')
        ]);
    }

}
