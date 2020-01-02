<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 2/19/2019
 * Time: 8:27 AM
 */

namespace App\Admin\Controllers\Inventory;

use App\Admin\Controllers\BaseController;
use App\Helpers\ConfigHelp;
use App\Helpers\PosHelper;
use App\Repository\Inventory\MasterMaterialUsageReportRepository;
use App\Widget\DateRange;
use Illuminate\Http\Request;
use App\Admin\Exports\Inventory\ViewMaterialDetailUsageExport;
use Maatwebsite\Excel\Facades\Excel;

class ViewMaterialUsageController extends BaseController
{
    private $outletInformation;
    private $widget;
    private $material_usage_report_repository;
    private $is_report_class;

    public function __construct(MasterMaterialUsageReportRepository $material_usage_report_repository)
    {
        parent::__construct();
        $this->outletInformation = ConfigHelp::get("outlet_code") . '.' . ConfigHelp::get("outlet_name");
        $this->widget            = new DateRange("fromDate", "toDate");
        $this->widget->setFromDate(date("Y-m-d"))->setToDate(date("Y-m-d"));
        $this->material_usage_report_repository = $material_usage_report_repository;

        $this->is_report_class   = array(0 => '', 1 => 'light-orange', 2 => 'light-green');

        // Disable libxml errors and allow user to fetch error information as needed
        libxml_use_internal_errors(true);
    }

    public function report(Request $request)
    {
        $position_sku = 0;
        $param        = $this->getParamFilter($request);

        $items  = $this->material_usage_report_repository->getList($param);
        $arr_order_id = [];

        foreach ($items as $item) {
            if (!array_key_exists($item->order_id, $arr_order_id)) {
                $arr_order_id[$item->order_id] = [
                    'order_id'   => $item->order_id,
                    'number'     => 1,
                    'cost_order' => $item->total,
                    'sku'        => []
                ];
            } else {
                $arr_order_id[$item->order_id]['number']++;
                $arr_order_id[$item->order_id]['cost_order'] = $arr_order_id[$item->order_id]['cost_order'] + $item->total;
            }
        }

        $arr_sort = [];
        foreach ($arr_order_id as $key => $order_id) {
            $arr_temp = [];
            foreach ($items as $item) {
                if ($item->order_id == $key) {
                    $arr_temp[] = $item;
                }
            }
            $sku = array_column($arr_temp, 'sku');
            array_multisort($sku, SORT_DESC, $arr_temp);
            $arr_sort = array_merge($arr_sort, $arr_temp);
        }

        foreach ($arr_sort as $item) {
            if (!array_key_exists($item->sku, $arr_order_id[$item->order_id]['sku'])) {
                $arr_order_id[$item->order_id]['sku'][$item->sku] = [
                    'sku'      => $item->sku,
                    'number'   => 1,
                    'cost_sku' => $item->total
                ];
            } else {
                $arr_order_id[$item->order_id]['sku'][$item->sku]['number']++;
                $arr_order_id[$item->order_id]['sku'][$item->sku]['cost_sku'] = $arr_order_id[$item->order_id]['sku'][$item->sku]['cost_sku'] + $item->total;
            }
        }

        foreach ($arr_sort as &$item) {
            if (array_key_exists($item->order_id, $arr_order_id)) {
                $item->rowspan_order_id = $arr_order_id[$item->order_id]['number'];
                $item->cost_order       = $arr_order_id[$item->order_id]['cost_order'];
                foreach ($arr_order_id[$item->order_id]['sku'] as $sku) {
                    $arr_sort[$position_sku]->rowspan_sku = $sku['number'];
                    $arr_sort[$position_sku]->cost_sku    = $sku['cost_sku'];
                    $position_sku                         += $sku['number'];
                }
                unset($arr_order_id[$item->order_id]);
            }

            $item->highlight = $this->is_report_class[$item->type];
        }

        $data['data']   = $arr_sort;
        $data['period'] = $param['period'];
        $data['date']   = date("d/m/Y");
        $data['time']   = date("H:i:s");
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget']            = $this->widget;
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = "Material Usage";
        $data['base_url_download'] = route('inventory-report-material-usage');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new ViewMaterialDetailUsageExport($data), "material_detail_usage{$extensionFile}");
        }

        return view("inventory_report.material_usage", $data);
    }

}
