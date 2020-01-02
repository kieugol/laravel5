<?php

namespace App\Admin\Controllers;

require app_path() . "/../vendor/mike42/escpos-php/autoload.php";

use App\Repository\{OrderDetailLogRepository};
use Illuminate\Http\Request;
use App\Helpers\{PosHelper, ConfigHelp};
use  App\Widget\DateRange;
use  Maatwebsite\Excel\Facades\Excel;
use App\Admin\Exports\{PaymentNonCashExport};


class ReportOrderController extends BaseController
{
    private $orderDetailLogRep = null;
    private $widget = null;
    private $outletInformation = null;

    public function __construct(OrderDetailLogRepository $orderDetailLogRep)
    {
        parent::__construct();
        $this->orderDetailLogRep = $orderDetailLogRep;
        $this->widget = new DateRange("fromDate", "toDate");
        $this->widget->setFromDate(date("Y-m-d"))->setToDate(date("Y-m-d"));
        $this->outletInformation = ConfigHelp::get("outlet_code") . '.' . ConfigHelp::get("outlet_name");

        // Disable libxml errors and allow user to fetch error information as needed
        libxml_use_internal_errors(true);
    }

    private function getData() {
        $data = array(
            'outlet_name' => ConfigHelp::get("outlet_name"),
            'outlet_code' => ConfigHelp::get("outlet_code"),
        );

        return $data;
    }

    public function getActivityLogOrder(Request $request) {
        $param = $this->getParamFilter($request);
        $result = $this->orderDetailLogRep->getActivitylogOrder($param);
        $result = $this->filterData($result);

        $data = $this->getData();

        $data['data'] = $result;
        $data['period'] = $param['period'];
        $data['date'] = date("d/m/Y");
        $data['time'] = date("H:i:s");
        $data['action_edit_order'] = ACTION_EDIT_ORDER;
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget'] = $this->widget;
        $data['outletInformation'] = $this->outletInformation;
        $data['name'] = 'ACTIVITY LOG EDIT ORDER';
        $data['base_url_download'] = route('report-payment-non-cash');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new PaymentNonCashExport($data), "activity_log_order.{$extensionFile}");
        }

        return view("report.activity_log_order", $data);
    }

    protected function filterData($input)
    {
        $menu_child = [];
        $menu_parent = [];

        foreach ($input as $row) {
            $menu_parent_id = $row['order_detail_id'];
            $id = $row['id'];
            $row['action_name'] = ACTION_EDIT_ORDER[$row['action']];
            $row['class_bold'] = $row['is_combo'] == 1 ? ' bold' : '';
            if ($menu_parent_id > 0) {
                if (isset($menu_child[$menu_parent_id])) {
                    $menu_child[$menu_parent_id][] = $row;
                } else {
                    $menu_child[$menu_parent_id] = [$row];
                }
            } else {
                $menu_parent[$id] = $row;
            }
        }
        foreach ($menu_parent as $id => $item) {
            if (isset($menu_child[$id])) {
                $menu_parent[$id]['menu_child'] = $menu_child[$id];
            }
        }

        return $menu_parent;
    }
}
