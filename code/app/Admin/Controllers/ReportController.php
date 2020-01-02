<?php

namespace App\Admin\Controllers;

require app_path() . "/../vendor/mike42/escpos-php/autoload.php";

use Illuminate\Support\Facades\Log;
use Mike42\Escpos\{Printer, EscposImage};
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use App\Repository\{Inventory\MasterMaterialUsageReportRepository,
    Inventory\MasterPCCRepository,
    Inventory\ReceiveOrderRepository,
    Inventory\ReturnOrderRepository,
    Inventory\StockOpnameRepository,
    Inventory\TransferOrderRepository,
    Inventory\ViewDailyReceivingSummaryRepository,
    Inventory\ViewMaterialUsageRepository,
    Inventory\ViewSummaryMaterialUsageRepository,
    OrderRepository,
    UserRepository,
    EodHistoryRepository};
use Illuminate\Http\Request;
use App\Helpers\{PosHelper, ConfigHelp};
use  App\Widget\DateRange;
use  Maatwebsite\Excel\Facades\Excel;
use App\Admin\Exports\{PartnerExport,
    PaymentNonCashExport,
    SummaryLogExport,
    SummaryLogV2Export,
    SummaryLogV3Export,
    SummaryLogV4Export,
    SalesMixBySegmentExport,
    SalesMixBySegmentOCExport,
    SalesMixByHourExport,
    SummaryExport,
    SummaryOCExport,
    SummaryUsageExport,
    SummaryVoidExport,
    SpeedServiceTakeawayExport,
    SpeedServiceDelivertExport,
    PaymentDetailExport,
    PaymentByCashierExport,
    CustomerOrderingExport,
    NetSalesAndBillByHoursExport,
    SetoranTunaiBankExport,
    BillByPodingExport,
    DonationDetailExport,
    HistoryByBillExport,
    SpeedServiceSwipedoneExport};

class ReportController extends BaseController
{

    private $morder;
    private $muser;
    private $meodhistory;
    private $widget;
    private $outletInformation;
    private $storeCode;
    private $stockOpnameRep;
    private $receiveRep;
    private $returnRep;
    private $transferRep;
    private $viewDailyReceivingSummaryRep;
    private $masterPccRep;
    private $viewMaterialUsageRep;
    private $masterMaterialUsageReportRep;
    private $viewSummaryMaterialUsageRep;

    public function __construct(
        OrderRepository $morder,
        UserRepository $muser,
        EodHistoryRepository $meodhistory,
        ViewDailyReceivingSummaryRepository $viewDailyReceivingSummaryRep,
        StockOpnameRepository $stockOpnameRep,
        ReceiveOrderRepository $receiveRep,
        ReturnOrderRepository $returnRep,
        TransferOrderRepository $transferRep,
        MasterPCCRepository $masterPccRep,
        ViewMaterialUsageRepository $viewMaterialUsageRep,
        MasterMaterialUsageReportRepository $masterMaterialUsageReportRep,
        ViewSummaryMaterialUsageRepository $viewSummaryMaterialUsageRep
    )
    {
        parent::__construct();
        $this->morder      = $morder;
        $this->muser       = $muser;
        $this->meodhistory = $meodhistory;
        $this->widget      = new DateRange("fromDate", "toDate");
        $this->widget->setFromDate(date("Y-m-d"))->setToDate(date("Y-m-d"));
        $this->outletInformation = ConfigHelp::get("outlet_code") . '.' . ConfigHelp::get("outlet_name");
        $this->storeCode         = ConfigHelp::get("outlet_code");
        $this->viewDailyReceivingSummaryRep = $viewDailyReceivingSummaryRep;
        $this->stockOpnameRep               = $stockOpnameRep;
        $this->receiveRep                   = $receiveRep;
        $this->returnRep                    = $returnRep;
        $this->transferRep                  = $transferRep;
        $this->masterPccRep                 = $masterPccRep;
        $this->viewMaterialUsageRep         = $viewMaterialUsageRep;
        $this->masterMaterialUsageReportRep = $masterMaterialUsageReportRep;
        $this->viewSummaryMaterialUsageRep = $viewSummaryMaterialUsageRep;

        // Disable libxml errors and allow user to fetch error information as needed
        libxml_use_internal_errors(true);
    }

    private function getData()
    {
        $data = array(
            'outlet_name' => ConfigHelp::get("outlet_name"),
            'outlet_code' => ConfigHelp::get("outlet_code"),
        );

        return $data;
    }

    public function payment_non_cash(Request $request)
    {
        $param = $this->getParamFilter($request);
        $items = $this->morder->getReportNonCash($param);

        $data        = $this->getData();
        $arr         = array();
        $grand_total = 0;

        foreach ($items as $item) {
            $item->value_format                                = number_format($item->value);
            $item->date                                        = date("d/m/Y", strtotime($item->created_date));
            $sub_total                                         = isset($arr[$item->payment_method_name]) ? $arr[$item->payment_method_name]->sub_total : 0;
            $arr[$item->payment_method_name]->data[]           = $item;
            $arr[$item->payment_method_name]->sub_total        = $sub_total + $item->value;
            $arr[$item->payment_method_name]->sub_total_format = number_format($arr[$item->payment_method_name]->sub_total);
            $grand_total                                       += $item->value;
        }

        $data['data']        = $arr;
        $data['grand_total'] = number_format($grand_total);
        $data['period']      = $param['period'];
        $data['date']        = date("d/m/Y");
        $data['time']        = date("H:i:s");
        $data['layout']      = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget']            = $this->widget;
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = 'PAYMENT NON CASH';
        $data['base_url_download'] = route('report-payment-non-cash');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new PaymentNonCashExport($data), "payment_non_cash{$extensionFile}");
        }

        return view("report.payment_non_cash", $data);
    }

    public function summary_log(Request $request)
    {
        $param       = $this->getParamFilter($request);
        $items       = $this->morder->getReportSummaryLog($param);
        $order_types = config("admin.order_types");

        date_default_timezone_set("GMT");
        foreach ($items as &$item) {
            $production = $item->checkout_time ? strtotime($item->checkout_time) - strtotime($item->kds_time) : 0;
            $cashout    = $item->finished_time ? strtotime($item->finished_time) : 0;
            $dispatch   = '';
            $driverout  = '';
            $drivertime = '';
            $delivery   = '';
            if ($item->order_type_id == ORDER_TYPE_DELIVERY) {
                $dispatch  = $item->delivering_time && $item->checkout_time ? strtotime($item->delivering_time) - strtotime($item->checkout_time) : 0;
                $driverout = $item->delivering_time ? strtotime($item->delivering_time) : 0;
                $instore   = $item->delivering_time ? strtotime($item->delivering_time) - strtotime($item->kds_time) : 0;
                if (empty($item->delivered_time)) {
                    $dtime = $item->delivering_time && $item->finished_time ? intval((strtotime($item->finished_time) - strtotime($item->delivering_time))) : 0;
                } else {
                    $dtime = $item->delivered_time && $item->finished_time ? intval((strtotime($item->delivered_time) - strtotime($item->delivering_time))) : 0;
                }
                $drivertime = strpos(",", $item->delivery_together) !== false ? intval($dtime / 2.5) : intval($dtime / 2);
                $delivery   = $instore + $drivertime;
            }

            $item->ordertaken     = date("H:i:s", strtotime($item->kds_time));
            $item->maketime       = $item->cooked_time ? date("H:i:s", strtotime($item->cooked_time) - strtotime($item->kds_time)) : "";
            $item->productiontime = !empty($production) ? date("H:i:s", $production) : "";
            $item->dispatchtime   = !empty($dispatch) ? date("H:i:s", $dispatch) : "";
            $item->driverouttime  = !empty($driverout) ? date("H:i:s", $driverout) : "";
            $item->cashouttime    = !empty($cashout) ? date("H:i:s", $cashout) : "";
            $item->instoretime    = !empty($instore) ? date("H:i:s", $instore) : "";
            $item->drivertime     = !empty($drivertime) ? date("H:i:s", $drivertime) : "";
            $item->deliverytime   = !empty($delivery) ? date("H:i:s", $delivery) : "";

            $item->amount = PosHelper::beforeTax($item->amount);

            // format phone number
            $item->phone = $this->formatPhoneNumber($item->phone);
        }
        $data['data']   = $items;
        $data['period'] = $param['period'];
        $data['date']   = date("d/m/Y");
        $data['time']   = date("H:i:s");
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        date_default_timezone_set(config("app.timezone"));

        // Common information
        $data['widget']            = $this->widget;
        $data['orderTypesMaster']  = config("admin.order_types");
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = !empty($param['type']) ? strtoupper($order_types[$param['type']]) . " LOG" : "SUMMARY LOG";
        $data['base_url_download'] = route('report-summary-log');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SummaryLogExport($data), "summary_log{$extensionFile}");
        }

        return view("report.summary_log", $data);
    }

    /**
     *format phone number for Indonesia
     * @param $phone_number
     * @return string
     */
    public function formatPhoneNumber($phone_number) {
        $formatted_phone_number = $phone_number;

        // add 0 if start with 8
        if (substr($phone_number, 0, 1) == 8) {
            $formatted_phone_number = '0' . $phone_number;
        }

        // replace 62 by 0
        if (substr($phone_number, 0, 2) == 62) {
            $formatted_phone_number = '0' . (substr($phone_number, 2));
        }

        return $formatted_phone_number;
    }

    public function summary_log_v2(Request $request)
    {
        $param       = $this->getParamFilter($request);
        $items       = $this->morder->getReportSummaryLog($param);
        $order_types = config("admin.order_types");

        $data['data']   = $items;
        $data['period'] = $param['period'];
        $data['date']   = date("d/m/Y");
        $data['time']   = date("H:i:s");
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget']            = $this->widget;
        $data['orderTypesMaster']  = config("admin.order_types");
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = !empty($param['type']) ? strtoupper($order_types[$param['type']]) . " LOG V.2" : "SUMMARY LOG V.2";
        $data['base_url_download'] = route('report-summary-log-v2');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SummaryLogV2Export($data), "summary_log_v2{$extensionFile}");
        }

        return view("report.summary_log_v2", $data);
    }

    public function summary_log_v3(Request $request)
    {
        $param       = $this->getParamFilter($request);
        $items       = $this->morder->getReportSummaryLog($param);
        $order_types = config("admin.order_types");

        date_default_timezone_set("GMT");
        foreach ($items as &$item) {
            $ordertime  = strtotime($item->kds_time) - strtotime($item->make_time);
            $maketime   = strtotime($item->cooked_time) - strtotime($item->kds_time);
            $cuttime    = strtotime($item->checkout_time) - strtotime($item->cooked_time);
            $dispatch   = '';
            $drivertime = '';
            if ($item->order_type_id == ORDER_TYPE_DELIVERY) {
                $dispatch   = $item->delivering_time && $item->checkout_time ? strtotime($item->delivering_time) - strtotime($item->checkout_time) : 0;
                $drivertime = $item->delivering_time && $item->finished_time ? strtotime($item->finished_time) - strtotime($item->delivering_time) : 0;
                if (!empty($item->delivered_time)) {
                    $drivertime = $item->delivering_time && $item->delivered_time ? strtotime($item->delivered_time) - strtotime($item->delivering_time) : 0;
                }
            }

            $item->ordertime    = date("H:i:s", $ordertime);
            $item->maketime     = date("H:i:s", $maketime);
            $item->cuttime      = date("H:i:s", $cuttime);
            $item->dispatchtime = !empty($dispatch) ? date("H:i:s", $dispatch) : "";
            $item->drivertime   = !empty($drivertime) ? date("H:i:s", $drivertime) : "";
        }
        $data['data']   = $items;
        $data['period'] = $param['period'];
        $data['date']   = date("d/m/Y");
        $data['time']   = date("H:i:s");
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        date_default_timezone_set(config("app.timezone"));

        // Common information
        $data['widget']            = $this->widget;
        $data['orderTypesMaster']  = config("admin.order_types");
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = !empty($param['type']) ? strtoupper($order_types[$param['type']]) . " LOG V3" : "SUMMARY LOG V3";
        $data['base_url_download'] = route('report-summary-log-v3');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SummaryLogV3Export($data), "summary_log_v3{$extensionFile}");
        }

        return view("report.summary_log_v3", $data);
    }

    public function summary_log_v4(Request $request)
    {
        $param       = $this->getParamFilter($request);
        $items       = $this->morder->getReportSummaryLog($param);
        $order_types = config("admin.order_types");

        date_default_timezone_set("GMT");
        foreach ($items as &$item) {
            $productiontime = strtotime($item->checkout_time) - strtotime($item->kds_time);
            $deliverytime   = 0;
            if ($item->order_type_id == ORDER_TYPE_DELIVERY) {
                $instoretime = $item->delivering_time ? strtotime($item->delivering_time) - strtotime($item->kds_time) : 0;
                if (empty($item->delivered_time)) {
                    $dtime = $item->delivering_time && $item->finished_time ? intval((strtotime($item->finished_time) - strtotime($item->delivering_time))) : 0;
                } else {
                    $dtime = $item->delivered_time && $item->finished_time ? intval((strtotime($item->delivered_time) - strtotime($item->delivering_time))) : 0;
                }
                $deliverytime = strpos(",", $item->delivery_together) !== false ? intval($dtime / 2.5) : intval($dtime / 2);
                $servicetime  = $instoretime + $deliverytime;
            } else {
                $instoretime = $productiontime;
            }

            $item->productiontime = !empty($productiontime) ? date("H:i:s", $productiontime) : "";
            $item->instoretime    = !empty($instoretime) ? date("H:i:s", $instoretime) : "";
            $item->deliverytime   = !empty($deliverytime) ? date("H:i:s", $deliverytime) : "";
            $item->servicetime    = !empty($servicetime) ? date("H:i:s", $servicetime) : "";
        }
        $data['data']   = $items;
        $data['period'] = $param['period'];
        $data['date']   = date("d/m/Y");
        $data['time']   = date("H:i:s");
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        date_default_timezone_set(config("app.timezone"));

        // Common information
        $data['widget']            = $this->widget;
        $data['orderTypesMaster']  = config("admin.order_types");
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = !empty($param['type']) ? strtoupper($order_types[$param['type']]) . " LOG V4" : "SUMMARY LOG V4";
        $data['base_url_download'] = route('report-summary-log-v4');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SummaryLogV4Export($data), "summary_log_v4{$extensionFile}");
        }

        return view("report.summary_log_v4", $data);
    }

    public function partner(Request $request)
    {
        $gojek  = array();
        $grab   = array();
        $others = array();

        $param       = $this->getParamFilter($request);
        $orders      = $this->morder->getReportbyPartner($param);
        $count       = 0;
        $grand_total = 0;

        foreach ($orders as $order) {
            $order->date          = date("d/m/Y", strtotime($order->created_date));
            $order->time          = date("H:i", strtotime($order->created_date));
            $order->amount_format = "Rp " . number_format($order->amount);
            $grand_total          += $order->amount;
            $count++;
            if ($order->code_partner == GOJEK_CODE) {
                $gojek[] = $order;
            } elseif ($order->code_partner == GRAB_CODE) {
                $grab[] = $order;
            } else {
                $others[] = $order;
            }
        }

        $res['gojeks']             = $gojek;
        $res['grabs']              = $grab;
        $res['others']             = $others;
        $res['count']              = $count;
        $res['grand_total']        = $grand_total;
        $res['grand_total_format'] = "Rp " . number_format($grand_total);
        $res['period']             = $param['period'];
        $res['date']               = date("d/m/Y");
        $res['time']               = date("H:i:s");
        $res['layout']             = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'BY PARTNER';
        $res['base_url_download'] = route('report-partner');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new PartnerExport($res), "partner{$extensionFile}");
        }

        return view("report.partner", $res);
    }

    public function sales_mix_by_segment(Request $request)
    {
        $param            = $this->getParamFilter($request);
        $orders           = $this->morder->getReportMix($param);
        $list_category    = array();
        $list_menu        = array();
        $order_parent_qty = [];
        $grand            = array(
            'D'     => array('quantity' => 0, 'amount' => 0, 'amount_format' => 0),
            'C'     => array('quantity' => 0, 'amount' => 0, 'amount_format' => 0),
            'I'     => array('quantity' => 0, 'amount' => 0, 'amount_format' => 0),
            'total' => array('quantity' => 0, 'amount' => 0, 'amount_format' => 0)
        );
        foreach ($orders as &$order) {
            $order_parent_qty[$order->id] = $order->quantity;
            $arr_name                     = array($order->variant_name, $order->addon_name, $order->menu_name);
            if ($order->variant_name != "" && $order->addon_name != "") {
                $menu_name            = $order->variant_name . "/" . "$order->addon_name $order->menu_name";
                $order->category_name = strtoupper($order->variant_name);
            } else {
                $menu_name            = trim(implode(" ", $arr_name));
                $order->category_name = strtoupper($order->category_name);
            }

            // merge category 'Extra cheese' and 'Extra sauce' into 'Extra sauce'
            if ($order->category_name == 'EXTRA CHEESE') {
                $order->category_name = 'EXTRA SAUCE';
            }

            $list_category[$order->category_name]['name']                   = $order->category_name;
            $list_category[$order->category_name]['D']['quantity']          = 0;
            $list_category[$order->category_name]['D']['amount']            = 0;
            $list_category[$order->category_name]['D']['amount_format']     = 0;
            $list_category[$order->category_name]['C']['quantity']          = 0;
            $list_category[$order->category_name]['C']['amount']            = 0;
            $list_category[$order->category_name]['C']['amount_format']     = 0;
            $list_category[$order->category_name]['I']['quantity']          = 0;
            $list_category[$order->category_name]['I']['amount']            = 0;
            $list_category[$order->category_name]['I']['amount_format']     = 0;
            $list_category[$order->category_name]['total']['quantity']      = 0;
            $list_category[$order->category_name]['total']['amount']        = 0;
            $list_category[$order->category_name]['total']['amount_format'] = 0;

            $list_menu[$order->category_name][$order->addon_name]['name']                   = strtoupper($order->variant_name) . " " . $order->addon_name;
            $list_menu[$order->category_name][$order->addon_name]['I']['quantity']          = 0;
            $list_menu[$order->category_name][$order->addon_name]['I']['amount']            = 0;
            $list_menu[$order->category_name][$order->addon_name]['I']['amount_format']     = 0;
            $list_menu[$order->category_name][$order->addon_name]['D']['quantity']          = 0;
            $list_menu[$order->category_name][$order->addon_name]['D']['amount']            = 0;
            $list_menu[$order->category_name][$order->addon_name]['D']['amount_format']     = 0;
            $list_menu[$order->category_name][$order->addon_name]['C']['quantity']          = 0;
            $list_menu[$order->category_name][$order->addon_name]['C']['amount']            = 0;
            $list_menu[$order->category_name][$order->addon_name]['C']['amount_format']     = 0;
            $list_menu[$order->category_name][$order->addon_name]['total']['quantity']      = 0;
            $list_menu[$order->category_name][$order->addon_name]['total']['amount']        = 0;
            $list_menu[$order->category_name][$order->addon_name]['total']['amount_format'] = 0;

            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['name']                   = $menu_name;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['addon_name']             = $order->addon_name;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['sku']                    = $order->sku;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['D']['quantity']          = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['D']['amount']            = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['D']['amount_format']     = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['C']['quantity']          = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['C']['amount']            = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['C']['amount_format']     = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['I']['quantity']          = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['I']['amount']            = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['I']['amount_format']     = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['total']['quantity']      = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['total']['amount']        = 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['total']['amount_format'] = 0;
        }
        $order = null;
        foreach ($orders as $order) {
            $qty = $order->quantity;

            $list_category[$order->category_name][$order->order_type_id]['quantity']      += $qty;
            $list_category[$order->category_name][$order->order_type_id]['amount']        += $order->order_detail_id == NULL ? $qty * $order->sub_price : 0;
            $list_category[$order->category_name][$order->order_type_id]['amount_format'] = number_format($list_category[$order->category_name][$order->order_type_id]['amount']);
            $list_category[$order->category_name]['total']['quantity']                    += $qty;
            $list_category[$order->category_name]['total']['amount']                      += $order->order_detail_id == NULL ? $qty * $order->sub_price : 0;
            $list_category[$order->category_name]['total']['amount_format']               = number_format($list_category[$order->category_name]['total']['amount']);

            $list_menu[$order->category_name][$order->addon_name][$order->order_type_id]['quantity']      += $qty;
            $list_menu[$order->category_name][$order->addon_name][$order->order_type_id]['amount']        += $order->order_detail_id == NULL ? $qty * $order->sub_price : 0;
            $list_menu[$order->category_name][$order->addon_name][$order->order_type_id]['amount_format'] = number_format($list_menu[$order->category_name][$order->addon_name][$order->order_type_id]['amount']);
            $list_menu[$order->category_name][$order->addon_name]['total']['quantity']                    += $qty;
            $list_menu[$order->category_name][$order->addon_name]['total']['amount']                      += $order->order_detail_id == NULL ? $qty * $order->sub_price : 0;
            $list_menu[$order->category_name][$order->addon_name]['total']['amount_format']               = number_format($list_menu[$order->category_name][$order->addon_name]['total']['amount']);

            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode][$order->order_type_id]['quantity']      += $qty;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode][$order->order_type_id]['amount']        += $order->order_detail_id == NULL ? $qty * $order->sub_price : 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode][$order->order_type_id]['amount_format'] = number_format($list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode][$order->order_type_id]['amount']);
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['total']['quantity']                    += $qty;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['total']['amount']                      += $order->order_detail_id == NULL ? $qty * $order->sub_price : 0;
            $list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['total']['amount_format']               = number_format($list_menu[$order->category_name][$order->addon_name]['menus'][$order->plucode]['total']['amount']);

            $grand[$order->order_type_id]['quantity']      += $qty;
            $grand[$order->order_type_id]['amount']        += $order->order_detail_id == NULL ? $qty * $order->sub_price : 0;
            $grand[$order->order_type_id]['amount_format'] = number_format($grand[$order->order_type_id]['amount']);
            $grand['total']['quantity']                    += $qty;
            $grand['total']['amount']                      += $order->order_detail_id == NULL ? $qty * $order->sub_price : 0;
            $grand['total']['amount_format']               = number_format($grand['total']['amount']);
        }
        foreach ($list_category as $key => $item_category) {
            $list_category[$key]['menus'] = $list_menu[$key];
        }
        $res['grand']  = $grand;
        $res['data']   = $list_category;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'SALES MIX BY SEGMENT';
        $res['base_url_download'] = route('report-sales-mix-by-segment');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SalesMixBySegmentExport($res), "sales_mix_by_segment{$extensionFile}");
        }

        return view("report.sales_mix_by_segment", $res);
    }

    public function sales_mix_by_segment_oc(Request $request)
    {
        $param  = $this->getParamFilter($request);
        $orders = $this->morder->getReportMixBySegmentOC($param);

        $list_category = array();
        $list_menu     = array();
        $grand         = array(
            'total' => array('quantity' => 0, 'amount' => 0, 'amount_format' => 0),
            'D'     => array('quantity' => 0, 'amount' => 0, 'amount_format' => 0),
            'C'     => array('quantity' => 0, 'amount' => 0, 'amount_format' => 0),
            'I'     => array('quantity' => 0, 'amount' => 0, 'amount_format' => 0),
        );
        foreach ($orders as $order) {
//            $arr_category = isset()
            $arr_name                                                       = array($order->variant_name, $order->addon_name, $order->menu_name);
            $menu_name                                                      = trim(implode(" ", $arr_name));
            $list_category[$order->category_name]['name']                   = $order->category_name;
            $list_category[$order->category_name]['D']['quantity']          = 0;
            $list_category[$order->category_name]['D']['amount']            = 0;
            $list_category[$order->category_name]['D']['amount_format']     = 0;
            $list_category[$order->category_name]['C']['quantity']          = 0;
            $list_category[$order->category_name]['C']['amount']            = 0;
            $list_category[$order->category_name]['C']['amount_format']     = 0;
            $list_category[$order->category_name]['I']['quantity']          = 0;
            $list_category[$order->category_name]['I']['amount']            = 0;
            $list_category[$order->category_name]['I']['amount_format']     = 0;
            $list_category[$order->category_name]['total']['quantity']      = 0;
            $list_category[$order->category_name]['total']['amount']        = 0;
            $list_category[$order->category_name]['total']['amount_format'] = 0;

            $list_menu[$order->category_name][$order->plucode]['name']                   = $menu_name;
            $list_menu[$order->category_name][$order->plucode]['sku']                    = $order->sku;
            $list_menu[$order->category_name][$order->plucode]['D']['quantity']          = 0;
            $list_menu[$order->category_name][$order->plucode]['D']['amount']            = 0;
            $list_menu[$order->category_name][$order->plucode]['D']['amount_format']     = 0;
            $list_menu[$order->category_name][$order->plucode]['C']['quantity']          = 0;
            $list_menu[$order->category_name][$order->plucode]['C']['amount']            = 0;
            $list_menu[$order->category_name][$order->plucode]['C']['amount_format']     = 0;
            $list_menu[$order->category_name][$order->plucode]['I']['quantity']          = 0;
            $list_menu[$order->category_name][$order->plucode]['I']['amount']            = 0;
            $list_menu[$order->category_name][$order->plucode]['I']['amount_format']     = 0;
            $list_menu[$order->category_name][$order->plucode]['total']['quantity']      = 0;
            $list_menu[$order->category_name][$order->plucode]['total']['amount']        = 0;
            $list_menu[$order->category_name][$order->plucode]['total']['amount_format'] = 0;
        }

        $order = null;
        foreach ($orders as $order) {
            $arr_name                                                                     = array($order->variant_name, $order->addon_name, $order->menu_name);
            $menu_name                                                                    = trim(implode(" ", $arr_name));
            $list_category[$order->category_name][$order->order_type_id]['quantity']      += $order->quantity;
            $list_category[$order->category_name][$order->order_type_id]['amount']        += $order->quantity * $order->sub_price;
            $list_category[$order->category_name][$order->order_type_id]['amount_format'] = number_format($list_category[$order->category_name][$order->order_type_id]['amount']);
            $list_category[$order->category_name]['total']['quantity']                    += $order->quantity;
            $list_category[$order->category_name]['total']['amount']                      += $order->quantity * $order->sub_price;
            $list_category[$order->category_name]['total']['amount_format']               = number_format($list_category[$order->category_name]['total']['amount']);

            $list_menu[$order->category_name][$order->plucode][$order->order_type_id]['quantity']      += $order->quantity;
            $list_menu[$order->category_name][$order->plucode][$order->order_type_id]['amount']        += $order->quantity * $order->sub_price;
            $list_menu[$order->category_name][$order->plucode][$order->order_type_id]['amount_format'] = number_format($list_menu[$order->category_name][$order->plucode][$order->order_type_id]['amount']);
            $list_menu[$order->category_name][$order->plucode]['total']['quantity']                    += $order->quantity;
            $list_menu[$order->category_name][$order->plucode]['total']['amount']                      += $order->quantity * $order->sub_price;
            $list_menu[$order->category_name][$order->plucode]['total']['amount_format']               = number_format($list_menu[$order->category_name][$order->plucode]['total']['amount']);

            $grand[$order->order_type_id]['quantity']      += $order->quantity;
            $grand[$order->order_type_id]['amount']        += $order->quantity * $order->sub_price;
            $grand[$order->order_type_id]['amount_format'] = number_format($grand[$order->order_type_id]['amount']);
            $grand['total']['quantity']                    += $order->quantity;
            $grand['total']['amount']                      += $order->quantity * $order->sub_price;
            $grand['total']['amount_format']               = number_format($grand['total']['amount']);
        }
        foreach ($list_category as $key => $item_category) {
            $list_category[$key]['menus'] = $list_menu[$key];
        }

        $res['data']        = $list_category;
        $res['grand_total'] = $grand;
        $res['period']      = $param['period'];
        $res['date']        = date("d/m/Y");
        $res['time']        = date("H:i:s");
        $res['layout']      = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'SALES MIX BY SEGMENT - OC';
        $res['base_url_download'] = route('report-sales-mix-by-segment-oc');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SalesMixBySegmentOCExport($res), "sales_mix_by_segment_oc{$extensionFile}");
        }

        return view("report.sales_mix_by_segment_oc", $res);
    }

    public function sales_mix_by_hour(Request $request)
    {
        $param  = $this->getParamFilter($request);
        $orders = $this->morder->getReportMixByHour($param);

        $list  = array();
        $total = array();
        foreach ($orders as $order) {
            $hour = date("H", strtotime($order->created_date));
            if (!isset($list[$hour])) {
                $list[$hour][ORDER_TYPE_EATIN]['qty']      = 0;
                $list[$hour][ORDER_TYPE_EATIN]['sales']    = 0;
                $list[$hour][ORDER_TYPE_DELIVERY]['qty']   = 0;
                $list[$hour][ORDER_TYPE_DELIVERY]['sales'] = 0;
                $list[$hour][ORDER_TYPE_TAKEAWAY]['qty']   = 0;
                $list[$hour][ORDER_TYPE_TAKEAWAY]['sales'] = 0;
                $list[$hour]['total']['qty']               = 0;
                $list[$hour]['total']['sales']             = 0;
            }
        }
        $total[ORDER_TYPE_EATIN]['qty']      = 0;
        $total[ORDER_TYPE_EATIN]['sales']    = 0;
        $total[ORDER_TYPE_DELIVERY]['qty']   = 0;
        $total[ORDER_TYPE_DELIVERY]['sales'] = 0;
        $total[ORDER_TYPE_TAKEAWAY]['qty']   = 0;
        $total[ORDER_TYPE_TAKEAWAY]['sales'] = 0;
        $total['total']['qty']               = 0;
        $total['total']['sales']             = 0;

        foreach ($orders as $order) {
            $h                                        = date("H", strtotime($order->created_date));
            $list[$h][$order->order_type_id]['qty']   += 1;
            $list[$h][$order->order_type_id]['sales'] += $order->amount;
            $list[$h]['total']['qty']                 += 1;
            $list[$h]['total']['sales']               += $order->amount;

            $total[$order->order_type_id]['qty']   += 1;
            $total[$order->order_type_id]['sales'] += $order->amount;

            $total['total']['qty']   += 1;
            $total['total']['sales'] += $order->amount;
        }

        $res['data']   = $list;
        $res['total']  = $total;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'SALES MIX BY HOUR';
        $res['base_url_download'] = route('report-sales-mix-by-hour');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SalesMixByHourExport($res), "sales_mix_by_hour{$extensionFile}");
        }

        return view("report.sales_mix_by_hour_v2", $res);
    }

    public function summary(Request $request)
    {
        $param           = $this->getParamFilter($request);
        $orders          = $this->morder->getReportSummaryOrder($param);
        $orders_void     = $this->morder->getReportSummaryOrderVoid($param);
        $order_details   = $this->morder->getReportSummaryOrderDetail($param);
        $payment_methods = $this->morder->getReportSummaryPaymentMethod($param);
        $data            = array();
        $total_bill      = 0;
        $total_amount    = 0;

        $data['D']['count']            = 0;
        $data['D']['sum_amount']       = 0;
        $data['D']['avg_amount']       = 0;
        $data['D']['food_gross_sales'] = 0;
        $data['D']['bvrg_gross_sales'] = 0;
        $data['D']['total_f_b_gross']  = 0;
        $data['D']['discount_food']    = 0;
        $data['D']['discount_bvrg']    = 0;
        $data['D']['total_discount']   = 0;
        $data['D']['food_net_sales']   = 0;
        $data['D']['bvrg_net_sales']   = 0;
        $data['D']['total_f_b_net']    = 0;
        $data['D']['delivery_cost']    = 0;
        $data['D']['others']           = 0;
        $data['D']['restaurant_tax']   = 0;
        $data['D']['transfer_out']     = 0;
        $data['D']['down_payment']     = 0;
        $data['D']['donasi']           = 0;

        $data['C']['count']            = 0;
        $data['C']['sum_amount']       = 0;
        $data['C']['avg_amount']       = 0;
        $data['C']['food_gross_sales'] = 0;
        $data['C']['bvrg_gross_sales'] = 0;
        $data['C']['total_f_b_gross']  = 0;
        $data['C']['discount_food']    = 0;
        $data['C']['discount_bvrg']    = 0;
        $data['C']['total_discount']   = 0;
        $data['C']['food_net_sales']   = 0;
        $data['C']['bvrg_net_sales']   = 0;
        $data['C']['total_f_b_net']    = 0;
        $data['C']['delivery_cost']    = 0;
        $data['C']['others']           = 0;
        $data['C']['restaurant_tax']   = 0;
        $data['C']['transfer_out']     = 0;
        $data['C']['down_payment']     = 0;
        $data['C']['donasi']           = 0;

        $data['I']['count']            = 0;
        $data['I']['sum_amount']       = 0;
        $data['I']['avg_amount']       = 0;
        $data['I']['food_gross_sales'] = 0;
        $data['I']['bvrg_gross_sales'] = 0;
        $data['I']['total_f_b_gross']  = 0;
        $data['I']['discount_food']    = 0;
        $data['I']['discount_bvrg']    = 0;
        $data['I']['total_discount']   = 0;
        $data['I']['food_net_sales']   = 0;
        $data['I']['bvrg_net_sales']   = 0;
        $data['I']['total_f_b_net']    = 0;
        $data['I']['delivery_cost']    = 0;
        $data['I']['others']           = 0;
        $data['I']['restaurant_tax']   = 0;
        $data['I']['transfer_out']     = 0;
        $data['I']['down_payment']     = 0;
        $data['I']['donasi']           = 0;

        $data['total']                     = [];
        $data['total']['count']            = 0;
        $data['total']['sum_amount']       = 0;
        $data['total']['avg_amount']       = 0;
        $data['total']['food_gross_sales'] = 0;
        $data['total']['bvrg_gross_sales'] = 0;
        $data['total']['total_f_b_gross']  = 0;
        $data['total']['discount_food']    = 0;
        $data['total']['discount_bvrg']    = 0;
        $data['total']['total_discount']   = 0;
        $data['total']['food_net_sales']   = 0;
        $data['total']['bvrg_net_sales']   = 0;
        $data['total']['total_f_b_net']    = 0;
        $data['total']['delivery_cost']    = 0;
        $data['total']['others']           = 0;
        $data['total']['restaurant_tax']   = 0;
        $data['total']['transfer_out']     = 0;
        $data['total']['down_payment']     = 0;
        $data['total']['donasi']           = 0;

        $data['payment_methods']                          = $payment_methods;
        $data['D']['payment_method']                      = [];
        $data['C']['payment_method']                      = [];
        $data['I']['payment_method']                      = [];
        $data['total_payment']['payment_method']['D']     = 0;
        $data['total_payment']['payment_method']['C']     = 0;
        $data['total_payment']['payment_method']['I']     = 0;
        $data['total_payment']['payment_method']['total'] = 0;
        $data['total']['payment_method']                  = [];

        $data['C']['void']['count']     = 0;
        $data['D']['void']['count']     = 0;
        $data['I']['void']['count']     = 0;
        $data['total']['void']['count'] = 0;
        $data['C']['void']['value']     = 0;
        $data['D']['void']['value']     = 0;
        $data['I']['void']['value']     = 0;
        $data['total']['void']['value'] = 0;

        $data['third_party'] = [];
        $partners            = $this->morder->getListPartner();
        foreach ($partners as $partner_id => $partner_name) {
            $data['third_party'][$partner_id] = ['C' => 0, 'D' => 0, 'I' => 0, 'total' => 0, 'name' => $partner_name, 'count' => 0];
        }
        $data['third_party']['total'] = ['C' => 0, 'D' => 0, 'I' => 0, 'total' => 0, 'name' => '', 'count' => 0];

        foreach ($orders_void as $order_void) {
            if (isset($data[$order_void->order_type_id])) {
                $data[$order_void->order_type_id]['void']['count'] += 1;
                $data[$order_void->order_type_id]['void']['value'] += $order_void->sub_price;
            }

            $data['total']['void']['count'] += 1;
            $data['total']['void']['value'] += $order_void->sub_price;
        }

        $orders_temp   = [];
        $arr_order_ids = [
            ORDER_TYPE_DELIVERY => [],
            ORDER_TYPE_TAKEAWAY => [],
            ORDER_TYPE_EATIN    => []
        ];

        // Put discount only for discount food
        $data['C']['total_discount'] = $data['C']['discount_food'];
        $data['D']['total_discount'] = $data['D']['discount_food'];
        $data['I']['total_discount'] = $data['I']['discount_food'];
        $data['total']['total_discount'] = $data['total']['discount_food'];

        $food_order_details = [];
        $bvrg_order_details = [];

        foreach ($order_details as $order_detail) {
            if ($order_detail->category_id == 71) {
                $bvrg_order_details[] = $order_detail;
            } else {
                $food_order_details[] = $order_detail;
            }
        }

        foreach ($food_order_details as $order_detail) {
            $price_before_tax                                       = $order_detail->sub_price;
            $price_and_quantity                                     = $price_before_tax * $order_detail->quantity;
            $data[$order_detail->order_type_id]['food_gross_sales'] += $price_and_quantity;
            $data[$order_detail->order_type_id]['total_f_b_gross']  += $price_and_quantity;
            $data[$order_detail->order_type_id]['total_discount']   += 0;
            $data[$order_detail->order_type_id]['food_net_sales']   += $price_and_quantity;
            $data[$order_detail->order_type_id]['total_f_b_net']    += $price_and_quantity;

            $data['total']['food_gross_sales'] += $price_and_quantity;
            $data['total']['total_f_b_gross']  += $price_and_quantity;
            $data['total']['discount_food']    += 0;
            $data['total']['total_discount']   += 0;
            $data['total']['food_net_sales']   += $price_and_quantity;
            $data['total']['total_f_b_net']    += $price_and_quantity;

        }

        foreach ($bvrg_order_details as $order_detail) {
            $price_before_tax                                       = $order_detail->sub_price;
            $price_and_quantity                                     = $price_before_tax * $order_detail->quantity;
            $data[$order_detail->order_type_id]['bvrg_gross_sales'] += $price_and_quantity;
            $data[$order_detail->order_type_id]['total_f_b_gross']  += $price_and_quantity;
            $data[$order_detail->order_type_id]['discount_food']    += 0;
            $data[$order_detail->order_type_id]['total_discount']   += 0;
            $data[$order_detail->order_type_id]['bvrg_net_sales']   += $price_and_quantity;
            $data[$order_detail->order_type_id]['total_f_b_net']    += $price_and_quantity;

            $data['total']['bvrg_gross_sales'] += $price_and_quantity;
            $data['total']['total_f_b_gross']  += $price_and_quantity;
            $data['total']['discount_food']    += 0;
            $data['total']['total_discount']   += 0;
            $data['total']['bvrg_net_sales']   += $price_and_quantity;
            $data['total']['total_f_b_net']    += $price_and_quantity;
        }

        $order_id_previous = 0;
        foreach ($orders as $order) {
            if (!in_array($order->order_id, $arr_order_ids[$order->order_type_id])) {
                $arr_order_ids[$order->order_type_id][] = $order->order_id;
                $data[$order->order_type_id]['count']++;
                $total_bill++;
                $data[$order->order_type_id]['restaurant_tax'] += $order->tax_value;
                $data['total']['restaurant_tax']               += $order->tax_value;
                if ($order->order_type_id == ORDER_TYPE_DELIVERY) {
                    $data['D']['delivery_cost'] += $order->sub_delivery_fee;
                }

                $data[$order->order_type_id]['donasi'] += $order->donation;
                $data['total']['donasi']               += $order->donation;
            }

            $sum_amount                                = $data[$order->order_type_id]['sum_amount'];
            $data[$order->order_type_id]['sum_amount'] = ($sum_amount + $order->sub_total) - $order->discount;
            $total_amount                              += $order->sub_total;

            if (!isset($data['D']['payment_method'][$order->payment_method_id])) {
                $data['D']['payment_method'][$order->payment_method_id] = 0;
            }
            if (!isset($data['C']['payment_method'][$order->payment_method_id])) {
                $data['C']['payment_method'][$order->payment_method_id] = 0;
            }
            if (!isset($data['I']['payment_method'][$order->payment_method_id])) {
                $data['I']['payment_method'][$order->payment_method_id] = 0;
            }
            if (!isset($data['total']['payment_method'][$order->payment_method_id])) {
                $data['total']['payment_method'][$order->payment_method_id] = 0;
            }

            if ($order->order_type_id == ORDER_TYPE_DELIVERY) {
                $data['D']['payment_method'][$order->payment_method_id] += $order->total_payment;
                $data[$order->order_type_id]['avg_amount']                                = $data[$order->order_type_id]['sum_amount'] / $data[$order->order_type_id]['count'];
            } elseif ($order->order_type_id == ORDER_TYPE_TAKEAWAY) {
                $data['C']['payment_method'][$order->payment_method_id] += $order->total_payment;
                $data[$order->order_type_id]['avg_amount']              = round($data[$order->order_type_id]['sum_amount'] / $data[$order->order_type_id]['count'], 2);
            } elseif ($order->order_type_id == ORDER_TYPE_DINEIN) {
                $data['I']['payment_method'][$order->payment_method_id] += $order->total_payment;
                $data[$order->order_type_id]['avg_amount']              = round($data[$order->order_type_id]['sum_amount'] / $data[$order->order_type_id]['count'], 2);
            }

            if (!isset($data['payment_method'][$order->payment_method_id])) {
                $data['payment_method'][$order->payment_method_id] = [];
            }

            if ($order->partner_id != 0) {
                if (!isset($orders_temp[$order->order_id])) {
                    $data['third_party'][$order->partner_id][$order->order_type_id] += $order->total_payment;
                    $data['third_party']['total'][$order->order_type_id]            += $order->total_payment;
                    $data['third_party']['total']['count']                          += 1;
                    $data['third_party'][$order->partner_id]['count']               += 1;
                    $orders_temp[$order->order_id]                                  = [];
                }
            }
            $data['total']['payment_method'][$order->payment_method_id] += $order->total_payment;

            // Put discount only for discount food
            $data[$order->order_type_id]['discount_food']    += $order->discount;
            $data['total']['discount_food']    += $order->discount;

            if ($order->id != $order_id_previous) {
                $data[$order->order_type_id]['food_gross_sales'] -= $order->discount;
                $data[$order->order_type_id]['food_net_sales'] -= $order->discount;
                $data[$order->order_type_id]['total_f_b_gross'] -= $order->discount;
                $data[$order->order_type_id]['total_f_b_net'] -= $order->discount;

                $data['total']['food_gross_sales'] -= $order->discount;
                $data['total']['food_net_sales']   -= $order->discount;
                $data['total']['total_f_b_gross']  -= $order->discount;
                $data['total']['total_f_b_net']  -= $order->discount;
            }
            $order_id_previous = $order->id;

        }

        // Calculation again for
        $data['total']['delivery_cost'] += $data['D']['delivery_cost'];

        foreach ($data['payment_methods'] as $payment_method) {
            $data['total_payment']['payment_method']['D'] += $data['D']['payment_method'][$payment_method->payment_method_id];
            $data['total_payment']['payment_method']['C'] += $data['C']['payment_method'][$payment_method->payment_method_id];
            $data['total_payment']['payment_method']['I'] += $data['I']['payment_method'][$payment_method->payment_method_id];
        }
        $data['total_payment']['payment_method']['total'] = $data['total_payment']['payment_method']['D'] + $data['total_payment']['payment_method']['C'] + $data['total_payment']['payment_method']['I'];


        $data['total']['count']      = $total_bill;
        $data['total']['sum_amount'] = $total_amount;
        if ($total_bill == 0) {
            $data['total']['avg_amount'] = 0;
        } else {
            $data['total']['avg_amount'] = round($total_amount / $total_bill, 2);
        }

        // Reset for restaunrant tax
        /*$data['D']['restaurant_tax'] = $data['total_payment']['payment_method']['D'] - $data['D']['total_f_b_gross'] - $data['D']['delivery_cost'] - $data['D']['donasi'];
        $data['C']['restaurant_tax'] = $data['total_payment']['payment_method']['C'] - $data['C']['total_f_b_gross'] - $data['C']['donasi'];
        $data['I']['restaurant_tax'] = $data['total_payment']['payment_method']['I'] - $data['I']['total_f_b_gross'] - $data['I']['donasi'];*/
        $res['data']   = $data;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'SUMMARY OF SALES';
        $res['base_url_download'] = route('report-summary');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SummaryExport($res), "summary_of_sales{$extensionFile}");
        }

        return view("report.summary", $res);
    }

    public function summary_oc(Request $request)
    {
        $param       = $this->getParamFilter($request);
        $users       = $this->muser->getKeyValue("id", "name");
        $payments    = $this->morder->getReportSummaryOc($param);
        $grand_total = 0;
        $data        = array();

        foreach ($payments as &$payment) {
            if (!isset($data[$payment->payment_method_name])) {
                $data[$payment->payment_method_name]['items'] = array();
                $data[$payment->payment_method_name]['total'] = 0;
            }
            $payment->value                                 = $payment->value;
            $data[$payment->payment_method_name]['items'][] = $payment;
            $data[$payment->payment_method_name]['total']   += $payment->value;

            $grand_total += $payment->value;
        }

        $res['users']       = $users;
        $res['data']        = $data;
        $res['grand_total'] = $grand_total;
        $res['period']      = $param['period'];
        $res['date']        = date("d/m/Y");
        $res['time']        = date("H:i:s");
        $res['layout']      = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'SUMMARY - OC';
        $res['base_url_download'] = route('report-summary-oc');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SummaryOCExport($res), "summary_oc{$extensionFile}");
        }

        return view("report.summary_oc", $res);
    }

    public function summary_void(Request $request)
    {
        set_time_limit(120);
        $param   = $this->getParamFilter($request);
        $orders  = $this->morder->getReportSummaryVoid($param);
        $listsku = $this->morder->getListSku();
        $data    = array();
        $grand   = array("total" => 0, "quantity" => 0);
        foreach ($orders as $order) {
            $order->sub_quantity = 0;
            $order->sub_total    = 0;

            foreach ($order->details as $detail) {
                $detail->price       = before_tax($detail->price);
                $order->sub_quantity += $detail->quantity;
                $order->sub_total    += $detail->quantity * $detail->price;
                $grand['quantity']   += $detail->quantity;
                $grand['total']      += $detail->quantity * $detail->price;
            }

            $date          = date("d/m/Y", strtotime($order->created_date));
            $data[$date][] = $order;
        }
        $res['order_types'] = config("admin.order_types");
        $res['data']        = $data;
        $res['grand']       = $grand;
        $res['listsku']     = $listsku;
        $res['period']      = $param['period'];
        $res['date']        = date("d/m/Y");
        $res['time']        = date("H:i:s");
        $res['layout']      = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'SUMMARY VOID ITEM & BILL';
        $res['base_url_download'] = route('report-summary-void');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SummaryVoidExport($res), "summary_void{$extensionFile}");
        }

        return view("report.summary_void", $res);
    }

    public function speed_service_takeaway(Request $request)
    {
        $param                  = $this->getParamFilter($request);
        $param['order_type_id'] = array(ORDER_TYPE_TAKEAWAY, ORDER_TYPE_EATIN);
        $orders                 = $this->morder->getReportSpeedLog($param);
        $arr_time               = array();
        $arr_total              = array();
        // Get dynamic from to time
        foreach ($orders as $order) {
            $hour = date("H", strtotime($order->created_date));
            if (!isset($arr_time[$hour])) {
                $arr_time[$hour]['total']          = 0;
                $arr_time[$hour]['maketime']       = array(1 => 0, 2 => 0, 3 => 0);  // 1 : <= 60s , 2 <= 120s, 3 > 120s
                $arr_time[$hour]['productiontime'] = array(1 => 0, 2 => 0);
            }
        }

        $arr_total['total']          = 0;
        $arr_total['maketime']       = array(1 => 0, 2 => 0, 3 => 0);
        $arr_total['productiontime'] = array(1 => 0, 2 => 0);

        foreach ($orders as $order) {
            $hour = date("H", strtotime($order->created_date));
            $h    = ($hour > 1 && $hour < 9) ? "09" : $hour;

            $maketime       = $order->cooked_time ? strtotime($order->cooked_time) - strtotime($order->kds_time) : 0;
            $productiontime = $order->checkout_time ? strtotime($order->checkout_time) - strtotime($order->kds_time) : 0;

            $arr_time[$h]['total']++;
            $arr_total['total']++;

            if ($maketime <= 60) {
                $arr_time[$h]['maketime'][1]++;
                $arr_total['maketime'][1]++;
            } elseif ($maketime <= 180) {
                $arr_time[$h]['maketime'][2]++;
                $arr_total['maketime'][2]++;
            } else {
                $arr_time[$h]['maketime'][3]++;
                $arr_total['maketime'][3]++;
            }

            if ($productiontime <= 600) {
                $arr_time[$h]['productiontime'][1]++;
                $arr_total['productiontime'][1]++;
            } else {
                $arr_time[$h]['productiontime'][2]++;
                $arr_total['productiontime'][2]++;
            }
        }

        $res['data']   = $arr_time;
        $res['total']  = $arr_total;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'Speed of Service Log - Takeaway';
        $res['base_url_download'] = route('report-takeaway');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SpeedServiceTakeawayExport($res), "speed_service_takeaway{$extensionFile}");
        }

        return view("report.speed_service_takeaway", $res);
    }

    public function speed_service_delivery(Request $request)
    {
        $param                  = $this->getParamFilter($request);
        $param['order_type_id'] = array(ORDER_TYPE_DELIVERY);
        $orders                 = $this->morder->getReportSpeedLog($param);

        $arr_time  = array();
        $arr_total = array();
        // Get dynamic from to time
        foreach ($orders as $order) {
            $hour = date("H", strtotime($order->created_date));
            $hour = ($hour > 1 && $hour < 9) ? "09" : $hour;
            if (!isset($arr_time[$hour])) {
                $arr_time[$hour]['total']          = 0;
                $arr_time[$hour]['maketime']       = array(1 => 0, 2 => 0, 3 => 0);  // 1 : <= 60s , 2 <= 120s, 3 > 120s
                $arr_time[$hour]['productiontime'] = array(1 => 0, 2 => 0);
                $arr_time[$hour]['instoretime']    = array(1 => 0, 2 => 0);       // 1:  < 600s , 2 > 600s
                $arr_time[$hour]['deliverytime']   = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);  // 1: < 25m , 2 : > 25 m, 3: <30m, 4: >30m , 5: < 50m, 6: > 50m
            }
        }

        $arr_total['total']          = 0;
        $arr_total['maketime']       = array(1 => 0, 2 => 0, 3 => 0);
        $arr_total['productiontime'] = array(1 => 0, 2 => 0);
        $arr_total['instoretime']    = array(1 => 0, 2 => 0);
        $arr_total['deliverytime']   = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);
        foreach ($orders as $order) {
            $hour = date("H", strtotime($order->created_date));
            $h    = ($hour > 1 && $hour < 9) ? "09" : $hour;

            $maketime       = $order->cooked_time ? strtotime($order->cooked_time) - strtotime($order->kds_time) : 0;
            $productiontime = $order->checkout_time ? strtotime($order->checkout_time) - strtotime($order->kds_time) : 0;
            $instore        = $order->delivering_time ? strtotime($order->delivering_time) - strtotime($order->kds_time) : 0;

            if (empty($order->delivered_time)) {
                $dtime = intval((strtotime($order->finished_time) - strtotime($order->delivering_time)));
            } else {
                $dtime = intval((strtotime($order->delivered_time) - strtotime($order->delivering_time)));
            }
            $drivertime = strpos($order->delivery_together, ",") !== false ? intval($dtime / 2.5) : intval($dtime / 2);
            $delivery   = $instore + $drivertime;

            $arr_time[$h]['total']++;
            $arr_total['total']++;

            if ($maketime <= ONE_MINUTE) {
                $arr_time[$h]['maketime'][1]++;
                $arr_total['maketime'][1]++;
            }
            if ($maketime <= THREE_MINUTES) {
                $arr_time[$h]['maketime'][2]++;
                $arr_total['maketime'][2]++;
            } else {
                $arr_time[$h]['maketime'][3]++;
                $arr_total['maketime'][3]++;
            }

            if ($productiontime <= TEN_MINUTES) {
                $arr_time[$h]['productiontime'][1]++;
                $arr_total['productiontime'][1]++;
            } else {
                $arr_time[$h]['productiontime'][2]++;
                $arr_total['productiontime'][2]++;
            }

            if ($instore <= TEN_MINUTES) {
                $arr_time[$h]['instoretime'][1]++;
                $arr_total['instoretime'][1]++;
            } else {
                $arr_time[$h]['instoretime'][2]++;
                $arr_total['instoretime'][2]++;
            }

            if ($delivery < THIRTY_MINUTES) {
                $arr_time[$h]['deliverytime'][1]++;
                $arr_total['deliverytime'][1]++;
            }
            if ($delivery >= THIRTY_MINUTES && $delivery <= FOURTYFIVE_MINUTES) {
                $arr_time[$h]['deliverytime'][2]++;
                $arr_total['deliverytime'][2]++;
            }
            if ($delivery > FOURTYFIVE_MINUTES) {
                $arr_time[$h]['deliverytime'][3]++;
                $arr_total['deliverytime'][3]++;
            }
        }

        $res['data']   = $arr_time;
        $res['total']  = $arr_total;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'Speed of Service Log - Delivery';
        $res['base_url_download'] = route('report-delivery');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SpeedServiceDelivertExport($res), "speed_service_delivery{$extensionFile}");
        }

        return view("report.speed_service_delivery", $res);
    }

    /**
     * report for speed service swipe done
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function speed_service_swipedone(Request $request)
    {
        $param                  = $this->getParamFilter($request);
        $param['order_type_id'] = array(ORDER_TYPE_DELIVERY);
        $orders                 = $this->morder->getReportSwipeDone($param);

        $arr_time  = array();
        $arr_total = array();
        // Get dynamic from to time
        foreach ($orders as $order) {
            $hour = date("H", strtotime($order->created_date));
            $hour = ($hour > 1 && $hour < 9) ? "09" : $hour;
            if (!isset($arr_time[$hour])) {
                $arr_time[$hour]['total']               = 0;
                $arr_time[$hour]['driver_tracker']      = 0;
                $arr_time[$hour]['not_driver_tracker']  = 0;
                $arr_time[$hour]['under_30mins_driver_tracker']     = 0;
            }
        }

        $arr_total['total']                 = 0;
        $arr_total['driver_tracker']        = 0;
        $arr_total['not_driver_tracker']    = 0;
        $arr_total['under_30mins_driver_tracker']   = 0;

        foreach ($orders as $order) {
            $hour = date("H", strtotime($order->created_date));
            $h    = ($hour > 1 && $hour < 9) ? "09" : $hour;

            if (empty($order->delivered_time)) {
                $arr_time[$h]['not_driver_tracker']++;
                $arr_total['not_driver_tracker']++;
            } else {
                $dtime = intval((strtotime($order->delivered_time) - strtotime($order->delivering_time)));

                $arr_time[$h]['driver_tracker']++;
                $arr_total['driver_tracker']++;

                $drivertime = strpos($order->delivery_together, ",") !== false ? intval($dtime / 2.5) : intval($dtime / 2);

                if ($drivertime < THIRTY_MINUTES) {
                    $arr_time[$h]['under_30mins_driver_tracker']++;
                    $arr_total['under_30mins_driver_tracker']++;
                }

            }

            $arr_time[$h]['total']++;
            $arr_total['total']++;

        }

        $res['data']   = $arr_time;
        $res['total']  = $arr_total;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'Speed of Service Log - Swipe done';
        $res['base_url_download'] = route('report-swipedone');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SpeedServiceSwipedoneExport($res), "speed_service_swipedone{$extensionFile}");
        }

        return view("report.speed_service_swipedone", $res);
    }

    /**
     * summary usage report
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function summary_usage(Request $request)
    {
        $param  = $this->getParamFilter($request);
        $orders = $this->viewSummaryMaterialUsageRep->getList($param);

        $arr_total  = array();

        foreach ($orders as $order) {
            $code   = $order->material_code;
            $name   = $order->material_name;
            $usage  = $order->usage;
            $unit   = $order->usage_unit;

            $arr_total[$code]['usage']  = isset($arr_total[$code]['usage']) ? $arr_total[$code]['usage'] + $usage : $usage;
            $arr_total[$code]['name']   = $name;
            $arr_total[$code]['unit']   = $unit;
        }

        ksort($arr_total);

        $res['data']   = $arr_total;
        $res['total']  = $arr_total;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'Summary Usage Report';
        $res['base_url_download'] = route('summary-usage');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SummaryUsageExport($res), "summary_usage{$extensionFile}");
        }

        return view("report.summary_usage", $res);
    }

    public function payment_detail(Request $request)
    {
        $param           = $this->getParamFilter($request);
        $items           = $this->morder->getReportPaymentDetail($param);
        $data            = $this->getData();
        $payment_methods = [];
        $orders          = [];
        $grand_total     = 0;
        foreach ($items as $item) {
            if (!isset($payment_methods[$item->payment_method_id])) {
                $payment_methods[$item->payment_method_id] = [
                    'name'             => $item->payment_method_name,
                    'sub_total_format' => 0
                ];
                $orders[$item->payment_method_id]          = [];
            }
            $item->date                                                    = date('Y-m-d', strtotime($item->created_date));
            $item->time                                                    = date('H:i:s', strtotime($item->created_date));
            $orders[$item->payment_method_id][]                            = $item;
            $payment_methods[$item->payment_method_id]['sub_total_format'] += $item->total_payment;
            $item->amount                                                  = number_format($item->total_payment);
        }
        foreach ($payment_methods as &$payment_method) {
            $grand_total                        += $payment_method['sub_total_format'];
            $payment_method['sub_total_format'] = number_format($payment_method['sub_total_format']);
        }
        $data['orders']          = $orders;
        $data['payment_methods'] = $payment_methods;
        $data['grand_total']     = number_format($grand_total);
        $data['period']          = $param['period'];
        $data['date']            = date("d/m/Y");
        $data['time']            = date("H:i:s");
        $data['layout']          = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget']            = $this->widget;
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = 'PAYMENT DETAIL';
        $data['base_url_download'] = route('report-payment-detail');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new PaymentDetailExport($data), "payment_detail{$extensionFile}");
        }

        return view("report.payment_detail", $data);
    }

    public function payment_type_by_cashier(Request $request)
    {
        $param           = $this->getParamFilter($request);
        $items           = $this->morder->getReportByCashier($param);
        $data            = $this->getData();
        $cashiers        = [];
        $payment_methods = [];
        $grand_total     = 0;
        foreach ($items as $item) {
            if (!isset($cashiers[$item->encash_by])) {
                $cashiers[$item->encash_by] = [
                    'name'             => $item->cashier_name,
                    'count'            => 0,
                    'sub_total_format' => 0,
                    'payment_methods'  => []
                ];
            }
            if (!isset($cashiers[$item->encash_by]['payment_methods'][$item->payment_method_id])) {
                $cashiers[$item->encash_by]['payment_methods'][$item->payment_method_id] = [
                    'name'             => $item->payment_method_name,
                    'count'            => 0,
                    'sub_total_format' => 0
                ];
            }

            $cashiers[$item->encash_by]['sub_total_format']                                              += $item->total_payment;
            $cashiers[$item->encash_by]['count']                                                         += 1;
            $cashiers[$item->encash_by]['payment_methods'][$item->payment_method_id]['sub_total_format'] += $item->total_payment;
            $cashiers[$item->encash_by]['payment_methods'][$item->payment_method_id]['count']            += 1;
            $grand_total                                                                                 += $item->total_payment;
        }

        foreach ($cashiers as &$cashier) {
            $cashier['sub_total_format'] = number_format($cashier['sub_total_format']);
            foreach ($cashier['payment_methods'] as $key => $payment_methods) {
                $cashier['payment_methods'][$key]['sub_total_format'] = number_format($payment_methods['sub_total_format']);
            }
        }
        $data['cashiers']        = $cashiers;
        $data['payment_methods'] = $payment_methods;
        $data['grand_total']     = number_format($grand_total);
        $data['period']          = $param['period'];
        $data['date']            = date("d/m/Y");
        $data['time']            = date("H:i:s");
        $data['layout']          = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget']            = $this->widget;
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = 'PAYMENT TYPE BY CASHIER';
        $data['base_url_download'] = route('report-payment-type-by-cashier');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new PaymentByCashierExport($data), "payment_by_cashier{$extensionFile}");
        }

        return view("report.payment_by_cashier", $data);
    }

    public function customer_ordering(Request $request)
    {
        $param  = $this->getParamFilter($request);
        $result = $this->morder->getCustomerOrdering($param);

        $data = [
            'delivery_order' => [],
            'take_away'      => []
        ];

        foreach ($result as $row) {
            // format phone number
            $row['phone'] = $this->formatPhoneNumber($row['phone']);

            if ($row['order_type_id'] == ORDER_TYPE_DELIVERY) {
                $data['delivery_order'][] = $row;
            } else {
                $data['take_away'][] = $row;
            }
        }
        $res['data']   = $data;
        $res['total']  = count($result);
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'CUSTOMER ORDERING';
        $res['base_url_download'] = route('report-customer-ordering');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new CustomerOrderingExport($res), "customer_ordering{$extensionFile}");
        }

        return view("report.customer_ordering", $res);
    }

    public function net_sales_and_bill_by_hours(Request $request)
    {
        $param  = $this->getParamFilter($request);
        $orders = $this->morder->getReportMixByHour($param);

        $total_sales = 0;
        $total_qty   = 0;
        $list        = array();
        // Get dynamic from to time
        foreach ($orders as $order) {
            $hour = date("H", strtotime($order->created_date));
            if (!isset($arr_time[$hour])) {
                $list[$hour][ORDER_TYPE_EATIN]['qty']      = 0;
                $list[$hour][ORDER_TYPE_EATIN]['sales']    = 0;
                $list[$hour][ORDER_TYPE_DELIVERY]['qty']   = 0;
                $list[$hour][ORDER_TYPE_DELIVERY]['sales'] = 0;
                $list[$hour][ORDER_TYPE_TAKEAWAY]['qty']   = 0;
                $list[$hour][ORDER_TYPE_TAKEAWAY]['sales'] = 0;
                $list[$hour]['total']['qty']               = 0;
                $list[$hour]['total']['sales']             = 0;
            }
        }

        foreach ($orders as $order) {
            $h             = date("H", strtotime($order->created_date));
            $h             = strlen($h) == 1 ? "0$h" : $h;
            $order_type_id = $order->order_type_id;
            if (isset($list[$h])) {
                $list[$h][$order_type_id]['qty']   += 1;
                $list[$h][$order_type_id]['sales'] += $order->amount;
                $list[$h]['total']['qty']          += 1;
                $list[$h]['total']['sales']        += $order->amount;
                $total_sales                       += $order->amount;
                $total_qty                         += 1;
            }
        }

        $group = array(
            "lunch"  => array(
                "items" => array(),
                "I"     => array("qty" => 0, "sales" => 0),
                "D"     => array("qty" => 0, "sales" => 0),
                "C"     => array("qty" => 0, "sales" => 0),
                "total" => array("qty" => 0, "sales" => 0, "percent_sales" => 0, "percent_qty" => 0)
            ),
            "dinner" => array(
                "items" => array(),
                "I"     => array("qty" => 0, "sales" => 0),
                "D"     => array("qty" => 0, "sales" => 0),
                "C"     => array("qty" => 0, "sales" => 0),
                "total" => array("qty" => 0, "sales" => 0, "percent_sales" => 0, "percent_qty" => 0)
            ),
            'total'  => array(
                "I"     => array("qty" => 0, "sales" => 0),
                "D"     => array("qty" => 0, "sales" => 0),
                "C"     => array("qty" => 0, "sales" => 0),
                "total" => array("qty" => 0, "sales" => 0, "percent_sales" => 0, "percent_qty" => 0)
            )
        );
        foreach ($list as $hour => $itemlist) {
            $key = intval($hour) >= 9 && intval($hour) <= 13 ? "lunch" : "dinner";

            $itemlist['total']['percent_qty']      = division($itemlist['total']['qty'], $total_qty, 4) * 100;
            $itemlist['total']['percent_sales']    = division($itemlist['total']['sales'], $total_sales, 4) * 100;
            $group[$key]['items'][$hour]           = $itemlist;
            $group[$key]['I']['qty']               += $itemlist['I']['qty'];
            $group[$key]['I']['sales']             += $itemlist['I']['sales'];
            $group[$key]['D']['qty']               += $itemlist['D']['qty'];
            $group[$key]['D']['sales']             += $itemlist['D']['sales'];
            $group[$key]['C']['qty']               += $itemlist['C']['qty'];
            $group[$key]['C']['sales']             += $itemlist['C']['sales'];
            $group[$key]['total']['qty']           += $itemlist['total']['qty'];
            $group[$key]['total']['sales']         += $itemlist['total']['sales'];
            $group[$key]['total']['percent_qty']   = division($group[$key]['total']['qty'], $total_qty, 4) * 100;
            $group[$key]['total']['percent_sales'] = division($group[$key]['total']['sales'], $total_sales, 4) * 100;

            $group['total']['I']['qty']               += $itemlist['I']['qty'];
            $group['total']['I']['sales']             += $itemlist['I']['sales'];
            $group['total']['D']['qty']               += $itemlist['D']['qty'];
            $group['total']['D']['sales']             += $itemlist['D']['sales'];
            $group['total']['C']['qty']               += $itemlist['C']['qty'];
            $group['total']['C']['sales']             += $itemlist['C']['sales'];
            $group['total']['total']['qty']           += $itemlist['total']['qty'];
            $group['total']['total']['sales']         += $itemlist['total']['sales'];
            $group['total']['total']['percent_qty']   = division($group['total']['total']['qty'], $total_qty, 4) * 100;
            $group['total']['total']['percent_sales'] = division($group['total']['total']['sales'], $total_sales, 4) * 100;
        }
        $res['group']  = $group;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'Net Sales and Bill by Hours';
        $res['base_url_download'] = route('report-net-sales-and-bill-by-hours');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new NetSalesAndBillByHoursExport($res), "net_sales_and_bill_by_hours{$extensionFile}");
        }

        return view("report.net_sales_and_bill_by_hours", $res);
    }

    public function setoran_tunai_bank(Request $request)
    {
        $param = $this->getParamFilter($request);
        $data  = $this->morder->getReportCash($param);
        $total = 0;

        foreach ($data as $item) {
            $total += $item->amount;
        }

        $res['data']   = $data;
        $res['total']  = $total;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'Setoran Tunai Bank (STB)';
        $res['base_url_download'] = route('report-setoran-tunai-bank');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new SetoranTunaiBankExport($res), "setoran_tunai_bank{$extensionFile}");
        }

        return view("report.setoran_tunai_bank", $res);
    }

    public function bill_by_poding(Request $request)
    {
        $param = $this->getParamFilter($request);
        $data  = $this->morder->getReportBillByPoding($param);
        $grand = array('total_bill' => 0, "total_amount" => 0, "percent" => 100);

        foreach ($data as $item) {
            $grand['total_bill']   += $item->total_bill;
            $grand['total_amount'] += $item->total_amount;
        }

        foreach ($data as $item) {
            $item->percent = division($item->total_amount, $grand['total_amount'], 4) * 100;
        }

        $res['data']   = $data;
        $res['grand']  = $grand;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'BILL BY PODING';
        $res['base_url_download'] = route('report-bill-by-poding');

        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new BillByPodingExport($res), "bill_by_poding{$extensionFile}");
        }

        return view("report.bill_by_poding", $res);
    }

    public function donation_detail(Request $request) {
        $param = $this->getParamFilter($request);
        $data = $this->morder->getReportDonationDetail($param);
        $total = 0;
        foreach ($data as &$item) {
            if(empty($item->user_name)) {
                $item->user_name = 'Online Payment';
            }
            $item->before_amount = $item->amount - $item->donation;
            $total += $item->donation;
        }

        $res['data']   = $data;
        $res['total']  = $total;
        $res['period'] = $param['period'];
        $res['date'] = date("d/m/Y");
        $res['time'] = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        // Common information
        $res['widget'] = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name'] = 'Donation Detail';
        $res['base_url_download'] = route('report-donation-detail');
        if (!empty($param['export_type'])) {
            $res['is_exported_file'] = true;
            $extensionFile = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new DonationDetailExport($res), "donation_detail{$extensionFile}");
        }
        return view("report.donation_detail", $res);
    }

    public function history_by_bill(Request $request)
    {
        $param  = $this->getParamFilter($request);
        $orders = $this->morder->getReportHistoryByBill($param);

        $res['data']   = $orders;
        $res['period'] = $param['period'];
        $res['date']   = date("d/m/Y");
        $res['time']   = date("H:i:s");
        $res['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $res['widget']            = $this->widget;
        $res['outletInformation'] = $this->outletInformation;
        $res['name']              = 'History By Bill';
        $res['base_url_download'] = route('report-history-by-bill');

        if (!empty($param['export_type'])) {
            // TODO
            $data_export = [];
            foreach ($orders as $key => $item) {
                $total_tax         = array_get($item, 'amount', 0);
                $total             = before_tax($total_tax); //= subtotal + delivery (exclude tax)
                $delivery_cost_tax = array_get($item, 'delivery_fee', 0);
                $delivery_cost     = before_tax($delivery_cost_tax);
                $subtotal          = $item['is_oc'] == 1 ? $item['sub_total'] : $total - $delivery_cost;
                $created_date      = date("d/m/y H:i", strtotime($item['created_date']));
                $order_details     = array_get($item, 'order_details', []);
                $order_payments    = array_get($item, 'order_payments', []);
                $menu_detailed     = [];
                $payment_detailed  = [];

                foreach ($order_details as $_key => $detail) {
                    $children        = array_get($detail, 'children', []);
                    $price           = before_tax($detail['price'] ?? 0);
                    $menu_detailed[] = [
                        array_get($detail, 'menu_name'),
                        array_get($detail, 'quantity'),
                        number_format($price)
                    ];
                    foreach ($children as $k => $v) {
                        $price           = 0;
                        $menu_detailed[] = [
                            '&nbsp;&nbsp;:-' . array_get($v, 'menu_name'),
                            array_get($v, 'quantity'),
                            number_format($price)
                        ];
                    }
                }
                foreach ($order_payments as $row) {
                    $payment_detailed[] = [
                        array_get($row, 'payment_method_name'),
                        '',
                        number_format(array_get($row, 'value', '0'))
                    ];
                }
                $data_export[] = [
                    'bill'           => ['Bill#   ' . $item['number'], '', array_get($item, 'order_type.name', '')],
                    'cashier'        => ['Cashier  ', '', array_get($item, 'user_encash.name', ''), $created_date],
                    'detailed'       => $menu_detailed,
                    'sub_total'      => ['Sub Total', '', number_format($subtotal)],
                    'delivery_cost'  => [$delivery_cost > 0 ? 'Delivery cost' : '', '', $delivery_cost > 0 ? number_format($delivery_cost) : ''],
                    'Restaurant_tax' => ['Restaurant tax', '', number_format($total_tax - $total)],
                    'payment'        => $payment_detailed,
                    'total_payment'  => ['Total Payment', '', number_format($total_tax)]
                ];
            }

            $res['data_export']      = $data_export;
            $res['is_exported_file'] = true;
            $extensionFile           = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new HistoryByBillExport($res), "history_by_bill{$extensionFile}");
        }

        return view("report.history_by_bill", $res);
    }

    public function print_bill(Request $request)
    {
        $jsondata = $request->post("data");
        $ip       = $request->post("ip");
//        $ip = "192.168.1.222";

        $object         = json_decode($jsondata);
        $report         = !empty($object->data->report) ? $object->data->report : null;
        $data['result'] = false;

        $length = 48;

        try {
            $connector = new NetworkPrintConnector($ip);
            $printer   = new Printer($connector);
            for ($i = 0; $i < 2; $i++) {
                $img = EscposImage::load(public_path() . "/images/logo-header-bill.png");
                $printer->selectPrintMode(Printer::MODE_FONT_B);
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->graphics($img);
                $printer->feed();
                $printer->initialize();
                $printer->text("Shop No: " . ConfigHelp::get('outlet_code'));
                $printer->feed();
                $printer->text("END OF DAY REPORT:");
                $printer->feed();
                $printer->text(str_pad("", $length, "-", STR_PAD_BOTH));
                $printer->feed();
                $printer->text("End of Day Count:");
                $printer->feed();
                $printer->text("Business Date: " . date('Y-m-d'));
                $printer->feed();
                $printer->text(str_pad("", $length, "-", STR_PAD_BOTH));
                $printer->feed();

                $printer->setUnderline(Printer::UNDERLINE_SINGLE);
                $printer->text("PAYMENT DETAIL:");
                $printer->setUnderline(Printer::UNDERLINE_NONE);
                $printer->feed();

                if (!empty($report->payment_details)) {
                    foreach ($report->payment_details as $payment) {
                        $text = "$payment->payment_method_name ($payment->quantity)";
                        $printer->text($text);
                        $printer->text(str_pad(number_format($payment->value), $length - strlen($text), " ", STR_PAD_LEFT));
                        $printer->feed();
                    }
                }

                $printer->text("TOTAL PAYMENT:");
                $printer->text(str_pad(number_format($report->payment_total), $length - 14, " ", STR_PAD_LEFT));
                $printer->feed();
                $printer->feed();

                $printer->setUnderline(Printer::UNDERLINE_SINGLE);
                $printer->text("O.C DETAIL:");
                $printer->setUnderline(Printer::UNDERLINE_NONE);
                $printer->feed();

                if (!empty($report->oc_details)) {
                    foreach ($report->oc_details as $payment) {
                        $text = "$payment->payment_method_name ($payment->quantity)";
                        $printer->text($text);
                        $printer->text(str_pad(number_format($payment->value), $length - strlen($text), " ", STR_PAD_LEFT));
                        $printer->feed();
                    }
                }

                $printer->text("TOTAL O.C:");
                $printer->text(str_pad(number_format($report->oc_total), $length - 10, " ", STR_PAD_LEFT));
                $printer->feed();

                $printer->text(str_pad("", $length, "-", STR_PAD_BOTH));
                $printer->feed();
                $printer->setTextSize(2, 2);
                $printer->text("Grand Total");
                $printer->setTextSize(1, 1);
                $printer->text(str_pad(number_format($report->grand_total), $length - (11 * 2), " ", STR_PAD_LEFT));
                $printer->feed();
                $printer->text(str_pad("", $length, "-", STR_PAD_BOTH));
                $printer->feed();
                $printer->text("End of Report");
                $printer->feed();

                $printer->text("Read By:" . auth("admin")->user()->name);
                $printer->feed();
                $printer->feed();

                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->text(date("Y-m-d H:i:s"));

                $printer->feed();
                $printer->feed();

                $printer->cut();
            }
            $printer->close();
            $data['result'] = true;
        } catch (Exception $exc) {
            $data['msg'] = "Can not connect to printer! " . $exc->getMessage();
        }

        die(json_encode($data));
    }

    public function check_eod(Request $request)
    {
        $data['result'] = false;
        $data['msg']    = "ERROR";

        $dateTimeEOD = PosHelper::getDateTimeEOD($request->get("from_time"));
        $from_time   = $dateTimeEOD['start_date'];
        $to_time     = $dateTimeEOD['end_date'];

        $result = PosHelper::post("report/check_eod", array("from_time" => $from_time, "to_time" => $to_time, "username" => auth("admin")->user()->username));

        if ($result['result'] == true) {
            $res                = PosHelper::post("report/payment_eod", array("from_time" => $from_time, "to_time" => $to_time));
            $res['report_type'] = $result['data']['reports'];
            $res['date']        = date("Y-m-d", strtotime($from_time));
            $res['from_time']   = $from_time;
            $res['to_time']     = $to_time;
            $res['response']    = json_encode($res);
            $res['printers']    = $this->morder->getListPrinter();
            $res['read_by']     = auth("admin")->user()->name;
            $result['html']     = view("report.eod", $res)->render();
        }

        die(json_encode($result));
    }

    public function push_ftp(Request $request)
    {
        set_time_limit(120);
        $params['report_type'] = $request->input("report_type");
        $params['date']        = $request->input("date");
        $params['from_time']   = $request->input("from_time");
        $params['to_time']     = $request->input("to_time");
        $result                = PosHelper::post("report/push_ftp", $params);

        die(json_encode($result));
    }

    public function finish_eod(Request $request)
    {
        $rules  = [
            'from_time'  => 'required',
            'to_time'    => 'required',
            'path_file'  => 'required',
            'fpt_folder' => 'required'
        ];
        $errMgs = $this->validateRequest($request->all(), $rules);
        if (!empty($errMgs)) {
            return json_encode([
                "status"  => false,
                'result'  => null,
                'message' => $errMgs
            ]);
        }
    
        $from_time       = $request->get("from_time");
        $to_time         = $request->get("to_time");
        $path_folder     = $request->get("path_file");
        $destination_ftp = $request->get("fpt_folder");
        $hasSentDRS      = intval(ConfigHelp::get("has_sent_drs"));
        
        $response   = [
            "status"  => true,
            'result'  => '',
            'message' => trans('message.push_ftp_eod_successfully')
        ];

        try {
            Log::info('Push FTP report', $request->all());

            // Create zip file
            $file_name = PosHelper::get_name_file_ftp($from_time);;
            $path_file_zip          = $path_folder . DIRECTORY_SEPARATOR . $file_name;
            $files                  = glob($path_folder . '/*.csv');
            $path_download          = route('download_eod_csv') . "?path=" . $path_folder . '&file_name=' . $file_name;
            $destination_ftp_server = $destination_ftp . DIRECTORY_SEPARATOR . $file_name;
            \Zipper::make($path_file_zip)->add($files)->close();
            // Sending FTP
            $result = PosHelper::push_fpt($destination_ftp_server, $path_file_zip, FTP_HOST, FTP_PORT, FTP_TIMEOUT, FTP_EOD_USER_NAME, FTP_EOD_PASSWORD);

            $this->meodhistory->insert(array(
                "admin_user_id"   => auth("admin")->user()->id,
                "payment_data"    => null,
                "path"            => $path_folder,
                "file_name"       => $file_name,
                "is_sent_ftp"     => $result ? STATUS_ACTIVE : STATUS_INACTIVE,
                "destination_ftp" => $destination_ftp,
                "start_date"      => $from_time,
                "end_date"        => $to_time
            ));
            $response['result'] = ['url_download' => $path_download];
            if ($result) {
                if ($hasSentDRS) {
                    $response['message'] .= $this->sendFPTDRS($from_time, $to_time);
                }
            } else {
                $response['status']  = false;
                $response['message'] = trans('message.connect_ftp_eod_failed');
            }

            if (!$result) {
                $response['status']  = false;
                $response['message'] = 'Connect to FTP failed!';
            }
            // Automatic shut down server after 1 min
            //exec("shutdown -h +1");
        } catch (\Exception $ex) {
            $response['status']  = false;
            $response['message'] = 'Push FTP report Failed, please try it again!';
            Log::error($ex->getMessage() . ' At ' . $ex->getFile() . '[' . $ex->getLine() . ']', $request->all());
        }

        return json_encode($response);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function report_pizza_size(Request $request)
    {
        $jumbo        = array();
        $regular      = array();
        $personal     = array();
        $sum          = 0;
        $sum_jumbo    = 0;
        $sum_regular  = 0;
        $sum_personal = 0;

        $param = $this->getParamFilter($request);
        $items = $this->morder->getReportPizzaSize($param);

        $this->countPizzaSize($items, $sum, $sum_jumbo, $sum_regular, $sum_personal);
        $jumbo['qty']           = $sum_jumbo;
        $jumbo['percentage']    = PosHelper::division($sum_jumbo * 100, $sum);
        $regular['qty']         = $sum_regular;
        $regular['percentage']  = PosHelper::division($sum_regular * 100, $sum);
        $personal['qty']        = $sum_personal;
        $personal['percentage'] = PosHelper::division($sum_personal * 100, $sum);

        $data['jumbo']          = $jumbo;
        $data['regular']        = $regular;
        $data['personal']       = $personal;
        $data['sum_pizza_size'] = $sum;
        $data['period']         = $param['period'];
        $data['date']           = date("d/m/Y");
        $data['time']           = date("H:i:s");
        $data['layout']         = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget']            = $this->widget;
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = 'PIZZA SIZE';
        $data['base_url_download'] = route('report-pizza-size');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new PizzaSizeExport($data), "pizza_size{$extensionFile}");
        }

        return view("report.pizza_size", $data);
    }

    /**
     * @param array $arrs
     * @param int $sum
     * @param int $jumbo
     * @param int $regular
     * @param int $personal
     */
    protected function countPizzaSize($arrs = [], &$sum = 0, &$jumbo = 0, &$regular = 0, &$personal = 0)
    {
        foreach ($arrs as $item) {
            if ($item->addon_id == JUMBO) {
                $jumbo++;
                $sum++;
            } elseif ($item->addon_id == REGULAR) {
                $regular++;
                $sum++;
            } elseif ($item->addon_id == PERSONAL) {
                $personal++;
                $sum++;
            }
        }
    }

    public function sendFPTDRS($from_date, $to_date)
    {
        $subDir       = date("d", strtotime($from_date));
        $from_date    = date("Y-m-d H:i:s", strtotime($from_date));
        $to_date      = date("Y-m-d H:i:s", strtotime($to_date));

        $stock_opnames = $this->stockOpnameRep->getByPeriod($from_date, $to_date);
        $receives      = $this->receiveRep->getByPeriod($from_date, $to_date);
        $returns       = $this->returnRep->getByPeriod($from_date, $to_date);
        $transfers     = $this->transferRep->getByPeriod($from_date, $to_date);
        try {
            $file = $this->viewDailyReceivingSummaryRep->zipFileTransaction($this->storeCode, $stock_opnames, $receives, $returns, $transfers, $from_date);
            if ($file) {
                // Sending FTP
                Log::info("Push FTP DRS: ". $file['file_name']);
                $result = PosHelper::push_fpt(
                    FTP_INVENTORY_FOLDER_REPORT . '/' . $subDir . '/' . $file['file_name'],
                    $file['path'] . '/' . $file['file_name'],
                    FTP_HOST,
                    FTP_PORT,
                    FTP_TIMEOUT,
                    FTP_EOD_USER_NAME,
                    FTP_EOD_PASSWORD
                );
                $mgs = $result ? trans('message.push_ftp_drs_successfully') : trans('message.connect_ftp_eod_failed');
            } else {
                $mgs = trans('message.not_found_drs_data');
            }

        } catch (\Exception $ex) {
            Log::error("Send DRS Failed: " . $ex->getMessage() . ' At ' . $ex->getFile() . '[' . $ex->getLine() . ']');
            $mgs = trans('message.push_ftp_drs_failed');
        }

        return $mgs;
    }
}
