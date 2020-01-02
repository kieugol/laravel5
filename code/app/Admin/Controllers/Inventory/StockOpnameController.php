<?php

namespace App\Admin\Controllers\Inventory;

use App\Admin\Controllers\BaseController;
use App\Admin\Exports\Inventory\MCCExport;
use App\Admin\Exports\Inventory\StockOpnameExport;
use App\Repository\AdminUserRepository;
use App\Repository\ConfigRepository;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\{Response};
use Illuminate\Support\Facades\DB;
use App\Helpers\{ConfigHelp, FileHelper, PosHelper};
use App\Model\Inventory\{MasterAccount, MasterGroup, MasterPCC, MasterType, StockOpname};
use Maatwebsite\Excel\Facades\Excel;
use App\Admin\Exports\Inventory\PCCExport;
use App\Repository\OrderRepository;
use App\Repository\Inventory\{CurrentStockRepository,
    MasterMaterialRepository,
    LocationRepository,
    MasterMaterialDetailRepository,
    MasterUomDetailRepository,
    MasterUomRepository,
    MasterPCCRepository,
    StockOpnameDetailRepository,
    StockOpnameRepository,
    StockOpnameRecipeRepository,
    MasterRecipeRepository,
    StockOpnameSummaryRepository,
    MasterMaterialUsageRepository,
    ViewMaterialDetailWithUOMRepository
};

class StockOpnameController extends BaseController
{
    private $stock_opname_rep;
    private $master_pcc_rep;
    private $master_material_rep;
    private $master_material_detail_rep;
    private $master_uom_rep;
    private $master_uom_detail_rep;
    private $location_rep;
    private $stock_opname_detail_rep;
    private $stock_opname_summary_rep;
    private $config_rep;
    private $stock_opname_recipe_rep;
    private $master_recipe_rep;
    private $master_material_usage_rep;
    private $currentStockRep;
    private $order_rep;
    private $vMaterialDetailWithUOMRep;
    private $outlet_code;
    private $outlet_name;
    private $admin_user_rep;

    public function __construct(
        StockOpnameRepository $stock_opname_rep,
        StockOpnameDetailRepository $stock_opname_detail_rep,
        MasterPCCRepository $pccRepository,
        MasterMaterialRepository $master_material_rep,
        MasterMaterialDetailRepository $master_material_detail_rep,
        MasterUomRepository $master_uom_rep,
        MasterUomDetailRepository $master_uom_detail_rep,
        LocationRepository $location_rep,
        ConfigRepository $configRepository,
        StockOpnameRecipeRepository $stock_opname_recipe_rep,
        MasterRecipeRepository $master_recipe_repo,
        StockOpnameSummaryRepository $stock_opname_summary_rep,
        OrderRepository $order_rep,
        MasterMaterialUsageRepository $master_material_usage_rep,
        AdminUserRepository $admin_user_rep,
        CurrentStockRepository $currentStockRep,
        ViewMaterialDetailWithUOMRepository $vMaterialDetailWithUOMRep
    )
    {
        parent::__construct();
        $this->stock_opname_rep           = $stock_opname_rep;
        $this->master_pcc_rep             = $pccRepository;
        $this->master_material_rep        = $master_material_rep;
        $this->master_material_detail_rep = $master_material_detail_rep;
        $this->master_uom_rep             = $master_uom_rep;
        $this->master_uom_detail_rep      = $master_uom_detail_rep;
        $this->location_rep               = $location_rep;
        $this->stock_opname_detail_rep    = $stock_opname_detail_rep;
        $this->config_rep                 = $configRepository;
        $this->stock_opname_recipe_rep    = $stock_opname_recipe_rep;
        $this->stock_opname_summary_rep   = $stock_opname_summary_rep;
        $this->master_recipe_rep          = $master_recipe_repo;
        $this->order_rep                  = $order_rep;
        $this->master_material_usage_rep  = $master_material_usage_rep;
        $this->admin_user_rep             = $admin_user_rep;
        $this->currentStockRep            = $currentStockRep;
        $this->vMaterialDetailWithUOMRep  = $vMaterialDetailWithUOMRep;
        $this->outlet_code                = ConfigHelp::get("outlet_code");
        $this->outlet_name                = ConfigHelp::get("outlet_name");
    }

    /**
     * @return mixed
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('Stock Opname');
            $content->description('List');
            $content->body("<style>span .btn-group{display:none !important}</style>");
            $content->body("<script type='text/javascript' src='" . admin_asset('js/freeze-table.js') . "'></script>");
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
        $current_user_role = $this->admin_user_rep->getRoleCurrentUser();
        return Admin::grid(StockOpname::class, function (Grid $grid) use ($current_user_role) {
            $grid->disableRowSelector();
            $grid->disableExport();
            $grid->model()->orderBy('id', 'DESC');
            $grid->id('ID')->sortable();
            $grid->store_code('Outlet Code')->sortable();;
            $grid->column('master_pcc.name', 'PCC')->sortable();
            $grid->column('master_pcc.month', 'Month')->sortable();
            $grid->column('master_pcc.year', 'Year')->sortable();
            $grid->column('status_id', 'Status')->display(function ($status) {
                return STOCK_OPNAME_STATUS[$status] ?? '';
            })->sortable();
            $grid->column('user_admin.name', 'Created By')->sortable();
            $grid->created_date()->sortable();
            $grid->updated_date()->sortable();
            $grid->column('is_sent_ftp', 'Send FTP')->display(function ($is_sent_ftp) {
                return $is_sent_ftp ? '<i class="fa fa-check text-success"></i>' : '';
            })->sortable();
            $grid->column('stockopname', 'Report')->display(function () {
                //if ($this->status_id == TRANSACTION_ORDER_STATUS_APPROVED) {
                    $pathView = route('stock-opname-report', [$this->id]);
                    $onclick  = "onclick=download_file('" . route('stock-opname-report', [$this->id]) . "?is_exported_file=1" . "')";
                    $strHTML  = "<a href='{$pathView}' class='btn btn-xs btn-default'><i class='fa fa-eye'></i> View</a>";
                    $strHTML  .= "<button class='btn btn-xs btn-success' $onclick><i class='fa fa-download' aria-hidden='true'></i></i> Download</button>";

                    return $strHTML;
                //}
                //return '';
            });

            $grid->column('pcc', 'PCC')->display(function () {
                //if ($this->status_id == TRANSACTION_ORDER_STATUS_APPROVED) {
                    $pathView = route('stock-opname-pcc-report', [$this->pcc_id]);
                    $onclick  = "onclick=download_file('" . route('stock-opname-pcc-report', [$this->pcc_id]) . "?is_exported_file=1" . "')";
                    $strHTML  = "<a href='{$pathView}' class='btn btn-xs btn-default'><i class='fa fa-eye'></i> View</a>";
                    $strHTML  .= "<button class='btn btn-xs btn-success' $onclick><i class='fa fa-download' aria-hidden='true'></i></i> Download</button>";

                    return $strHTML;
                //}
                //return '';
            });

            $grid->column('mcc', 'MCC')->display(function () {
                if ($this->master_pcc['period'] == PERIOD_PCC_3) {
                    $pathView = route('stock-opname-mcc-report', [$this->pcc_id]);
                    $onclick  = "onclick=download_file('" . route('stock-opname-mcc-report', [$this->pcc_id]) . "?is_exported_file=1" . "')";
                    $strHTML  = "<a href='{$pathView}' class='btn btn-xs btn-default'><i class='fa fa-eye'></i> View</a>";
                    $strHTML  .= "<button class='btn btn-xs btn-success' $onclick><i class='fa fa-download' aria-hidden='true'></i></i> Download</button>";

                    return $strHTML;
                }
                return '';
            });
            if (PosHelper::getCurrentUser('username') == SUPERADMIN || $current_user_role == COST_CONTROLER) {
                $grid->column('', 'Change Status')->display(function () {
                    if ($this->status_id == STOCK_OPNAME_STATUS_CONFIRMED) {
                        $pathView = route('stock-opname-update-status', ['id'=> $this->id, 'status_id'=>STOCK_OPNAME_STATUS_DRAFT]);
                        $strHTML  = "<a href='{$pathView}' class='btn btn-xs btn-primary'><i class='fa fa-adjust'></i>Update</a>";
                        return $strHTML;
                    }
                    return '';
                });
            }

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableView();
            });
            $grid->filter(function ($filter) {
                $filter->like('master_pcc.id', 'PCC')->select(MasterPCC::all()->pluck('name', 'id'));
                $filter->between('created_date', 'Created date')->datetime();
                $filter->between('updated_date', 'Updated date')->datetime();
            });
        });
    }

    public function create()
    {
        $material_detail_filter = [];
        // Get what recipe already created with material detail id before
        $stock_opname_recipe = $this->stock_opname_recipe_rep->getMappingRecipeDataByStockOpnameId(0);
        $material_detail     = $this->master_material_detail_rep->getAllForCreateStockOptName()->toArray();
        $location            = $this->location_rep->all('ASC');
        $recipe_data         = $this->master_recipe_rep->getForCreateStockOpname($stock_opname_recipe);
        $uom_detail_query    = $this->master_uom_detail_rep->all();
        $uom_detail          = object_key_column($uom_detail_query, 'id');
        $arrayMaterialId     = array_unique(array_column($material_detail, 'material_id') + array_column($recipe_data, 'material_id'));
        $arrMaterialDetailId = array_unique(array_column($material_detail, 'material_detail_id') + array_column($recipe_data, 'material_detail_id'));
        $dataUsageMaterial   = $this->master_material_usage_rep->getTotalUsageByMaterialIds($arrayMaterialId);
        $dataUOMConversation = $this->vMaterialDetailWithUOMRep->GetDataConversationByMaterialDetailId($arrMaterialDetailId);
        $data_total_usage    = $this->getTotalUsageByGroupByPeriod();

        $this->stock_opname_rep->filterUOMReportForCreate($material_detail, $material_detail_filter, $recipe_data, $location, $uom_detail, $dataUsageMaterial, $dataUOMConversation);

        $info_print = [
            'outlet_code'  => $this->outlet_code,
            'initial'      => '',
            'outlet_name'  => $this->outlet_name,
            'manager'      => PosHelper::getCurrentUser('name'),
            'store_keeper' => PosHelper::getCurrentUser('name'),
            'pcc_default'  => 'PCC 1'
        ];

        return Admin::content(function (Content $content) use ($material_detail_filter, $location, $recipe_data, $data_total_usage, $info_print) {
            $content->header('Stock Opname');
            $content->description('Create');

            $month   = date('m');
            $year    = date('Y');
            $pccList = $this->master_pcc_rep->getAllByPeriod($month, $year);

            $data  = [
                'has_perm_edit'             => 1,
                'disable_edit_form'         => '',
                'action'                    => ACTION_CREATE,
                'month'                     => $month,
                'year'                      => $year,
                'pccs'                      => $pccList,
                'pcc_id'                    => $pccList[0]->id ?? null,
                'locations'                 => $location,
                'master_material_details'   => $material_detail_filter,
                'material_data_json'        => json_encode($material_detail_filter, true),
                'recipe_data'               => $recipe_data,
                'recipe_data_json'          => json_encode($recipe_data, true),
                'stock_opname_summary_json' => json_encode([], true),
                'data_total_usage'          => json_encode($data_total_usage, true),
                'info_print'                => $info_print
            ];
            $view  = view("inventory.stock_opname.form", $data);
            $content->body($view);
        });
    }

    public function save()
    {
        $data                     = $this->request->all();
        $pcc_id                   = $data['pcc_id'];
        $is_sent_ftp              = $data['is_sent_ftp'];
        $store_code               = $this->outlet_code;
        $created_by               = PosHelper::getCurrentUser('id');
        $pccExisted               = $this->stock_opname_rep->findByAttributes(["pcc_id" => $pcc_id]);
        $stock_opname_recipe_data = $this->stock_opname_recipe_rep->filterDataCreate($this->request['stock_opname_recipe']);

        if (!$pccExisted->isEmpty()) {
            abort(Response::HTTP_BAD_REQUEST, 'This PCC already existed.');
        }
        /*if (empty($stock_opname_ercipe_data) && $is_sent_ftp) {
            abort(Response::HTTP_BAD_REQUEST, 'Recipe data is invalid');
        }*/

        $stock_opname = [
            'store_code' => $store_code,
            'pcc_id'     => $pcc_id,
            'status_id'  => STOCK_OPNAME_STATUS_DRAFT,
            'created_by' => $created_by
        ];

        $stt_code = Response::HTTP_OK;
        $response = [
            'status'  => STATUS_TRUE,
            'message' => 'Created Successfully.',
        ];

        DB::beginTransaction();
        try {
            $stock_opname               = $this->stock_opname_rep->create($stock_opname);
            $id                         = $stock_opname->id;
            $stock_opname_detail        = $data['stock_opname_details'];
            $stock_opname_summary       = $data['stock_opname_summary'];
            $data_create_detail         = [];
            $stock_opname_summary_data  = [];
            $material_detail_ids_no_qty = [];

            // Attach stock_opname_id
            foreach ($stock_opname_recipe_data as &$row) {
                $row['stock_opname_id'] = $id;
            }

            foreach ($stock_opname_summary as $stockSummary) {
                $stock_opname_summary_data[$stockSummary['material_detail_id']] = [
                    'stock_opname_id'    => $id,
                    'material_detail_id' => $stockSummary['material_detail_id'],
                    'ending_inv'         => $stockSummary['ending_inv'],
                    'quantity_recipe'    => $stockSummary['quantity_recipe'],
                    'quantity_material'  => $stockSummary['quantity_material'],
                ];
            }

            $totalQty = 0;
            foreach ($stock_opname_detail as $item) {
                $totalLocationQty = 0;
                foreach ($item['locations'] as $location) {
                    $qtyConvertOutlet = ($location['quantity_supplier_uom'] * $item['final_conversion_rate']);

                    $qtyConvert = $location['id'] == LOCATION_BAR_ID && isset($stock_opname_summary_data[$item['material_detail_id']])
                        ? $stock_opname_summary_data[$item['material_detail_id']]['quantity_recipe'] : $qtyConvertOutlet + $location['quantity_outlet_uom'];

                    $data_create_detail[] = [
                        'stock_opname_id'       => $id,
                        'material_detail_id'    => $item['material_detail_id'],
                        'quantity_supplier_uom' => $location['quantity_supplier_uom'],
                        'quantity_outlet_uom'   => $location['quantity_outlet_uom'],
                        'quantity_convert'      => $qtyConvert,
                        'location_id'           => $location['id'],
                        'created_by'            => $created_by
                    ];
                    $totalLocationQty     += $location['quantity_supplier_uom'] + $location['quantity_outlet_uom'];
                }
                if ($totalLocationQty == 0) {
                    $material_detail_ids_no_qty[] = $item['material_detail_id'];
                }
                $totalQty += $totalLocationQty;
            }

            if ($totalQty <= 0 && $is_sent_ftp) {
                abort(Response::HTTP_BAD_REQUEST, 'Material is invalid');
            }

            $this->stock_opname_detail_rep->insertMultiple($data_create_detail);
            $this->stock_opname_recipe_rep->insertMultiple($stock_opname_recipe_data);
            $this->stock_opname_summary_rep->insertMultiple($stock_opname_summary_data);

            if ($is_sent_ftp) {
                // Re-update material did not put qty
                $this->master_material_detail_rep->UpdateByIds(['has_transaction' => 0], $material_detail_ids_no_qty);
                // Save csv
                $file = $this->createCSVFile($id);
                $this->stock_opname_rep->generateSaleDataCSV($pcc_id, $this->outlet_code, $this->order_rep, $this->master_pcc_rep);
                // Calculating summary pcc report
                $this->stock_opname_rep->update([
                    'path'        => $file['path'],
                    'file_name'   => $file['file_name'],
                    'status_id'   => STOCK_OPNAME_STATUS_CONFIRMED,
                    'is_sent_ftp' => STATUS_ACTIVE
                ], $id);
                $response['message'] = 'Created and Sent FTP Successfully.';
            }
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollback();
            $stt_code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response = [
                'status'  => STATUS_FALSE,
                'message' => $ex->getMessage(),
            ];
        }

        return response()->json($response, $stt_code);
    }

    public function edit($id)
    {
        $current_user_role = $this->admin_user_rep->getRoleCurrentUser();
        $material_detail_filter    = [];
        $material_detail           = $this->stock_opname_detail_rep->getDetailByStockOpnameIdForUpdate($id)->toArray();
        $stock_opname_recipe       = $this->stock_opname_recipe_rep->getMappingRecipeDataByStockOpnameId($id);
        $recipe_data_master        = $this->master_recipe_rep->getForCreateStockOpname($stock_opname_recipe);
        $stock_opname              = $this->stock_opname_rep->find($id);
        $stock_opname_summary      = $this->stock_opname_summary_rep->getDetailByStockOpnameId($id);
        $stock_opname_summary      = $this->stock_opname_summary_rep->parseStockSummaryData($stock_opname_summary);
        $pccs                      = $this->master_pcc_rep->getAllByPeriod($stock_opname->master_pcc->month, $stock_opname->master_pcc->year);
        $uom_detail_query          = $this->master_uom_detail_rep->all();
        $uom_detail                = object_key_column($uom_detail_query, 'id');
        $arrayMaterialId           = array_unique(array_column($material_detail, 'material_id') + array_column($recipe_data_master, 'material_id'));
        $arrMaterialDetailId       = array_unique(array_column($material_detail, 'material_detail_id') + array_column($recipe_data_master, 'material_detail_id'));
        $dataUOMConversation       = $this->vMaterialDetailWithUOMRep->GetDataConversationByMaterialDetailId($arrMaterialDetailId);
        $dataUsageMaterial         = $this->master_material_usage_rep->getTotalUsageByMaterialIds($arrayMaterialId);
        $data_total_usage          = $this->getTotalUsageByGroupByPeriod();
        $arr_location_id           = explode(',', $material_detail[0]['group_location_id'] ?? '');
        $locations                 = $this->location_rep->getByArrayId($arr_location_id);

        $this->stock_opname_rep->filterUOMReportForUpdate($material_detail, $material_detail_filter, $recipe_data_master, $uom_detail, $dataUsageMaterial, $dataUOMConversation);

        if ($stock_opname->status_id == STOCK_OPNAME_STATUS_DRAFT) {
            // Get what material was created after stock have been created before
            $recipe_data_master_tmp = $recipe_data_master ? [array_first($recipe_data_master)] : [];
            $material_detail_tmp    = $this->master_material_detail_rep->getDataAfterReceiveForStockOptNameUpdate($arrayMaterialId);
            $arrayMaterialIdTmp     = array_unique(array_column($material_detail_tmp, 'material_id') + array_column($recipe_data_master_tmp, 'material_id'));
            $dataUsageMaterialTmp   = $this->master_material_usage_rep->getTotalUsageByMaterialIds($arrayMaterialIdTmp);
            $this->stock_opname_rep->filterUOMReportForCreate($material_detail_tmp, $material_detail_filter_tmp, $recipe_data_master_tmp, $locations, $uom_detail, $dataUsageMaterialTmp, $dataUOMConversation);
            // Merge master material detail
            if (!empty($material_detail_filter_tmp)) {
                $material_detail_filter += $material_detail_filter_tmp;
            }
        }

        // check if pcc is overdue or not, if overdue, lock it
        // get pcc max day
        $pccMaxDay = $stock_opname->master_pcc->year . '-' . $stock_opname->master_pcc->month . '-' . $stock_opname->master_pcc->to_date;

        //get overdue config day and hour
        $overdueConfigDay   = ConfigHelp::get("pcc_lock_day");
        $overdueConfigHour  = ConfigHelp::get("pcc_lock_hour");

        // if overdue config day and hour = 0, or login user = super admin, not lock
        if (($overdueConfigDay == 0 && $overdueConfigHour == 0) || PosHelper::getCurrentUser('username') == SUPERADMIN) {
            $isLock = 0;
        } else {
            $compareDay = date('Y-m-d '.$overdueConfigHour.':00:00', strtotime('+'.$overdueConfigDay.' days', strtotime($pccMaxDay)));

            // if today is bigger than (pcc max day + overdue config day + overdue config hour), then block pcc
            $isLock = time() > strtotime($compareDay) ? 1 : 0;
        }

        $info_print = [
            'outlet_code'  => $this->outlet_code,
            'initial'      => '',
            'outlet_name'  => $this->outlet_name,
            'manager'      => PosHelper::getCurrentUser('name'),
            'store_keeper' => PosHelper::getCurrentUser('name'),
            'pcc_default'  => 'PCC 1'
        ];

        foreach ($material_detail_filter as &$material_detail) {
            $final_recipe_conversion_rate   = $this->master_material_detail_rep->convertToReportUomRate($material_detail, $uom_detail);
            $material_detail['contains']    = '1 ' . $material_detail['supplier_uom_description'] . ' = ' . $final_recipe_conversion_rate . ' ' . $material_detail['report_uom_description'];
        }

        return Admin::content(function (Content $content) use ($id, $stock_opname, $pccs, $locations, $material_detail_filter, $recipe_data_master, $stock_opname_summary, $data_total_usage, $current_user_role, $isLock, $info_print) {
            $content->header('Stock Opname');
            $content->description('Edit');
            $hasPermissionEdit = $stock_opname->status_id != TRANSACTION_ORDER_STATUS_APPROVED || PosHelper::getCurrentUser('username') == SUPERADMIN || $current_user_role == COST_CONTROLER;
            $data = [
                'info_print'                => $info_print,
                'is_lock'                   => $isLock,
                'has_perm_edit'             => $hasPermissionEdit,
                'disable_edit_form'         => !$hasPermissionEdit ? 'disabled' : '',
                'action'                    => ACTION_UPDATE,
                'month'                     => $stock_opname->master_pcc->month,
                'year'                      => $stock_opname->master_pcc->year,
                'pccs'                      => $pccs,
                'pcc_id'                    => $stock_opname->master_pcc->id,
                'locations'                 => $locations,
                'stock_opname'              => $stock_opname,
                'stock_opname_summary'      => $stock_opname_summary,
                'stock_opname_summary_json' => json_encode($stock_opname_summary, true),
                'master_material_details'   => $material_detail_filter,
                'material_data_json'        => json_encode($material_detail_filter, true),
                'recipe_data'               => $recipe_data_master,
                'recipe_data_json'          => json_encode($recipe_data_master, true),
                'data_total_usage'          => json_encode($data_total_usage, true)
            ];
            $view = view("inventory.stock_opname.form", $data);
            $content->body($view);
        });
    }

    public function update()
    {
        $data                     = $this->request->all();
        $id                       = $data['id'];
        $is_sent_ftp              = $data['is_sent_ftp'];
        $stock_opname_details     = $data['stock_opname_details'];
        $stock_opname_summary     = $data['stock_opname_summary'];
        $created_by               = PosHelper::getCurrentUser('id');
        $stock_opname             = $this->stock_opname_rep->find($id);
        $stock_opname_recipe_data = $this->stock_opname_recipe_rep->filterDataCreate($data['stock_opname_recipe']);

        $stt_code = Response::HTTP_OK;
        $response = [
            'status'  => STATUS_TRUE,
            'message' => 'Updated Successfully.',
        ];

        // Do not update anyway when the user have no perm
        if ($stock_opname->status_id == TRANSACTION_ORDER_STATUS_APPROVED && PosHelper::getCurrentUser('username') != SUPERADMIN) {
            return response()->json($response, $stt_code);
        }
        /*if (empty($stock_opname_recipe_data) && $is_sent_ftp) {
            abort(Response::HTTP_BAD_REQUEST, 'Recipe data is invalid');
        }*/
        // Attach stock_opname_id
        foreach ($stock_opname_recipe_data as &$row) {
            $row['stock_opname_id'] = $id;
        }

        $stock_opname_summary_data = [];
        foreach ($stock_opname_summary as $stockSummary) {
            $stock_opname_summary_data[$stockSummary['material_detail_id']] = [
                'stock_opname_id'    => $id,
                'material_detail_id' => $stockSummary['material_detail_id'],
                'ending_inv'         => $stockSummary['ending_inv'],
                'quantity_recipe'    => $stockSummary['quantity_recipe'],
                'quantity_material'  => $stockSummary['quantity_material'],
            ];
        }

        $data_create_detail             = [];
        $material_detail_ids_collection = [
            'no_qty'  => [],
            'has_qty' => [],
        ];

        $totalQty = 0;
        foreach ($stock_opname_details as $item) {
            $totalLocationQty = 0;
            foreach ($item['locations'] as $location) {
                $qtyConvertOutlet = ($location['quantity_supplier_uom'] * $item['final_conversion_rate']);

                $qtyConvert = $location['id'] == LOCATION_BAR_ID && isset($stock_opname_summary_data[$item['material_detail_id']])
                    ? $stock_opname_summary_data[$item['material_detail_id']]['quantity_recipe'] : $qtyConvertOutlet + $location['quantity_outlet_uom'];

                $data_create_detail[] = [
                    'stock_opname_id'       => $id,
                    'material_detail_id'    => $item['material_detail_id'],
                    'quantity_supplier_uom' => $location['quantity_supplier_uom'],
                    'quantity_outlet_uom'   => $location['quantity_outlet_uom'],
                    'quantity_convert'      => $qtyConvert,
                    'location_id'           => $location['id'],
                    'created_by'            => $created_by
                ];
                $totalLocationQty     += $location['quantity_supplier_uom'] + $location['quantity_outlet_uom'];
            }
            // Saving into collection
            $key                                    = $totalLocationQty == 0 ? 'no_qty' : 'has_qty';
            $material_detail_ids_collection[$key][] = $item['material_detail_id'];
            $totalQty                               += $totalLocationQty;
        }

        if ($totalQty <= 0 && $is_sent_ftp) {
            abort(Response::HTTP_BAD_REQUEST, 'Material is invalid');
        }
        
        $result = null;
        DB::beginTransaction();
        try {
            $this->stock_opname_detail_rep->deleteByStockOpnameId($id);
            $this->stock_opname_recipe_rep->deleteByStockOpnameId($id);
            $this->stock_opname_summary_rep->deleteByStockOpnameId($id);

            $this->stock_opname_detail_rep->insertMultiple($data_create_detail);
            $this->stock_opname_recipe_rep->insertMultiple($stock_opname_recipe_data);
            $this->stock_opname_summary_rep->insertMultiple($stock_opname_summary_data);



            if ($is_sent_ftp) {
                // Re-update material putted qty or not
                $this->master_material_detail_rep->UpdateByIds(['has_transaction' => 0], $material_detail_ids_collection['no_qty']);
                $this->master_material_detail_rep->UpdateByIds(['has_transaction' => 1], $material_detail_ids_collection['has_qty']);
                // Save csv
                $file = $this->createCSVFile($id);
                $this->stock_opname_rep->generateSaleDataCSV($stock_opname->pcc_id, $this->outlet_code, $this->order_rep, $this->master_pcc_rep);
                $this->stock_opname_rep->update([
                    'path'        => $file['path'],
                    'file_name'   => $file['file_name'],
                    'is_sent_ftp' => STATUS_ACTIVE,
                    'status_id'   => STOCK_OPNAME_STATUS_CONFIRMED,
                    'updated_by'  => $created_by,
                    'updated_date' => now()
                ], $id);
                $response['message'] = 'Updated and Sent FTP Successfully.';
            } else {
                $this->stock_opname_rep->update(['updated_by' => $created_by, 'updated_date' => now()], $id);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $stt_code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response = ['message' => $ex->getMessage() . $ex->getLine()];
        }
        DB::commit();
        
        return response()->json($response, $stt_code);
    }

    protected function createCSVFile($id)
    {
        $sub_path    = date("Y/m/d");
        $file_name   = $this->outlet_code . CSV_NAME_STOCK_OPNAME;
        $destination = FileHelper::create_sub_folder(INVENTORY_DIR_STOCK_OPNAME_CSV, $sub_path);
        $csvData     = $this->stock_opname_rep->mergeDataForCSV($id);

        return [
            'path'      => $destination,
            'file_name' => PosHelper::generateCSV($csvData, $destination, $file_name)
        ];
    }

    public function generatePCC($pccId)
    {
        $pccId       = intval($pccId);
        $masterPCC   = masterPCC::find($pccId);
        $dateRange   = range($masterPCC->from_date, $masterPCC->to_date);
        $monthFormat = sprintf("%02d", $masterPCC->month);

        $keyTotalGroupFB                   = 'total_f_and_b';
        $paramsDate                        = [
            'fromDate' => "{$masterPCC->year}-{$monthFormat}-{$masterPCC->from_date}",
            'toDate'   => "{$masterPCC->year}-{$monthFormat}-{$masterPCC->to_date}"
        ];
        $masterGroupName                   = MasterGroup::all()->pluck('name', 'id')->toArray();
        $masterGroupName[$keyTotalGroupFB] = 'F & B';

        $dataMCC = $this->stock_opname_rep->getPCCReport($pccId);;
        $result = $this->stock_opname_rep->filterPCCData($dataMCC, $keyTotalGroupFB, $dateRange);

        $res['layout']               = "admin::index";
        $res['current_user']         = PosHelper::getCurrentUser('name');
        $res['outlet_information']   = $this->outlet_code . '.' . $this->outlet_name;
        $res['outlet_code']          = $this->outlet_code;
        $res['column_date']          = $dateRange;
        $res['total_date_range']     = count($dateRange);
        $res['total_group_col_date'] = $res['total_date_range'] * 3;
        $res['key_total_group_fb']   = $keyTotalGroupFB;
        $res['master_type']          = MasterType::all()->pluck('name', 'id')->toArray();
        $res['master_account']       = MasterAccount::all()->pluck('name', 'id')->toArray();
        $res['master_group']         = $masterGroupName;
        $res['data_sale']            = $this->order_rep->calculateSaleFoodAndBeverage($paramsDate, $keyTotalGroupFB);
        $res['data_group']           = $result['data_group'];
        $res['data_account']         = $result['data_account'];
        $res['data_type']            = $result['data_type'];
        $res['data_detail']          = $result['data_detail'];
        $res['period']               = $masterPCC->period;
        $res['month']                = MONTHS[$masterPCC->month] ?? null;
        $res['year']                 = $masterPCC->year;
        $res['periodic_turn_over']   = 10;
        $res['is_exported_file']     = $this->request['is_exported_file'] ?? false;
        $res['character_format']     = $res['is_exported_file'] ? '' : '-';

        if ($res['is_exported_file']) {
            // Disable libxml errors and allow user to fetch error information as needed
            libxml_use_internal_errors(true);
            $extensionFile = PosHelper::get_extension_export_file('excel');
            return Excel::download(new PCCExport($res), "PCC{$masterPCC->period}{$extensionFile}");
        }
        return view("inventory_report.pcc_report", $res);
    }

    public function generateMCC($pccId)
    {
        $pccId       = intval($pccId);
        $masterPCC   = masterPCC::find($pccId);
        $pccRange    = ['pcc_1', 'pcc_2', 'pcc_3'];
        $monthFormat = sprintf("%02d", $masterPCC->month);

        $periodicTurnOver = 30;
        $keyTotalGroupFB  = 'total_f_and_b';
        $paramsDate       = [
            'fromDate' => "{$masterPCC->year}-{$monthFormat}-01", // MCC start date from 01
            'toDate'   => "{$masterPCC->year}-{$monthFormat}-{$masterPCC->to_date}"
        ];

        $masterGroupName                   = MasterGroup::all()->pluck('name', 'id')->toArray();
        $masterGroupName[$keyTotalGroupFB] = 'F & B';

        $dataMCC = $this->stock_opname_rep->getMCCReport($masterPCC->month, $masterPCC->year);
        $result  = $this->stock_opname_rep->filterMCCData($dataMCC, $keyTotalGroupFB, $pccRange, $periodicTurnOver);

        $res['layout']               = "admin::index";
        $res['current_user']         = PosHelper::getCurrentUser('name');
        $res['outlet_information']   = $this->outlet_code . '.' . $this->outlet_name;
        $res['outlet_code']          = $this->outlet_code;
        $res['column_date']          = $pccRange;
        $res['total_date_range']     = count($pccRange);
        $res['total_group_col_date'] = $res['total_date_range'] * 3;
        $res['key_total_group_fb']   = $keyTotalGroupFB;
        $res['master_type']          = MasterType::all()->pluck('name', 'id')->toArray();
        $res['master_account']       = MasterAccount::all()->pluck('name', 'id')->toArray();
        $res['master_group']         = $masterGroupName;
        $res['data_sale']            = $this->order_rep->calculateSaleFoodAndBeverage($paramsDate, $keyTotalGroupFB);
        $res['data_group']           = $result['data_group'];
        $res['data_account']         = $result['data_account'];
        $res['data_type']            = $result['data_type'];
        $res['data_detail']          = $result['data_detail'];
        $res['period']               = $masterPCC->period;
        $res['month']                = MONTHS[$masterPCC->month] ?? null;
        $res['year']                 = $masterPCC->year;
        $res['periodic_turn_over']   = $periodicTurnOver;
        $res['is_exported_file']     = $this->request['is_exported_file'] ?? false;
        $res['character_format']     = $res['is_exported_file'] ? '' : '-';

        if ($res['is_exported_file']) {
            // Disable libxml errors and allow user to fetch error information as needed
            libxml_use_internal_errors(true);
            $extensionFile = PosHelper::get_extension_export_file('excel');
            return Excel::download(new MCCExport($res), "MCC{$extensionFile}");
        }
        return view("inventory_report.mcc_report", $res);
    }

    private function getTotalUsageByGroupByPeriod()
    {
        // Get what recipe already created with material detail id before
        $stock_opname_recipe   = $this->stock_opname_recipe_rep->getMappingRecipeDataByStockOpnameId(0);
        $material_detail       = $this->master_material_detail_rep->getAllForCreateStockOptName()->toArray();
        $recipe_data           = $this->master_recipe_rep->getForCreateStockOpname($stock_opname_recipe);
        $arrayMaterialId       = array_unique(array_column($material_detail, 'material_id') + array_column($recipe_data, 'material_id'));
        $dataUsageMaterial     = $this->master_material_usage_rep->getTotalUsageByMaterialIdsAndGroupByPeriod($arrayMaterialId)->toArray();
        $query_material_detail = $this->master_material_detail_rep->getMaterialDetailForTotalUsage();
        $uom_detail_query      = $this->master_uom_detail_rep->all();
        $uom_detail            = object_key_column($uom_detail_query, 'id');
        $result                = [];
        $all_material_detail   = [];
        $final_conversion_rate = 1;
        foreach ($query_material_detail as &$row) {
            $arr_uoms = [
                [
                    'key'   => $row['outlet_uom_id'],
                    'value' => $row['recipe_uom_detail_id']
                ],
                [
                    'key'   => $row['smaller_uom_id'],
                    'value' => $row['outlet_uom_detail_id']
                ],
                [
                    'key'   => $row['supplier_uom_id'],
                    'value' => $row['smaller_uom_detail_id']
                ],
            ];

            $condition_uom_id = $row['report_uom_id'];
            foreach ($arr_uoms as $arr_uom) {
                $final_conversion_rate *= $uom_detail[$arr_uom['value']]->conversion_rate;
                if ($condition_uom_id == $arr_uom['key']) {
                    break;
                }

            }
            $all_material_detail[$row['material_id']] = $final_conversion_rate;
        }
        foreach ($dataUsageMaterial as $rowUsage) {
            $value_convert                                                       = $all_material_detail[$rowUsage['material_id']] ?? 1;
            $result[$rowUsage['material_id'] . '_' . $rowUsage['master_pcc_id']] = $rowUsage['total_usage'] / $value_convert;
        }

        return $result;
    }

    public function generateStockOpname($id)
    {
        $material_details = $this->stock_opname_detail_rep->getDetailByStockOpnameIdForReport($id)->toArray();
        $uom_detail_query = $this->master_uom_detail_rep->all();
        $uom_detail       = object_key_column($uom_detail_query, 'id');
        foreach ($material_details as &$item) {
            $group_location_id   = explode(',', $item['group_location_id']);
            $group_location_name = explode(',', $item['group_location_name']);
            $group_qty_report    = explode(',', $item['group_quantity_report_uom']);
            foreach ($group_location_id as $key => $location) {
                $item['locations'][$location] = [
                    'id'             => $location,
                    'name'           => $group_location_name[$key],
                    'qty_report_uom' => $group_qty_report[$key],
                ];
            }

            // Conversion from supplier to recipe
            $final_recipe_conversion_rate = 1;
            $arr_recipe_uoms              = [
                [
                    'key'   => $item['supplier_uom_id'],
                    'value' => 1
                ],
                [
                    'key'   => $item['smaller_uom_id'],
                    'value' => $item['smaller_uom_detail_id']
                ],
                [
                    'key'   => $item['outlet_uom_id'],
                    'value' => $item['outlet_uom_detail_id']
                ],
                [
                    'key'   => $item['recipe_uom_id'],
                    'value' => $item['recipe_uom_detail_id']
                ],
            ];

            $condition_recipe_uom_id = $item['report_uom_id'];
            foreach ($arr_recipe_uoms as $key => $arr_recipe_uom) {
                if ($key != 0) {
                    $final_recipe_conversion_rate *= $uom_detail[$arr_recipe_uom['value']]->conversion_rate;
                }
                if ($condition_recipe_uom_id == $arr_recipe_uom['key']) {
                    break;
                }
            }
            $item['contains'] = '1 ' . $item['supplier_uom_description'] . ' = ' . $final_recipe_conversion_rate . ' ' . $item['report_uom_description'];

            ksort($item['locations']);
        }

        $pccId     = isset($material_details[0]) ? intval($material_details[0]['pcc_id']) : 0;
        $masterPCC = masterPCC::find($pccId);

        $res['layout']                  = "admin::index";
        $res['current_user']            = PosHelper::getCurrentUser('name');
        $res['outlet_information']      = $this->outlet_code . '.' . $this->outlet_name;
        $res['outlet_code']             = $this->outlet_code;
        $res['period']                  = $masterPCC->period;
        $res['month']                   = MONTHS[$masterPCC->month] ?? null;
        $res['year']                    = $masterPCC->year;
        $res['locations']               = $this->location_rep->all();
        $res['is_exported_file']        = $this->request['is_exported_file'] ?? false;
        $res['master_material_details'] = $material_details;

        if ($res['is_exported_file']) {
            // Disable libxml errors and allow user to fetch error information as needed
            libxml_use_internal_errors(true);
            $extensionFile = PosHelper::get_extension_export_file('excel');
            return Excel::download(new StockOpnameExport($res), "Stockopname{$masterPCC->period}{$extensionFile}");
        }
        return view("inventory_report.stockopname_report", $res);
    }
    
    public function updateStatus($id)
    {
        $this->validate($this->request, [
            "status_id" => 'required|integer',
        ]);
        
        $status_id   = $this->request->status_id;
        $this->stock_opname_rep->update([
            'status_id' => $status_id
        ], $id);
        
        return redirect('admin/inventory/stock-opname');
    }
}
