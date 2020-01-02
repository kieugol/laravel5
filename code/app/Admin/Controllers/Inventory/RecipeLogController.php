<?php
/**
 * Created by PhpStorm.
 * User: duongtram
 * Date: 2/19/2019
 * Time: 8:27 AM
 */

namespace App\Admin\Controllers\Inventory;

use App\Admin\Controllers\BaseController;
use App\Admin\Exports\Inventory\RecipeLogExport;
use App\Helpers\ConfigHelp;
use App\Helpers\PosHelper;
use App\Repository\Inventory\RecipeLogRepository;
use App\Widget\DateRange;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RecipeLogController extends BaseController
{
    private $outletInformation;
    private $widget;
    private $recipe_log_repository;

    public function __construct(RecipeLogRepository $recipe_log_repository)
    {
        parent::__construct();
        $this->outletInformation = ConfigHelp::get("outlet_code") . '.' . ConfigHelp::get("outlet_name");
        $this->widget            = new DateRange("fromDate", "toDate");
        $this->widget->setFromDate(date("Y-m-d"))->setToDate(date("Y-m-d"));
        $this->recipe_log_repository  = $recipe_log_repository;

        // Disable libxml errors and allow user to fetch error information as needed
        libxml_use_internal_errors(true);
    }

    public function report(Request $request)
    {
        $param        = $this->getParamFilter($request);
        $items        = $this->recipe_log_repository->getByPeriod("{$param['fromDate']} 00:00:00", "{$param['toDate']} 59:59:59");

        $data['data']   = $items;
        $data['period'] = $param['period'];
        $data['date']   = date("d/m/Y");
        $data['time']   = date("H:i:s");
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget']            = $this->widget;
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = "Recipe Log";
        $data['base_url_download'] = route('inventory-report-recipe-log');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new RecipeLogExport($data), "recipe_log{$extensionFile}");
        }

        return view("inventory_report.recipe_log", $data);
    }

}
