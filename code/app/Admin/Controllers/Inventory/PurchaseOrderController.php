<?php

namespace App\Admin\Controllers\Inventory;

use App\Model\Inventory\MasterSupplier;
use App\Model\Inventory\PurchaseOrder;
use App\Repository\Inventory\{MasterPCCRepository,
    PurchaseOrderRepository,
    PurchaseOrderDetailRepository,
    MasterSupplierRepository,
    MasterMaterialDetailRepository,
    PurchaseOrderStatusLogRepository,
    ReceiveOrderRepository};
use App\Admin\Controllers\BaseController;
use Encore\Admin\{Grid};
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Helpers\{PosHelper, ConfigHelp};

class PurchaseOrderController extends BaseController
{
    use ModelForm;

    private $supplierRep;
    private $purchaseRep;
    private $purchaseDetailRep;
    private $materialDetailRep;
    private $pccRep;
    private $purchaseStatusLogRep;
    private $storeCode;

    public function __construct(
        PurchaseOrderRepository $purchaseRep,
        PurchaseOrderDetailRepository $purchaseDetailRepRep,
        MasterSupplierRepository $supplierRep,
        MasterMaterialDetailRepository $materialDetailRep,
        MasterPCCRepository $pccRep,
        PurchaseOrderStatusLogRepository $purchaseStatusLogRep
    )
    {
        parent::__construct();
        $this->supplierRep          = $supplierRep;
        $this->purchaseRep          = $purchaseRep;
        $this->purchaseDetailRep    = $purchaseDetailRepRep;
        $this->materialDetailRep    = $materialDetailRep;
        $this->pccRep               = $pccRep;
        $this->purchaseStatusLogRep = $purchaseStatusLogRep;
        $this->storeCode            = ConfigHelp::get("outlet_code");
    }

    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Purchase Order');
            $content->description('List');
            $content->body("<style>.grid-row-delete{display:none !important}</style>");
            $content->body($this->grid());
            $content->body("<script src='" . admin_asset("/js/inventory/purchase_order.js?v=" . time()) . "'></script>");
        });
    }

    protected function grid()
    {
        return Admin::grid(PurchaseOrder::class, function (Grid $grid) {
            $grid->disableRowSelector();
            $grid->disableExport();
            $grid->actions(function ($actions) {
                $html = "";
                if ($actions->row->status_id != TRANSACTION_ORDER_STATUS_REJECTED) {
                    if ($actions->row->status_id == TRANSACTION_ORDER_STATUS_PENDING) {
                        $onclick = "approvePO(this, {$actions->row->id}," . TRANSACTION_ORDER_STATUS_APPROVED . ")";
                        $html    .= "<button onclick='$onclick' class='btn btn-sm btn-primary'><i class='fa fa-check'></i>  Approve</button>";
                    } else if ($actions->row->status_id == TRANSACTION_ORDER_STATUS_APPROVED && empty($actions->row->receive)) {
                        $text              = $actions->row->supplier['is_import_do'] == 1 ? 'Create DO' : 'Create Receive';
                        $url_receive_order = url('/admin/inventory/receive-order/create?purchase_id=' . $actions->row->id);
                        $html              .= "<a href='{$url_receive_order}' class='btn btn-sm btn-success'><i class='fa fa-plus'></i> {$text}</a>";
                    } else if (!empty($actions->row->receive)) {
                        $label_text        = SWITCH_TRANSACTION_RECEIVE_ORDER_STATUS[$actions->row->receive->status_id];
                        $url_receive_order = url('/admin/inventory/receive-order/' . $actions->row->receive->id);
                        if ($actions->row->receive->status_id != TRANSACTION_ORDER_STATUS_APPROVED) {
                            $html .= "<a href='{$url_receive_order}' class='btn btn-sm btn-info'>{$label_text}</a>";
                        }
                    }
                }
                // Button report
                $url = url('/admin/inventory-report/purchase-order/' . $actions->row->id);
                $receive_status  = isset($actions->row->receive) ? $actions->row->receive->status_id : 0;
                if (!empty($actions->row->path) && ($receive_status != TRANSACTION_ORDER_STATUS_APPROVED)) {
                    // Button download CSV
                    $path = '"' . route('download_eod_csv') . "?path={$actions->row->path}" . "&file_name=" . $actions->row->file_name . '"';
                    $html .= "<button class='btn btn-sm btn-success' onclick='download_file({$path})'><i class='fa fa-download'></i></i> Download</button>";
                }
                if ($actions->row->status_id == TRANSACTION_ORDER_STATUS_APPROVED && ($receive_status != TRANSACTION_ORDER_STATUS_APPROVED)) {
                    $html .= "<a class='btn btn-sm btn-default' href='{$url}'><i class='fa fa-bar-chart'></i> Report</a>";
                }

                $actions->disableEdit();
                $actions->disableView();
                $actions->append($html);
            });

            $grid->model()->orderBy("id", "desc");
            $grid->model()->where('status_id', '!=', TRANSACTION_ORDER_STATUS_REJECTED);

            $grid->column('code', 'PO#')->display(function ($code) {
                $url_management_order = url('/admin/inventory/purchase-order/' . $this->id);
                return "<a href='{$url_management_order}'>{$code}</a>";
            })->sortable();
            $grid->column('supplier.name', 'Supplier');
            $grid->column('created_date', 'PO Created Date');
            $grid->column('delivery_date', 'Delivery Date');
            $grid->column('', 'Received Date')->display(function () {
                return isset($this->receive) && $this->receive->status_id == RECEIVE_STATUS_CONFIRMED ? date_format($this->receive->created_date, 'Y-m-d')  : '';
            });
            $grid->column('receive', 'Invoice#')->display(function ($receive) {
                $receive = $this->receive;
                if (!empty($receive) && $receive->status_id == TRANSACTION_ORDER_STATUS_APPROVED) {
                    $url_receive_order = url('/admin/inventory/receive-order/' . $receive->id);
                    return "<a href='{$url_receive_order}' class='margin-r-5'>{$receive->invoice_number}</a>";
                } else {
                    return '';
                }

            });
            // Filter
            $grid->filter(function ($filter) {
                $filter->like('code', 'PO Code');
                $filter->like('receive.invoice_number', 'Invoice number');
                $filter->equal('supplier_id', "Supplier")->select(MasterSupplier::all()->pluck('name', 'id'));
                $filter->between('created_date', 'Create date')->date([
                    'format'      => 'YYYY-MM-DD',
                    'defaultDate' => date('Y-m-d')
                ]);
            });
        });
    }

    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('Purchase Order');
            $content->description('Create');
            $data       = [
                'title'         => 'Detail',
                'action'        => ACTION_CREATE,
                'delivery_date' => date('Y-m-d'),
                'status'        => '',
                'code'          => $this->purchaseRep->generateCode(),
                'supplier_list' => $this->supplierRep->getAll(),
            ];
            $customView = view("inventory.purchase_order.form", $data);

            $content->body($customView);
        });

    }

    public function store()
    {
        $this->validate($this->request, [
            "delivery_date"                              => 'required|date_format:"Y-m-d"',
            "purchase_order_detail.*.material_detail_id" => 'required|numeric',
            "purchase_order_detail.*.quantity"           => 'required|numeric|min:0',
            "purchase_order_detail.*.price"              => 'required|numeric|min:0'
        ]);

        $data = $this->request->only([
            "delivery_date",
            "supplier_id",
            "purchase_order_detail"
        ]);

        $dataDetail   = $data['purchase_order_detail'];
        $user_created = PosHelper::getCurrentUser('id');

        DB::beginTransaction();
        $pcc = $this->pccRep->getByCurrentDate();
        $po  = $this->purchaseRep->create([
            "code"          => $this->purchaseRep->generateCode(),
            "supplier_id"   => $data['supplier_id'],
            "pcc_id"        => $pcc->id,
            "delivery_date" => $data['delivery_date'],
            "status_id"     => TRANSACTION_ORDER_STATUS_PENDING,
            'created_by'    => $user_created
        ]);

        $dataPODetail = [];
        $total        = 0;
        $quantity     = 0;
        foreach ($dataDetail as $item) {
            $dataPODetail[] = [
                'purchase_id'        => $po->id,
                'material_id'        => $item['material_id'],
                'material_detail_id' => $item['material_detail_id'],
                'uom_id'             => $item['uom_id'],
                'account_id'         => $item['account_id'],
                'quantity'           => $item['quantity'],
                'price'              => $item['price'],
                'total'              => $item['price'] * $item['quantity'],
                'created_by'         => $user_created
            ];
            $total          += $item['price'] * $item['quantity'];
            $quantity       += $item['quantity'];
        }

        $this->purchaseDetailRep->insertMultiple($dataPODetail);

        $this->purchaseRep->update([
            "quantity" => $quantity,
            "total"    => $total
        ], $po->id);

        $this->createLogStatus($po->id, TRANSACTION_ORDER_STATUS_PENDING);

        DB::commit();

        return response([
            'status'  => true,
            'message' => "Purchase order created successfully.",
            'data'    => '',
        ], Response::HTTP_OK);
    }

    public function edit($id)
    {
        $poDetail        = $this->purchaseRep->find($id);
        $purchase_detail = $this->purchaseDetailRep->getByPOId($id);
        foreach ($purchase_detail as &$item) {
            $item->uoms = [
                $item->supplier_uom_id . '_-_' . $item->supplier_uom_name => $item->supplier_uom_id . '_-_' . $item->supplier_uom_name,
                $item->smaller_uom_id . '_-_' . $item->smaller_uom_name => $item->smaller_uom_id . '_-_' . $item->smaller_uom_name
            ];
        }
        $poDetail->purchase_detail = $purchase_detail;
        $poDetail->user_admin;
        $poDetail->user_admin_updated;
        $poDetail->account;

        return Admin::content(function (Content $content) use ($poDetail) {
            $content->header('Purchase Order');
            $content->description('Detail');
            $po_detail_json = json_encode($poDetail->toArray(), true);
            $data           = [
                'title'          => 'Detail',
                'action'         => ACTION_VIEW,
                'delivery_date'  => $poDetail->delivery_date,
                'po_detail'      => $poDetail->toArray(),
                'po_detail_json' => $po_detail_json,
                'code'           => $poDetail->code,
                'supplier_list'  => $this->supplierRep->getAll(),
            ];
            $customView     = view("inventory.purchase_order.form", $data);

            $content->body($customView);
        });
    }

    public function update($id)
    {
        $this->validate($this->request, [
            "purchase_order_detail.*.material_detail_id" => 'required|numeric',
            "purchase_order_detail.*.quantity"           => 'required',
        ]);

        $data              = $this->request["purchase_order_detail"];
        $is_edit_po_detail = $this->request["is_edit_po_detail"];
        $po                = $this->purchaseRep->find($id);

        if ($po->status_id == TRANSACTION_ORDER_STATUS_PENDING) {
            DB::beginTransaction();
            try {
                $quantityUpdate   = 0;
                $poDetailIdNotQty = [];

                foreach ($data as $item) {
                    if ($item['quantity'] > 0 || $is_edit_po_detail) {
                        $this->purchaseDetailRep->updateWithConditions([
                            'quantity'   => $item['quantity'],
                            'uom_id'     => $item['uom_id'],
                            'updated_by' => PosHelper::getCurrentUser('id'),
                        ], [
                            'purchase_id'        => $id,
                            'material_detail_id' => $item['material_detail_id']
                        ]);
                        $quantityUpdate++;
                    } else {
                        $poDetailIdNotQty[] = $item['po_id'];
                    }
                }

                if ($quantityUpdate == 0 && !$is_edit_po_detail) {
                    abort(Response::HTTP_BAD_REQUEST, "There is at least one record must fill the quantity");
                }
                if (!$is_edit_po_detail) {
                    // Remove data did not put qty
                    $this->purchaseDetailRep->deleteByIds($poDetailIdNotQty);
                    // Create Csv and send FPT & Changed status to Approved
                    $this->createCSVAndSendFtp($id);
                    $this->createLogStatus($id, TRANSACTION_ORDER_STATUS_APPROVED);

                }

            } catch (\Exception $ex) {
                return response([
                    'status'  => false,
                    'message' => $ex->getMessage(),
                    'data'    => $data,
                ], Response::HTTP_INTERNAL_SERVER_ERROR);

                DB::rollback();
            }

            DB::commit();
        }

        return response([
            'status'  => true,
            'message' => $is_edit_po_detail ? 'Updated purchase order detail successfully. ' : 'Sent FPT successfully. ',
            'data'    => '',
        ], Response::HTTP_OK);
    }

    public function updateStatus($id)
    {
        $this->validate($this->request, [
            'status' => 'required|in:' . implode(',', [TRANSACTION_ORDER_STATUS_APPROVED])
        ], []);

        $po = $this->purchaseRep->find($id);

        if ($po->quantity <= 0) {
            return response([
                'status'  => false,
                'message' => "There is at least one record must fill the quantity",
                'data'    => '',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($po && $po->status_id == TRANSACTION_ORDER_STATUS_PENDING) {
            $this->purchaseDetailRep->deleteEmptyQtyByPOId($id);
            $this->createCSVAndSendFtp($id);
            $this->createLogStatus($id, $this->request['status']);
        }

        return response([
            'status'  => true,
            'message' => "Approved successfully.",
            'data'    => '',
        ], Response::HTTP_OK);
    }

    protected function createLogStatus($purchase_id, $status)
    {
        $purchase_status_log = $this->purchaseStatusLogRep->getQueryByAttributes(['purchase_id' => $purchase_id])->first();
        $this->purchaseStatusLogRep->insert([
            "purchase_id"  => $purchase_id,
            "status_old"   => $purchase_status_log->status_new ?? '',
            "status_new"   => $status,
            'created_date' => date('Y-m-d H:i:s'),
            'created_by'   => PosHelper::getCurrentUser('id')
        ]);
    }

    public function reportPurchaseDetail($id)
    {
        $purchase = $this->purchaseRep->find($id);
        foreach ($purchase->purchase_detail as &$item) {
            $item->material_detail_name = $item->material_detail->name;
            $item->account_name         = $item->account->name;
            $item->uom                  = $item->uom->name;
            unset($item->material_detail);
            unset($item->account);
        }
        $data['data']   = $purchase;
        $data['period'] = '';
        $data['date']   = date("d/m/Y");
        $data['time']   = date("H:i:s");
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget']            = '';
        $data['outlet']            = ConfigHelp::getInstance();
        $data['user']              = PosHelper::getCurrentUser();
        $data['base_url_download'] = '';

        return view("inventory_report.report_purchase_detail", $data);
    }

    public function destroy()
    {
        return response()->json([
            'status'  => false,
            'message' => "Not Allow!",
        ]);
    }

    public function createCSVAndSendFtp($po_id)
    {
        // Export csv file
        $file = $this->purchaseDetailRep->createCSV($po_id, $this->storeCode);
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
        $this->purchaseRep->update([
            'status_id'    => TRANSACTION_ORDER_STATUS_APPROVED,
            'path'         => $file['path'],
            'file_name'    => $file['file_name'],
            'updated_by'   => PosHelper::getCurrentUser('id'),
            'updated_date' => date('Y-m-d H:i:s')
        ], $po_id);
        // Update material detail for has transaction
        //$material_detail_ids = $this->purchaseDetailRep->findByAttributes(['purchase_id' => $po_id])->pluck('material_detail_id')->toArray();
        // $this->materialDetailRep->UpdateByIds(['has_transaction' => 1], $material_detail_ids);
    }

}
