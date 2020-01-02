<?php

namespace App\Admin\Controllers;

use App\Repository\{OrderDetailRepository, UserRepository};
use Illuminate\Http\Request;
use App\Helpers\{PosHelper, ConfigHelp};
use  App\Widget\DateRange;
use  Maatwebsite\Excel\Facades\Excel;
use App\Admin\Exports\{
    SalesMixByMenuExport
};

class SaleReportController extends BaseController
{
    
    private $orderDetailRep = null;
    private $userRep = null;
    private $widget = null;
    private $outletInformation = null;
    
    
    public function __construct(OrderDetailRepository $orderDetailRep, UserRepository $userRep)
    {
        parent::__construct();
        $this->orderDetailRep = $orderDetailRep;
        $this->userRep        = $userRep;
        $this->widget         = new DateRange("fromDate", "toDate");
        $this->widget->setFromDate(date("Y-m-d"))->setToDate(date("Y-m-d"));
        $this->outletInformation = ConfigHelp::get("outlet_code") . '.' . ConfigHelp::get("outlet_name");
        
        // Disable libxml errors and allow user to fetch error information as needed
        libxml_use_internal_errors(true);
    }
    
    public function saleMixByMenu(Request $request)
    {
        $param = $this->getParamFilter($request);
        $data  = $this->orderDetailRep->getReportSaleMixMenu($param);
        
        $limitMaxHour        = 2;
        $limitMinHour        = 7;
        $defaultStartHour    = 10;
        $defaultEndHour      = 23;
        $rangeHourNewDay     = ['00', '01', '02'];
        $timeRange           = [];
        $menuSale            = [];
        $hourRange           = "%s:00-%s:59";
        $totalMenuSaleBySize = [];
        $dataTmp             = [];
        
        for ($hour = $defaultStartHour; $hour <= $defaultEndHour; $hour++) {
            $timeRange[$hour] = sprintf($hourRange, $hour, $hour);
        }
        
        // Additional 00:00->02:00 on new day
        foreach ($rangeHourNewDay as $hour) {
            $timeRange[intval($hour)] = sprintf($hourRange, $hour, $hour);
        }
        
        // Filter pizza is top
        foreach ($data as $index => $val) {
            if ($val['category_id'] != CATEGORY_PIZZA) {
                $dataTmp[] = $val;
                unset($data[$index]);
            }
        }
        // Merge menu with category is not pizza again
        $data = array_values($data);
        $data = array_merge($data, $dataTmp);
    
        foreach ($data as $index => $row) {
            $type         = $row['addon_name'];
            $categoryName = empty($row['category_name']) ? $row['category_name_tmp'] : $row['category_name'];
            $item         = empty($type) ? $categoryName : $row['variant_name'];
            
            $hour      = intval(date("H", strtotime($row['created_date'])));
            // Grant default 2:00 hour if ordered date is over 2:00 and less than 7:00 on new day
            $hour      = !array_key_exists($hour, $timeRange) && $hour > $limitMaxHour && $hour < $limitMinHour ? $limitMaxHour: $hour;
            // Grant default 10:00 hour if ordered date started before 10:00 and greater than 7:00
            $hour      = !array_key_exists($hour, $timeRange) && $hour >= $limitMinHour ? $defaultStartHour: $hour;
            $orderType = $row['order_type_id'];
            $qty       = $row['quantity'];
            
            if (!isset($menuSale[$item])) {
                $menuSale[$item]            = [];
                $totalMenuSaleBySize[$item] = [];
            }
            
            if (!isset($menuSale[$item][$type])) {
                $menuSale[$item][$type]            = [];
                $totalMenuSaleBySize[$item][$type] = 0;
            }
            
            $arrName = [$row['variant_name'], $row['addon_name'], $row['menu_name']];
            
            if (empty($row['variant_name']) && empty($row['addon_name'])) {
                $menuName = trim(implode(" ", $arrName));
            } else {
                $menuName = "{$row['variant_name']} / {$row['addon_name']} {$row['menu_name']}";
            }
            
            // Get menu name not having variant
            if (empty($menuName)) {
                $menuName = $row['menu_name_ordered'];
            }
            
            if (!isset($menuSale[$item][$type][$menuName])) {
                $menuSale[$item][$type][$menuName] = [
                    'total' => 0,
                    'hours' => []
                ];
                // Initial sale menu by hours
                foreach ($timeRange as $h => $val) {
                    $menuSale[$item][$type][$menuName]['hours'][$h] = ['I' => 0, 'C' => 0, 'D' => 0];
                }
            }
            
            $menuSale[$item][$type][$menuName]['hours'][$hour][$orderType] += $qty;
            $menuSale[$item][$type][$menuName]['total']                    += $qty;
            
            // Count total menu
            $totalMenuSaleBySize[$item][$type] = count($menuSale[$item][$type]);
        }
        
        // Count total menu for merging rows
        foreach ($totalMenuSaleBySize as $key => $total) {
            $totalMenuSaleBySize[$key] = count($total) + array_sum($total);
        }
        
        $res['data']               = $menuSale;
        $res['total_menu_by_size'] = $totalMenuSaleBySize;
        $res['time_range']         = $timeRange;
        $res['period']             = $param['period'];
        $res['date']               = date("d/m/Y");
        $res['time']               = date("H:i:s");
        $res['layout']             = isset($param['printview']) ? "layouts.webview" : "admin::index";
        
        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'SALES MIX BY MENU';
        $res['base_url_download'] = route('report-sales-mix-by-menu');
        
        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SalesMixByMenuExport($res), "sales_mix_by_menu{$extensionFile}");
        }
        
        return view("report.sales_mix_by_menu", $res);
    }
}
