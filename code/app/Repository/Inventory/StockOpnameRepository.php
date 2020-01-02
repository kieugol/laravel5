<?php

namespace App\Repository\Inventory;

use App\Helpers\FileHelper;
use App\Helpers\PosHelper;
use App\Model\Inventory\{Location, MasterMaterialDetail, MasterPCC, StockOpname, StockOpnameDetail, StockOpnameSummary};
use App\Repository\BaseRepository;
use App\Repository\OrderRepository;
use Illuminate\Support\Facades\DB;

class StockOpnameRepository extends BaseRepository
{
    public function __construct(StockOpname $model)
    {
        parent::__construct($model);
    }

    public function mergeDataForCSV($stockOpnameId)
    {
        $periodData = [];

        $stockOpname       = $this->find($stockOpnameId);
        $stockOpnameDetail = $this->getStockOpnameDetailForCreateCSV($stockOpnameId);
        $totalLocation     = Location::all();
        $mainPeriod        = $stockOpname->master_pcc->period;

        $arrQtyInitial          = array_fill(0, count($totalLocation), 0);
        $totalIdLocationInitial = array_column($totalLocation->toArray(), 'id');

            // Get All data detail based on Month and Year
        $arrayPeriod             = range(PERIOD_PCC_1, $mainPeriod);
        $listStockOpnameByPeriod = $this->getListStockOpnameByPeriod($stockOpname->master_pcc->month, $stockOpname->master_pcc->year, $arrayPeriod);

        if (!$listStockOpnameByPeriod->isEmpty()) {
            foreach ($listStockOpnameByPeriod as $row) {
                $tmpData = $this->getStockOpnameDetailForCreateCSV($row->id);

                foreach ($tmpData as $item) {
                    $isMaterialDetailRecipe = empty($item->stock_opname_detail_id);
                    $group_location_id      = $isMaterialDetailRecipe ? $totalIdLocationInitial : explode(',', $item->group_location_id);
                    $group_quantity_convert = $isMaterialDetailRecipe ? $arrQtyInitial : explode(',', $item->group_quantity_convert);

                    foreach ($group_location_id as $index => $id) {
                        // if location_id is LOCATION_CHILLER_ID and qty_material > 0 then get total ending_inv
                        //$qty = $id == LOCATION_CHILLER_ID &&  $group_quantity_convert[$index] > 0 ? $item->ending_inv : $group_quantity_convert[$index];
                        //$periodData[$row->master_pcc->period][$item->code][$id] = $qty;

                        // Merging material recipe with default quantity_recipe into csv
                        $qty = $isMaterialDetailRecipe && $id == LOCATION_BAR_ID ? $item->quantity_recipe : $group_quantity_convert[$index];
                        $periodData[$row->master_pcc->period][$item->code][$id] = $qty;
                    }
                }
            }
        }

        $csvData = [];
        foreach ($stockOpnameDetail as $row) {
            $csvData[$row->code] = [
                'store_code'    => $stockOpname->store_code,
                'year'          => $stockOpname->master_pcc->year,
                'month'         => $stockOpname->master_pcc->month,
                'material_code' => substr($row->code, 0, -1),
                'group'         => substr($row->code, -1),
            ];
            $materialCode        = $row->code;

            for ($pcc = PERIOD_PCC_1; $pcc <= PERIOD_PCC_3; $pcc++) {
                foreach ($totalLocation as $location) {
                    $locationId = $location->id;
                    $qtyConvert = $periodData[$pcc][$materialCode][$locationId] ?? 0;

                    $csvData[$row->code]["location_{$locationId}_{$pcc}"] = $qtyConvert;
                }
            }
        }

        return $csvData;
    }

    public function getStockOpnameDetailForCreateCSV($stockOpnameId)
    {
        return DB::table(StockOpnameSummary::getTbl())
            ->select([
                StockOpnameDetail::getCol('id AS stock_opname_detail_id'),
                StockOpnameSummary::getCol('material_detail_id'),
                MasterMaterialDetail::getCol('code'),
                DB::raw("GROUP_CONCAT(" . StockOpnameDetail::getCol('location_id') . ") AS group_location_id"),
                DB::raw("GROUP_CONCAT(" . StockOpnameDetail::getCol('quantity_convert') . ") AS group_quantity_convert"),
                StockOpnameSummary::getCol('quantity_material'),
                StockOpnameSummary::getCol('quantity_recipe'),
                StockOpnameSummary::getCol('ending_inv')
            ])
            ->join(MasterMaterialDetail::getTbl(), MasterMaterialDetail::getCol('id'), StockOpnameSummary::getCol('material_detail_id'))
            ->leftJoin(StockOpnameDetail::getTbl(), function ($join) use($stockOpnameId) {
                $join->on(StockOpnameSummary::getCol('material_detail_id'), '=', StockOpnameDetail::getCol('material_detail_id'))
                    ->where(StockOpnameDetail::getCol('stock_opname_id'), $stockOpnameId);
            })
            ->where(StockOpnameSummary::getCol('stock_opname_id'), $stockOpnameId)
            ->orderBy(StockOpnameSummary::getCol('id'), 'ASC')
            ->groupBy(StockOpnameSummary::getCol('material_detail_id'))
            ->get();
    }

    public function getListStockOpnameByPeriod($month, $year, $arrayPeriod)
    {
        $masterPCC = MasterPCC::getTbl();
        $idCol     = MasterPCC::getCol('id');
        $periodCol = MasterPCC::getCol('period');
        $monthCol  = MasterPCC::getCol('month');
        $yearCol   = MasterPCC::getCol('year');
        $PCCIdCol  = StockOpname::getCol('pcc_id');

        $arrayPeriod = implode(',', $arrayPeriod);

        return $this->model
            ->select([
                StockOpname::getCol('id'),
                $PCCIdCol,
                $periodCol,
                $monthCol,
                $yearCol
            ])
            ->join($masterPCC, $idCol, '=', $PCCIdCol)
            ->whereRaw(" $PCCIdCol IN (SELECT($idCol) FROM $masterPCC WHERE $monthCol = $month AND $yearCol = $year) AND $periodCol IN ($arrayPeriod)")
            ->get();
    }

    public function getLocationByArrId($arrLocationId)
    {
        $result = DB::table(Location::getTbl())
            ->select(Location::getCol('id'))
            ->whereIn(Location::getCol('id'), $arrLocationId)
            ->orderBy(Location::getCol('id'), 'ASC')
            ->get();

        return $result;
    }

    public function getPCCReport($pccId)
    {
        DB::statement("CALL sp_view_pcc({$pccId});");

        $detail = DB::table("temp_rpt_pcc")
            ->select("*")
            ->orderBy("type_id")
            ->get();

        return $detail->toArray();
    }

    public function getMCCReport($month, $year)
    {
        DB::statement("CALL sp_view_mcc_by_month_year($month, $year);");

        $detail = DB::table("temp_rpt_mcc")
            ->select("*")
            ->orderBy("type_id")
            ->get();

        return $detail->toArray();
    }

    public function filterPCCData($dataReport, $keyTotalGroupFB, $dateRange)
    {
        $dataDetail       = [];
        $dataType         = [];
        $dataAccount      = [];
        $dataGroup        = [
            GROUP_FOOD       => [],
            GROUP_BEVERAGE   => [],
            $keyTotalGroupFB => []
        ];

        // Initial three fixed groups
        foreach ($dataGroup as $groupId => &$row) {
            $row = ['total_beginning' => 0,'_total_period' => 0, '_total_ending' => 0, '_total_cost_of_sales' => 0];
            foreach ($dateRange as $key => $date) {
                $row["_total_$date"] = 0;
            }
        }

        foreach ($dataReport as $index => &$row) {
            $type                   = $row->type_id;
            $group                  = $row->group_id;
            $account                = $row->account_id;
            $row->dll               = $row->_cost_of_sales > 0 ? round(($row->_total_ending / $row->_cost_of_sales) * 10, 2) : 0;
            $row->explanation       = '';
            $dataDetail[$account][] = $row;

            $dataType[$type]["total_beginning"]               = ($dataType[$type]["total_beginning"] ?? 0) + $row->total; // total beginning by type
            $dataAccount[$group][$account]["total_beginning"] = ($dataAccount[$group][$account]["total_beginning"] ?? 0) + $row->total; // total beginning by account
            $dataGroup[$group]["total_beginning"]             = ($dataGroup[$group]["total_beginning"] ?? 0) + $row->total; // total beginning by type
            foreach ($dateRange as $key => $date) {
                $keyTotal = "_total_" . ($key + 1);

                $dataType[$type]["_total_$date"]               = ($dataType[$type]["_total_$date"] ?? 0) + $row->{$keyTotal};
                $dataGroup[$group]["_total_$date"]             = ($dataGroup[$group]["_total_$date"] ?? 0) + $row->{$keyTotal};
                $dataAccount[$group][$account]["_total_$date"] = ($dataAccount[$group][$account]["_total_$date"] ?? 0) + $row->{$keyTotal};
            }
            // total by date by type
            $dataType[$type]["group_id"]             = $group;
            $dataType[$type]["_total_period"]        = ($dataType[$type]["_total_period"] ?? 0) + $row->_total_period; // total period by type
            $dataType[$type]["_total_cost_of_sales"] = ($dataType[$type]["_total_cost_of_sales"] ?? 0) + $row->_cost_of_sales; // total cost of sale by type
            $dataType[$type]["_total_ending"]        = ($dataType[$type]["_total_ending"] ?? 0) + $row->_total_ending;  // total ending by type
            // total by date by type
            $dataAccount[$group][$account]["account_id"]           = $account;
            $dataAccount[$group][$account]["_total_period"]        = ($dataAccount[$group][$account]["_total_period"] ?? 0) + $row->_total_period; // total period by type
            $dataAccount[$group][$account]["_total_cost_of_sales"] = ($dataAccount[$group][$account]["_total_cost_of_sales"] ?? 0) + $row->_cost_of_sales; // total cost of sale by type
            $dataAccount[$group][$account]["_total_ending"]        = ($dataAccount[$group][$account]["_total_ending"] ?? 0) + $row->_total_ending;  // total ending by type
            // total group
            $dataGroup[$group]["_total_period"]        = ($dataGroup[$group]["_total_period"] ?? 0) + $row->_total_period; // total period by group
            $dataGroup[$group]['_total_cost_of_sales'] = ($dataGroup[$group]['_total_cost_of_sales'] ?? 0) + $row->_cost_of_sales; // total cost of sale by group
            $dataGroup[$group]['_total_ending']        = ($dataGroup[$group]['_total_ending'] ?? 0) + $row->_total_ending; // total cost of sale by group
            if ($group == GROUP_FOOD || $group == GROUP_BEVERAGE) {
                $dataGroup[$keyTotalGroupFB]['_total_cost_of_sales'] += $row->_cost_of_sales;
                $dataGroup[$keyTotalGroupFB]['_total_ending'] += $row->_total_ending;
            }
        }

        return [
            'data_group'   => $dataGroup,
            'data_type'    => $dataType,
            'data_account' => $dataAccount,
            'data_detail'  => json_decode(json_encode($dataDetail), true),
        ];
    }

    public function filterMCCData($dataReport, $keyTotalGroupFB, $pccRange, $periodicTurnOver)
    {
        $dataDetail       = [];
        $dataType         = [];
        $dataAccount      = [];
        $dataGroup        = [
            GROUP_FOOD       => [],
            GROUP_BEVERAGE   => [],
            $keyTotalGroupFB => []
        ];

        // Initial three fixed groups
        foreach ($dataGroup as $groupId => &$row) {
            $row = ['total_beginning' => 0,'total_end_inv' => 0, 'total_in_total' => 0, 'total_cost_of_sales' => 0];
            foreach ($pccRange as $key => $pcc) {
                $row["{$pcc}_total"] = 0;
            }
        }

        foreach ($dataReport as $index => $item) {
            $type                   = $item->type_id;
            $group                  = $item->group_id;
            $account                = $item->account_id;
            $item->dll               = $item->cost_of_sales > 0 ? round(($item->total_in_total / $item->cost_of_sales) * $periodicTurnOver, 2) : 0;
            $item->explanation       = '';
            $dataDetail[$account][] = $item;

            $dataType[$type]["total_beginning"]               = ($dataType[$type]["total_beginning"] ?? 0) + $item->beginning_total; // total beginning by type
            $dataAccount[$group][$account]["total_beginning"] = ($dataAccount[$group][$account]["total_beginning"] ?? 0) + $item->beginning_total; // total beginning by account
            $dataGroup[$group]["total_beginning"]             = ($dataGroup[$group]["total_beginning"] ?? 0) + $item->beginning_total; // total beginning by type
            foreach ($pccRange as $pcc) {
                $keyPCC = "{$pcc}_total";

                $dataType[$type]["{$pcc}_total"]        = ($dataType[$type][$keyPCC] ?? 0) + $item->{$keyPCC};
                $dataGroup[$group][$keyPCC]             = ($dataGroup[$group][$keyPCC] ?? 0) + $item->{$keyPCC};
                $dataAccount[$group][$account][$keyPCC] = ($dataAccount[$group][$account][$keyPCC] ?? 0) + $item->{$keyPCC};
            }
            // total by date by type
            $dataType[$type]["group_id"]            = $group;
            $dataType[$type]["total_in_total"]      = ($dataType[$type]["total_in_total"] ?? 0) + $item->total_in_total; // total in total by type
            $dataType[$type]["total_cost_of_sales"] = ($dataType[$type]["total_cost_of_sales"] ?? 0) + $item->cost_of_sales; // total cost of sale by type
            $dataType[$type]["total_end_inv"]       = ($dataType[$type]["total_end_inv"] ?? 0) + $item->total_ending;  // total ending by type
            // total by date by type
            $dataAccount[$group][$account]["account_id"]          = $account;
            $dataAccount[$group][$account]["total_in_total"]      = ($dataAccount[$group][$account]["total_in_total"] ?? 0) + $item->total_in_total; // total in total by type
            $dataAccount[$group][$account]["total_cost_of_sales"] = ($dataAccount[$group][$account]["total_cost_of_sales"] ?? 0) + $item->cost_of_sales; // total cost of sale by type
            $dataAccount[$group][$account]["total_end_inv"]       = ($dataAccount[$group][$account]["total_end_inv"] ?? 0) + $item->total_ending;  // total ending by type
            // total group
            $dataGroup[$group]["total_in_total"]      = ($dataGroup[$group]["total_in_total"] ?? 0) + $item->total_in_total; // total in total by group
            $dataGroup[$group]['total_cost_of_sales'] = ($dataGroup[$group]['total_cost_of_sales'] ?? 0) + $item->cost_of_sales; // total cost of sale by group
            $dataGroup[$group]['total_end_inv']       = ($dataGroup[$group]['total_end_inv'] ?? 0) + $item->total_ending; // total cost of sale by group
            if ($group == GROUP_FOOD || $group == GROUP_BEVERAGE) {
                $dataGroup[$keyTotalGroupFB]['total_cost_of_sales'] += $item->cost_of_sales;
                $dataGroup[$keyTotalGroupFB]['total_end_inv'] += $item->total_ending;
            }
        }

        return [
            'data_group'   => $dataGroup,
            'data_type'    => $dataType,
            'data_account' => $dataAccount,
            'data_detail'  => json_decode(json_encode($dataDetail), true),
        ];
    }

    public function generateSaleDataCSV($pccId, $outletCode, OrderRepository $orderRep,  MasterPCCRepository $masterPCCRep)
    {
        //$currentYear  = date("Y");
        //$currentMonth = date("m");
        $pccDetail    = MasterPCC::find($pccId);
        $year         = $pccDetail->year;
        $month        = str_pad($pccDetail->month, 2, '0', STR_PAD_LEFT);
        $period       = $pccDetail->period;

        // Do not generate different period sale
        /*if (intval($currentYear) != intval($year) || intval($currentMonth) != intval($month)) {
            return false;
        }*/

        $periodPCC1   = $masterPCCRep->getPeriodByPcc($month, $year, PERIOD_PCC_1);
        $periodPCC2   = $period == PERIOD_PCC_2 || $period == PERIOD_PCC_3 ? $masterPCCRep->getPeriodByPcc($month, $year, PERIOD_PCC_2) : [];
        $periodPCC3   = $period == PERIOD_PCC_3 ? $masterPCCRep->getPeriodByPcc($month, $year, PERIOD_PCC_3) : [];
        $saleDataPCC1 = $periodPCC1 ? $orderRep->calculateSaleFoodAndBeverage($periodPCC1, 'total_sale') : [];
        $saleDataPCC2 = $periodPCC2 ? $orderRep->calculateSaleFoodAndBeverage($periodPCC2, 'total_sale') : [];
        $saleDataPCC3 = $periodPCC3 ? $orderRep->calculateSaleFoodAndBeverage($periodPCC3, 'total_sale') : [];

        $saleData[] = [
            'outlet'                   => $outletCode,
            'year'                     => $year,
            'month'                    => $month,
            'gross_food_pcc1'          => $saleDataPCC1[GROUP_FOOD] ?? 0,
            'gross_beverage_pcc1'      => $saleDataPCC1[GROUP_BEVERAGE] ?? 0,
            'qty_catering_pcc1'        => 0,
            'oc_company_food_pcc1'     => 0,
            'oc_company_beverage_pcc1' => 0,
            'oc_outlet_1'              => 0,
            'oc_outlet_2'              => 0,
            'gross_food_pcc2'          => $saleDataPCC2[GROUP_FOOD] ?? 0,
            'gross_beverage_pcc2'      => $saleDataPCC2[GROUP_BEVERAGE] ?? 0,
            'qty_catering_pcc2'        => 0,
            'oc_company_food_pcc2'     => 0,
            'oc_company_beverage_pcc2' => 0,
            'oc_outlet_3'              => 0,
            'oc_outlet_4'              => 0,
            'gross_food_pcc3'          => $saleDataPCC3[GROUP_FOOD] ?? 0,
            'gross_beverage_pcc3'      => $saleDataPCC3[GROUP_BEVERAGE] ?? 0,
            'qty_catering_pcc3'        => 0,
            'oc_company_food_pcc3'     => 0,
            'oc_company_beverage_pcc3' => 0,
            'oc_outlet_5'              => 0,
            'oc_outlet_6'              => 0,
        ];

        $subPath        = date("Y/m/d");
        $result         = [
            INVENTORY_DIR_DAILY_SHEET_CSV          => '',
            INVENTORY_DIR_DAILY_SHEET_DOWNLOAD_CSV => '',
        ];
        $destinationDir = [
            INVENTORY_DIR_DAILY_SHEET_CSV,
            INVENTORY_DIR_DAILY_SHEET_DOWNLOAD_CSV,
        ];

        foreach ($destinationDir as $pathDir) {
            $saleDataNameFile = $outletCode . CSV_NAME_SALE_DATA;
            $flagFolder       = FileHelper::create_sub_folder($pathDir, $subPath);
            $pathFile         = $flagFolder . "/" . $saleDataNameFile;

            if (file_exists($pathFile)) {
                unlink($pathFile);
            }

            $result[$pathDir] = PosHelper::generateCSV($saleData, $flagFolder, $saleDataNameFile);
        }

        return $result;
    }
    
    
    public function getByPeriod($from_date, $to_date) {
        return $this->model
            ->whereRaw("updated_date >= '".$from_date."'")
            ->whereRaw("updated_date <= '".$to_date."'")
            ->get();
    }
    
    public function filterUOMReportForCreate($material_detail, &$material_detail_filter, &$recipe_data, $location, $uom_detail, $dataUsageMaterial, $dataUOMConversation, $isUseIndexLocation = true)
    {
        foreach ($material_detail as $index => $row) {
            foreach ($location as $item) {
                $row['recipe_qty'] = 0;
                $arrData = [
                    'id'                       => $item->id,
                    'is_display'               => $item->is_display,
                    'quantity_supplier_uom'    => 0,
                    'supplier_uom_description' => $row['supplier_uom_description'],
                    'quantity_outlet_uom'      => 0,
                    'report_uom_description'   => $row['report_uom_description'],
                ];
                if ($isUseIndexLocation) {
                    $row['locations'][$item->id] = $arrData;
                } else {
                    $row['locations'][] = $arrData;
                }
            }
            // Conversion to report uom
            $final_conversion_rate = 1;
            $arr_uoms              = [
                [
                    'key'   => $row['supplier_uom_id'],
                    'value' => $row['smaller_uom_detail_id']
                ],
                [
                    'key'   => $row['smaller_uom_id'],
                    'value' => $row['outlet_uom_detail_id']
                ],
                [
                    'key'   => $row['outlet_uom_id'],
                    'value' => 1
                ],
            ];
            $condition_uom_id = $row['report_uom_id'];
            foreach ($arr_uoms as $arr_uom) {
                if ($condition_uom_id != $arr_uom['key']) {
                    $final_conversion_rate *= $uom_detail[$arr_uom['value']]->conversion_rate;
                } else {
                    break;
                }
            }
            // Conversion from supplier to recipe
            $final_recipe_conversion_rate = 1;
            $arr_recipe_uoms              = [
                [
                    'key'   => $row['smaller_uom_id'],
                    'value' => $row['smaller_uom_detail_id']
                ],
                [
                    'key'   => $row['outlet_uom_id'],
                    'value' => $row['outlet_uom_detail_id']
                ],
                [
                    'key'   => $row['outlet_uom_id'],
                    'value' => $row['recipe_uom_detail_id']
                ],
            ];
            $condition_recipe_uom_id = $row['supplier_uom_id'];
            foreach ($arr_recipe_uoms as $arr_recipe_uom) {
                if ($condition_recipe_uom_id != $condition_recipe_uom_id['key']) {
                    $final_recipe_conversion_rate *= $uom_detail[$arr_recipe_uom['value']]->conversion_rate;
                } else {
                    break;
                }
            }
            $row['final_conversion_rate'] = $final_conversion_rate;
            $row['total_usage_material']  = $dataUsageMaterial[$row['material_id']] ?? 0;
            $row['contains']              = '1 ' . $row['supplier_uom_description'] . ' = ' . $final_recipe_conversion_rate . ' ' . $row['recipe_uom_description'];
            // Transform for total_available
            $arr_pcc_id                = explode(',', $row['master_pcc_ids']);
            $arr_total_available       = explode(',', $row['total_available']);
            $arr_total_available_final = [];
            foreach ($arr_pcc_id as $index_pcc => $item_pcc) {
                $arr_total_available_final[$item_pcc] = $arr_total_available[$index_pcc] ?? 0;
            }
            $row['arr_total_available_final']                   = $arr_total_available_final;
            // Append conversation to calculate net weight
            $row['conversation_net_weight'] = $dataUOMConversation[$row['material_detail_id']] ?? 1;
            $material_detail_filter[$row['material_detail_id']] = $row;
        }
        
        if (!empty($recipe_data)) {
            foreach ($recipe_data as &$row) {
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
                $row['total_usage_material'] = $dataUsageMaterial[$row['material_id']] ?? 0;
                // Append conversation to calculate net weight
                $row['conversation_net_weight'] = $dataUOMConversation[$row['material_detail_id']] ?? 1;
            }
        }
    }
    
    public function filterUOMReportForUpdate($material_detail, &$material_detail_filter, &$recipe_data_master, $uom_detail, $dataUsageMaterial, $dataUOMConversation)
    {
        foreach ($material_detail as $index => $row) {
            $group_location_id           = explode(',', $row['group_location_id']);
            $group_location_display      = explode(',', $row['group_location_display']);
            $group_quantity_supplier_uom = explode(',', $row['group_quantity_supplier_uom']);
            $group_quantity_outlet_uom   = explode(',', $row['group_quantity_outlet_uom']);
            foreach ($group_location_id as $key => $location_id) {
                $row['recipe_qty']              = 0;
                $row['locations'][$location_id] = [
                    'id'                       => $location_id,
                    'is_display'               => $group_location_display[$key] ?? 0,
                    'quantity_supplier_uom'    => floatval($group_quantity_supplier_uom[$key] ?? 0),
                    'supplier_uom_description' => $row['supplier_uom_description'],
                    'quantity_outlet_uom'      => floatval($group_quantity_outlet_uom[$key] ?? 0),
                    'report_uom_description'   => $row['report_uom_description']
                ];
            }
            ksort($row['locations']);
            // Conversion to report uom
            $final_conversion_rate = 1;
            $arr_uoms              = [
                [
                    'key'   => $row['supplier_uom_id'],
                    'value' => $row['smaller_uom_detail_id']
                ],
                [
                    'key'   => $row['smaller_uom_id'],
                    'value' => $row['outlet_uom_detail_id']
                ],
                [
                    'key'   => $row['outlet_uom_id'],
                    'value' => 1
                ],
            ];
            $condition_uom_id      = $row['report_uom_id'];
            foreach ($arr_uoms as $arr_uom) {
                if ($condition_uom_id != $arr_uom['key']) {
                    $final_conversion_rate *= $uom_detail[$arr_uom['value']]->conversion_rate;
                } else {
                    break;
                }
            }
            $row['final_conversion_rate'] = $final_conversion_rate;
            $row['total_usage_material']  = $dataUsageMaterial[$row['material_id']] ?? 0;
            // Transform for total_available
            $arr_pcc_id                = explode(',', $row['master_pcc_ids']);
            $arr_total_available       = explode(',', $row['total_available']);
            $arr_total_available_final = [];
            foreach ($arr_pcc_id as $index_pcc => $item_pcc) {
                $arr_total_available_final[$item_pcc] = $arr_total_available[$index_pcc];
            }
            $row['arr_total_available_final']                   = $arr_total_available_final;
            // Append conversation to calculate net weight
            $row['conversation_net_weight'] = $dataUOMConversation[$row['material_detail_id']] ?? 1;
            
            $material_detail_filter[$row['material_detail_id']] = $row;
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
            $row['total_usage_material'] = $dataUsageMaterial[$row['material_id']] ?? 0;
            // Append conversation to calculate net weight
            $row['conversation_net_weight'] = $dataUOMConversation[$row['material_detail_id']] ?? 1;
        }
    }
}
