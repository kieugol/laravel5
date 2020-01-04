<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Admin\Controllers\BaseController;
use App\Helpers\ConfigHelp;
use App\Helpers\FileHelper;
use App\Helpers\PosHelper;
use App\Libraries\Api;
use App\Repository\ConfigRepository;
use App\Repository\Inventory\LocationRepository;
use App\Repository\Inventory\MasterMaterialDetailRepository;
use App\Repository\Inventory\MasterPCCRepository;
use App\Repository\Inventory\MasterRecipeRepository;
use App\Repository\Inventory\MasterUomDetailRepository;
use App\Repository\Inventory\StockOpnameRecipeRepository;
use App\Repository\Inventory\MasterUomRepository;
use App\Repository\Inventory\StockOpnameDetailRepository;
use App\Repository\Inventory\StockOpnameRepository;
use App\Repository\Inventory\StockOpnameSummaryRepository;
use App\Repository\Inventory\MasterMaterialUsageRepository;
use App\Repository\OrderRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends BaseController
{
    private $master_pcc_rep;
    private $master_uom_rep;
    private $master_uom_detail_rep;
    private $location_rep;
    private $config_rep;
    private $master_material_detail_rep;
    private $stock_opname_rep;
    private $stock_opname_detail_rep;
    private $stock_opname_recipe_rep;
    private $master_recipe_rep;
    private $stock_opname_summary_rep;
    private $master_material_usage_rep;
    private $order_rep;
    private $outlet_code;

    public function __construct(
        MasterPCCRepository $master_pcc_rep,
        MasterUomRepository $master_uom_rep,
        MasterUomDetailRepository $master_uom_detail_rep,
        LocationRepository $location_rep,
        MasterMaterialDetailRepository $master_material_detail_rep,
        ConfigRepository $config_rep,
        StockOpnameDetailRepository $stock_opname_detail_rep,
        StockOpnameRepository $stock_opname_rep,
        StockOpnameRecipeRepository $stock_opname_recipe_rep,
        MasterRecipeRepository $master_recipe_repo,
        StockOpnameSummaryRepository $stock_opname_summary_rep,
        MasterMaterialUsageRepository $master_material_usage_rep,
        OrderRepository $order_rep
    )
    {
        parent::__construct();
        $this->master_pcc_rep             = $master_pcc_rep;
        $this->master_uom_rep             = $master_uom_rep;
        $this->master_uom_detail_rep      = $master_uom_detail_rep;
        $this->location_rep               = $location_rep;
        $this->master_material_detail_rep = $master_material_detail_rep;
        $this->config_rep                 = $config_rep;
        $this->stock_opname_rep           = $stock_opname_rep;
        $this->stock_opname_detail_rep    = $stock_opname_detail_rep;
        $this->stock_opname_recipe_rep    = $stock_opname_recipe_rep;
        $this->master_recipe_rep          = $master_recipe_repo;
        $this->stock_opname_summary_rep   = $stock_opname_summary_rep;
        $this->master_material_usage_rep  = $master_material_usage_rep;
        $this->order_rep                  = $order_rep;
        $this->outlet_code                = ConfigHelp::get("outlet_code");
    }

    public function getList()
    {
        $option  = $this->request->all();
        $filters = [];
        if (isset($option['pcc_id'])) {
            $filters['pcc_id'] = $option['pcc_id'];
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

        $data     = $this->stock_opname_rep->getDataTableApi($filters, $searches, $sort, $period);
        $response = [
            'message' => '',
            'data'    => $data,
        ];
        foreach ($data['items'] as &$item) {
            $item->pcc_name = $item->master_pcc->name;
            $item->month    = $item->master_pcc->month;
            $item->year     = $item->master_pcc->year;
            unset($item->master_pcc);
        }

        return Api::response($response);
    }

    public function getAll()
    {
        $month = date('m');
        $year  = date('Y');

        $data     = [
            'month' => $month,
            'year'  => $year,
            'pccs'  => $this->master_pcc_rep->getAllByPeriod($month, $year)
        ];
        $response = [
            'message' => '',
            'data'    => $data,
        ];

        return Api::response($response);
    }

    public function create()
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

        $stock_opname = [
            'store_code'          => $store_code,
            'pcc_id'              => $pcc_id,
            'status_id'           => STOCK_OPNAME_STATUS_DRAFT,
            'created_by'          => $created_by
        ];

        $stt_code = Response::HTTP_OK;
        $response = ['message' => 'Created Successfully.'];

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
            
                    $qtyConvert = $location['id'] == LOCATION_BAR_ID  && isset($stock_opname_summary_data[$item['material_detail_id']])
                        ? $stock_opname_summary_data[$item['material_detail_id']]['quantity_recipe'] :  $qtyConvertOutlet + $location['quantity_outlet_uom'];
            
                    $data_create_detail[] = [
                        'stock_opname_id'       => $id,
                        'material_detail_id'    => $item['material_detail_id'],
                        'quantity_supplier_uom' => $location['quantity_supplier_uom'],
                        'quantity_outlet_uom'   => $location['quantity_outlet_uom'],
                        'quantity_convert'      => $qtyConvert,
                        'location_id'           => $location['id'],
                        'created_by'            => $created_by
                    ];
                    $totalLocationQty  += $location['quantity_supplier_uom'] + $location['quantity_outlet_uom'];
                }
                // Saving into collection
                $key                                    = $totalLocationQty == 0 ? 'no_qty' : 'has_qty';
                $material_detail_ids_collection[$key][] = $item['material_detail_id'];
                $totalQty                               += $totalLocationQty;
            }
            
            if ($totalQty <= 0 && $is_sent_ftp) {
                abort(Response::HTTP_BAD_REQUEST, 'Material is invalid');
            }

            $this->stock_opname_detail_rep->insertMultiple($data_create_detail);
            $this->stock_opname_recipe_rep->insertMultiple($stock_opname_recipe_data);
            $this->stock_opname_summary_rep->insertMultiple($stock_opname_summary_data);
            // Re-update material did not put qty
            $this->master_material_detail_rep->UpdateByIds(['has_transaction' => 0], $material_detail_ids_no_qty);

            if ($is_sent_ftp) {
                // Save csv
                $file = $this->createCSVFile($id);
                $this->stock_opname_rep->generateSaleDataCSV($pcc_id, $this->outlet_code, $this->order_rep, $this->master_pcc_rep);
    
                $this->stock_opname_rep->update([
                    'path'        => $file['path'],
                    'file_name'   => $file['file_name'],
                    'status_id'   => STOCK_OPNAME_STATUS_CONFIRMED,
                    'is_sent_ftp' => STATUS_ACTIVE
                ], $id);
                $response['message'] = 'Created and Sent FTP Successfully.';
            }
            DB::commit();
            $response['data'] = $this->stock_opname_rep->find($id);
        } catch (\Exception $ex) {
            DB::rollback();
            $stt_code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response = ['message' => 'Error System'];
        }

        return Api::response($response, $stt_code);
    }

    public function getDetail($id)
    {
        $stock_opname         = $this->stock_opname_rep->find($id);
        $stock_opname_details = $this->stock_opname_detail_rep->getDetailByStockOpnameIdForUpdate($id);
        $stock_opname_recipe  = $this->stock_opname_recipe_rep->getMappingRecipeDataByStockOpnameId($id);
        $recipe_data_master   = $this->master_recipe_rep->getForCreateStockOpname($stock_opname_recipe);
        $stock_opname_summary = $this->stock_opname_summary_rep->parseStockSummaryData($stock_opname->stock_opname_summary);
        $uom_detail_query     = $this->master_uom_detail_rep->all();
        $uom_detail           = object_key_column($uom_detail_query, 'id');
        $arrayMaterialId      = array_unique(array_column($stock_opname_details->toArray(), 'material_id') + array_column($recipe_data_master, 'material_id'));
        $dataUsageMaterial    = $this->master_material_usage_rep->getTotalUsageByMaterialIds($arrayMaterialId);
    
        $arr_location = [];
        foreach ($stock_opname_details as &$item) {
            $locations            = [];
            $group_locations      = [];
            $arr_qty_outlet_uom   = explode(',', $item->group_quantity_outlet_uom);
            $arr_qty_supplier_uom = explode(',', $item->group_quantity_supplier_uom);
            $arr_location         = explode(',', $item->group_location_id);
            foreach ($arr_location as $key => $id) {
                if (!in_array($id, $group_locations)) {
                    $group_locations[] = $id;
                    $locations[] = [
                        'id'                    => $id,
                        'quantity_outlet_uom'   => $arr_qty_outlet_uom[$key],
                        'quantity_supplier_uom' => $arr_qty_supplier_uom[$key]
                    ];
                }
            }
            $item->locations = $locations;
            // Conversion to report uom
            $final_conversion_rate = 1;
            $arr_uoms              = [
                [
                    'key'   => $item->supplier_uom_id,
                    'value' => $item->smaller_uom_detail_id
                ],
                [
                    'key'   => $item->smaller_uom_id,
                    'value' => $item->outlet_uom_detail_id
                ],
                [
                    'key'   => $item->outlet_uom_id,
                    'value' => 1
                ],
            ];

            $condition_uom_id = $item->report_uom_id;
            foreach ($arr_uoms as $arr_uom) {
                if ($condition_uom_id != $arr_uom['key']) {
                    $final_conversion_rate *= $uom_detail[$arr_uom['value']]->conversion_rate;
                } else {
                    break;
                }

            }
            $item->final_conversion_rate = $final_conversion_rate;
            $item->total_usage_material  = $dataUsageMaterial[$item->material_id] ?? 0;

            unset($item->group_quantity_outlet_uom);
            unset($item->group_quantity_supplier_uom);
            unset($item->group_location_id);
        }

        foreach ($recipe_data_master as &$row) {
            foreach ($row['material_details'] as &$row_material_detail) {
                // Conversion to report uom
                $final_conversion_rate = 1;
                $arr_uoms              = [
                    [
                        'key'   => $row_material_detail['outlet_uom_id'],
                        'value' => $row_material_detail['recipe_uom_detail_id']
                    ],
                    [
                        'key'   => $row_material_detail['smaller_uom_id'],
                        'value' => $row_material_detail['outlet_uom_detail_id']
                    ],
                    [
                        'key'   => $row_material_detail['supplier_uom_id'],
                        'value' => $row_material_detail['smaller_uom_detail_id']
                    ],
                ];

                $condition_uom_id = $row_material_detail['report_uom_id'];
                foreach ($arr_uoms as $arr_uom) {
                    $final_conversion_rate *= $uom_detail[$arr_uom['value']]->conversion_rate;
                    if ($condition_uom_id == $arr_uom['key']) {
                        break;
                    }

                }
                $row_material_detail['final_conversion_rate'] = $final_conversion_rate;
            }
            $row['total_usage_material']  = $dataUsageMaterial[$row['material_id']] ?? 0;
        }
    
        $stock_opname_details = $stock_opname_details->toArray();
        
        if ($stock_opname->status_id == STOCK_OPNAME_STATUS_DRAFT) {
            $locationsMaster =  $this->location_rep->getByArrayId($arr_location);
            // Get what material was created after stock have been created before
            $recipe_data_master_tmp = $recipe_data_master ? [array_first($recipe_data_master)] : [];
            $material_detail_tmp    = $this->master_material_detail_rep->getDataAfterReceiveForStockOptNameUpdate($arrayMaterialId);
            $arrayMaterialIdTmp     = array_unique(array_column($material_detail_tmp, 'material_id') + array_column($recipe_data_master_tmp, 'material_id'));
            $dataUsageMaterialTmp   = $this->master_material_usage_rep->getTotalUsageByMaterialIds($arrayMaterialIdTmp);
            $this->stock_opname_rep->filterUOMReportForCreate($material_detail_tmp, $material_detail_filter_tmp, $recipe_data_master_tmp, $locationsMaster, $uom_detail, $dataUsageMaterialTmp, false);
            // Merge master material detail
            $stock_opname_details += $material_detail_filter_tmp;
        }
        
        $stock_opname_details = array_values($stock_opname_details);

        $pccs = $this->master_pcc_rep->getAllByPeriod($stock_opname->master_pcc->month, $stock_opname->master_pcc->year);

        $data     = [
            'id'                   => $stock_opname->id,
            'store_code'           => $stock_opname->store_code,
            'pcc_id'               => $stock_opname->pcc_id,
            'path'                 => $stock_opname->path,
            'file_name'            => $stock_opname->file_name,
            'is_sent_ftp'          => $stock_opname->is_sent_ftp,
            'status_id'            => $stock_opname->status_id,
            'is_active'            => $stock_opname->is_active,
            'created_date'         => $stock_opname->created_date,
            'updated_date'         => $stock_opname->updated_date,
            'created_by'           => $stock_opname->created_by,
            'month'                => $stock_opname->master_pcc->month,
            'year'                 => $stock_opname->master_pcc->year,
            'pccs'                 => $pccs,
            'stock_opname_details' => $stock_opname_details,
            'stock_opname_recipe'  => $recipe_data_master,
            'stock_opname_summary' => $stock_opname_summary,
        ];
        $response = [
            'message' => '',
            'data'    => $data
        ];
        return Api::response($response);
    }

    public function update($id)
    {
        $data                     = $this->request->all();
        $is_sent_ftp              = $data['is_sent_ftp'];
        $stock_opname_details     = $data['stock_opname_details'];
        $stock_opname_summary     = $data['stock_opname_summary'];
        $created_by               = PosHelper::getCurrentUser('id');
        $stock_opname             = $this->stock_opname_rep->find($id);
        $stock_opname_recipe_data = $this->stock_opname_recipe_rep->filterDataCreate($data['stock_opname_recipe']);

        // Attach stock_opname_id
        foreach ($stock_opname_recipe_data as &$row) {
            $row['stock_opname_id'] = $id;
        }

        $stock_opname_summary_data      = [];
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
            
                $qtyConvert = $location['id'] == LOCATION_BAR_ID  && isset($stock_opname_summary_data[$item['material_detail_id']])
                    ? $stock_opname_summary_data[$item['material_detail_id']]['quantity_recipe'] :  $qtyConvertOutlet + $location['quantity_outlet_uom'];
            
                $data_create_detail[] = [
                    'stock_opname_id'       => $id,
                    'material_detail_id'    => $item['material_detail_id'],
                    'quantity_supplier_uom' => $location['quantity_supplier_uom'],
                    'quantity_outlet_uom'   => $location['quantity_outlet_uom'],
                    'quantity_convert'      => $qtyConvert,
                    'location_id'           => $location['id'],
                    'created_by'            => $created_by
                ];
                $totalLocationQty  += $location['quantity_supplier_uom'] + $location['quantity_outlet_uom'];
            }
            // Saving into collection
            $key                                    = $totalLocationQty == 0 ? 'no_qty' : 'has_qty';
            $material_detail_ids_collection[$key][] = $item['material_detail_id'];
            $totalQty                               += $totalLocationQty;
        }

        if ($totalQty <= 0 && $is_sent_ftp) {
            abort(Response::HTTP_BAD_REQUEST, 'Material is invalid');
        }

        $stt_code = Response::HTTP_OK;
        $response = ['message' => 'Updated Successfully.'];

        DB::beginTransaction();
        try {
            $this->stock_opname_detail_rep->deleteByStockOpnameId($id);
            $this->stock_opname_recipe_rep->deleteByStockOpnameId($id);
            $this->stock_opname_summary_rep->deleteByStockOpnameId($id);

            $this->stock_opname_detail_rep->insertMultiple($data_create_detail);
            $this->stock_opname_recipe_rep->insertMultiple($stock_opname_recipe_data);
            $this->stock_opname_summary_rep->insertMultiple($stock_opname_summary_data);

            // Re-update material putted qty or not
            $this->master_material_detail_rep->UpdateByIds(['has_transaction' => 0],  $material_detail_ids_collection['no_qty']);
            $this->master_material_detail_rep->UpdateByIds(['has_transaction' => 1],  $material_detail_ids_collection['has_qty']);


            if ($is_sent_ftp) {
                // Save csv
                $file = $this->createCSVFile($id);
                $this->stock_opname_rep->generateSaleDataCSV($stock_opname->pcc_id, $this->outlet_code, $this->order_rep, $this->master_pcc_rep);
                $this->stock_opname_rep->update([
                    'path'        => $file['path'],
                    'file_name'   => $file['file_name'],
                    'is_sent_ftp' => STATUS_ACTIVE,
                    'status_id'   => STOCK_OPNAME_STATUS_CONFIRMED,
                    'updated_by'  => $created_by
                ], $id);
                $response['message'] = 'Updated and Sent FTP Successfully.';
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $stt_code = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response = ['message' => 'Error System'];
        }
        DB::commit();

        $response['data'] = $this->stock_opname_rep->find($id);

        return Api::response($response, $stt_code);
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

    public function getTotalUsageByGroupByPeriod()
    {
        // Get what recipe already created with material detail id before
        $stock_opname_recipe   = $this->stock_opname_recipe_rep->getMappingRecipeDataByStockOpnameId(0);
        $material_detail       = $this->master_material_detail_rep->getAllForCreateStockOptName()->toArray();
        $recipe_data           = $this->master_recipe_rep->getForCreateStockOpname($stock_opname_recipe);
        $arrayMaterialId       = array_unique(array_column($material_detail, 'material_id') + array_column($recipe_data, 'material_id'));
        $dataUsageMaterial     = $this->master_material_usage_rep->getTotalUsageByMaterialIdsAndGroupByPeriod($arrayMaterialId)->toArray();
        $query_material_detail       = $this->master_material_detail_rep->getMaterialDetailForTotalUsage();
        $uom_detail_query      = $this->master_uom_detail_rep->all();
        $uom_detail            = object_key_column($uom_detail_query, 'id');
        $result                = [];
        $all_material_detail = [];
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
            $value_convert = $all_material_detail[$rowUsage['material_id']] ?? 1;
            $result[] = [
                'key' => $rowUsage['material_id'] . '_' . $rowUsage['master_pcc_id'],
                'value' => $rowUsage['total_usage']/$value_convert
            ];
        }

        return $result;
    }

}
