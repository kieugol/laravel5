<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 2/19/2019
 * Time: 11:19 AM
 */

namespace App\Admin\Controllers\Inventory;

use App\Admin\Controllers\BaseController;
use App\Helpers\ConfigHelp;
use App\Helpers\PosHelper;
use App\Repository\Inventory\MasterMaterialDetailRepository;
use App\Repository\Inventory\MasterTypeRepository;
use App\Repository\Inventory\ViewCurrentStockRecipeRepository;
use App\Repository\Inventory\ViewCurrentStockRepository;
use App\Widget\DateRange;
use Illuminate\Http\Request;
use App\Admin\Exports\Inventory\ViewCurrentStockExport;
use Maatwebsite\Excel\Facades\Excel;

class ViewCurrentStockController extends BaseController
{
    private $outletInformation;
    private $widget;
    private $view_current_stock_repository;
    private $view_current_stock_recipe_repository;
    private $master_type_repository;
    private $master_material_detail_repository;

    public function __construct(
        ViewCurrentStockRepository $view_current_stock_repository,
        ViewCurrentStockRecipeRepository $view_current_stock_recipe_repository,
        MasterMaterialDetailRepository $master_material_detail_repository,
        MasterTypeRepository $master_type_repository
    )
    {
        parent::__construct();
        $this->outletInformation = ConfigHelp::get("outlet_code") . '.' . ConfigHelp::get("outlet_name");
        $this->widget            = new DateRange("fromDate", "toDate");
        $this->widget->setFromDate(date("Y-m-d"))->setToDate(date("Y-m-d"));
        $this->view_current_stock_repository        = $view_current_stock_repository;
        $this->view_current_stock_recipe_repository = $view_current_stock_recipe_repository;
        $this->master_type_repository               = $master_type_repository;
        $this->master_material_detail_repository    = $master_material_detail_repository;

        // Disable libxml errors and allow user to fetch error information as needed
        libxml_use_internal_errors(true);
    }

    public function report(Request $request)
    {
        $param     = $this->getParamFilter($request);
        $materials = $this->view_current_stock_repository->getList($param);
        foreach ($materials as &$item) {
            $material_details  = $this->master_material_detail_repository->getRecipeUomDetailByMaterialId($item->material_id);
            $item->conversions = $this->convertRecipeQtyToOutletQty($item->quantity_recipe, $material_details);
            unset($item->material_id);
        }
        $recipes      = $this->view_current_stock_recipe_repository->getList($param);
        $master_types = $this->master_type_repository->getList();

        $data['materials'] = $materials;
        $data['recipes']   = $recipes;
        $data['master_types'] = $master_types;
        $data['period']       = '';
        $data['layout']       = isset($param['printview']) ? "layouts.webview" : "admin::index";
        $data['layout']       = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget']            = $this->widget;
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = "Stock on Hand";
        $data['base_url_download'] = route('inventory-report-current-stock');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new ViewCurrentStockExport($data), "current_stock{$extensionFile}");
        }

        return view("inventory_report.current_stock", $data);
    }

    protected function convertRecipeQtyToOutletQty($qty_recipe, $arr_material_detail)
    {
        foreach ($arr_material_detail as &$item) {
            $qty_outlet = $qty_recipe;
            if ($item->recipe_rate_uom_id != $item->recipe_uom_id && $item->recipe_rate != 0) {
                $qty_outlet = (float)$qty_recipe / (float)$item->recipe_rate;
            }
            if ($item->recipe_uom_id != $item->outlet_uom_id && $item->conversion_rate != 0) {
                $qty_outlet = (float)$qty_outlet / (float)$item->conversion_rate;
            }
            $item->qty_outlet = $qty_outlet;
            unset($item->recipe_rate_uom_id);
            unset($item->recipe_uom_id);
            unset($item->outlet_uom_id);
            unset($item->conversion_rate);
        }
        return $arr_material_detail;
    }

}
