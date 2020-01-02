<?php

namespace App\Admin\Controllers\Inventory;

use App\Admin\Controllers\BaseController;
use App\Helpers\ConfigHelp;
use App\Helpers\FileHelper;
use App\Helpers\PosHelper;
use App\Repository\Inventory\{CommonRepository,
    ViewDailyReceivingRepository,
    ViewDailyReceivingSummaryRepository,
    TransferOrderDetailRepository,
    StockOpnameRepository,
    PurchaseOrderRepository,
    ReceiveOrderRepository,
    ReturnOrderRepository,
    TransferOrderRepository};
use App\Repository\OrderRepository;
use App\Widget\DateRange;
use Illuminate\Http\Request;
use App\Admin\Exports\Inventory\ViewDailyReceivingExport;
use Maatwebsite\Excel\Facades\Excel;

class ViewDailyReceivingController extends BaseController
{
    private $orderRep;
    private $outletInformation;
    private $widget;
    private $transferDetailRep;
    private $viewDailyReceivingRep;
    private $viewDailyReceivingSummaryRep;
    private $stockOpnameRep;
    private $purchaseRep;
    private $receiveRep;
    private $returnRep;
    private $transferRep;
    private $storeCode;
    private $commonRep;

    public function __construct(
        OrderRepository $orderRep,
        TransferOrderDetailRepository $transferDetailRepo,
        ViewDailyReceivingRepository $viewDailyReceivingRep,
        ViewDailyReceivingSummaryRepository $viewDailyReceivingSummaryRep,
        StockOpnameRepository $stockOpnameRep,
        PurchaseOrderRepository $purchaseRep,
        ReceiveOrderRepository $receiveRep,
        ReturnOrderRepository $returnRep,
        TransferOrderRepository $transferRep,
        CommonRepository $commonRep
    )
    {
        parent::__construct();
        $this->widget            = new DateRange("fromDate", "toDate");
        $this->widget->setFromDate(date("Y-m-d"))->setToDate(date("Y-m-d"), false);
        $this->outletInformation = ConfigHelp::get("outlet_code") . '.' . ConfigHelp::get("outlet_name");

        $this->orderRep                     = $orderRep;
        $this->transferDetailRep            = $transferDetailRepo;
        $this->viewDailyReceivingRep        = $viewDailyReceivingRep;
        $this->viewDailyReceivingSummaryRep = $viewDailyReceivingSummaryRep;
        $this->stockOpnameRep               = $stockOpnameRep;
        $this->purchaseRep                  = $purchaseRep;
        $this->receiveRep                   = $receiveRep;
        $this->returnRep                    = $returnRep;
        $this->transferRep                  = $transferRep;
        $this->commonRep                    = $commonRep;
        $this->storeCode                    = ConfigHelp::get("outlet_code");
        // Disable libxml errors and allow user to fetch error information as needed
        libxml_use_internal_errors(true);
    }

    protected function filterDateTime(Request $request)
    {
        $start_hour = ConfigHelp::get("start_time");
        $end_hour   = ConfigHelp::get("end_time");
        $end_hour   = date('H:i:s', strtotime('+' . DEFAULT_ADDITIONAL_HOURS . ' hour', strtotime("$end_hour:00:00")));
        $param      = $this->getParamFilter($request);
        if ($param['fromDate'] == $param['toDate']) {
            $request['period']   = $param['fromDate'];
        } else {
            $request['period']   = $param['period'];
        }
        $to_date    = intval(date('H', strtotime($end_hour))) < $start_hour ? date('Y-m-d', strtotime('+1 day', strtotime($param['fromDate']))) : $param['fromDate'];

        $request['fromDate'] = "{$param['fromDate']} 05:01:00";
        $request['toDate']   = "{$to_date} 05:00:59";

        return $request;
    }

    public function report(Request $request)
    {
        $param    = $this->filterDateTime($request);
        $dailyData = $this->viewDailyReceivingRep->getListReport($param);

        $dailyReceiving = [];
        $dailyReturn    = [];
        foreach ($dailyData as $row) {
            if ($row->type_transaction == DAILY_RETURN) {
                $dailyReturn[] = $row;
            }
            if ($row->type_transaction == DAILY_RECEIVE) {
                $dailyReceiving[] = $row;
            }
        }

        $daily_receiving_summary    = $this->viewDailyReceivingSummaryRep->getListReport($param);
        $transfer                   = $this->transferDetailRep->getListReport($param, TRANSFER_TYPE_OUT);

        // get data for potential report
        // Potential Price : SELECT f_sum_total_material_price('2019-08-028','2019-08-28') AS potential_price;
        //
        // Sales : get from summary of Sales > TOTAL F & B NETT
        //
        // Food and Beverage Cost : Potential Price / Sales * 100
        $param['toDate'] = $param['fromDate']; // get the chosen day
        $potential_price = $this->commonRep->getPotentialPrice($param);
        $potential_price = !empty($potential_price) ? $potential_price[0]->potential_price : 0;

        $order_details  = $this->orderRep->getReportSummaryOrderDetail($param);
        $food_order_details = [];
        $bvrg_order_details = [];
        $data               = [];

        // get food and beverage net sale from order detail
        $food_net_sales     = 0;
        $bvrg_net_sales     = 0;
        foreach ($order_details as $order_detail) {
            if ($order_detail->category_id == 71) {
                $bvrg_order_details[] = $order_detail;
            } else {
                $food_order_details[] = $order_detail;
            }
        }

        // sum total food net sale
        foreach ($food_order_details as $order_detail) {
            $price_before_tax   = $order_detail->sub_price;
            $price_and_quantity = $price_before_tax * $order_detail->quantity;
            $food_net_sales     += $price_and_quantity;
        }

        // sum total beverage net sale
        foreach ($bvrg_order_details as $order_detail) {
            $price_before_tax   = $order_detail->sub_price;
            $price_and_quantity = $price_before_tax * $order_detail->quantity;
            $bvrg_net_sales     += $price_and_quantity;
        }

        $sales  = $food_net_sales + $bvrg_net_sales;

        $data['data'] = [
            'daily_receiving'            => $dailyReceiving,
            'daily_return'               => $dailyReturn,
            'daily_receiving_summary'    => $daily_receiving_summary,
            'transfer'                   => $transfer,
            'potential_price'            => $potential_price,
            'sales'                      => $sales,
            'f_b_cost'                   => $sales > 0 ? (($potential_price/$sales)*100) : 0 // prevent division by 0
        ];

        $data['period'] = date('Y-m-d', strtotime($param['fromDate']));
        $data['date']   = date("d/m/Y");
        $data['time']   = date("H:i:s");
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";
        $data['layout'] = isset($param['printview']) ? "layouts.webview" : "admin::index";

        // Common information
        $data['widget']            = $this->widget;
        $data['outletInformation'] = $this->outletInformation;
        $data['name']              = "Daily Receiving";
        $data['base_url_download'] = route('inventory-report-daily-receiving');
        $data['base_url_report_transaction'] = route('inventory-report-transaction');

        if (!empty($param['export_type'])) {
            $data['is_exported_file'] = true;
            $extensionFile            = PosHelper::get_extension_export_file($param['export_type']);
            return Excel::download(new ViewDailyReceivingExport($data), "daily_receiving{$extensionFile}");
        }

        return view("inventory_report.daily_receiving", $data);
    }

    public function reportTransaction(Request $request) {
        $params    = $this->filterDateTime($request);
        $from_date = $params['fromDate'];
        $to_date   = $params['toDate'];
        $stock_opnames  = $this->stockOpnameRep->getByPeriod($from_date, $to_date);
        $receives       = $this->receiveRep->getByPeriod($from_date, $to_date);
        $returns        = $this->returnRep->getByPeriod($from_date, $to_date);
        $transfers      = $this->transferRep->getByPeriod($from_date, $to_date);
        $folder_contain = INVENTORY_DIR_DAILY_SHEET_DOWNLOAD_CSV;

        $file = $this->viewDailyReceivingSummaryRep->zipFileTransaction($this->storeCode, $stock_opnames, $receives, $returns, $transfers, $from_date, $folder_contain);
        return $this->downloadFile($request, $file['path'], $file['file_name']);
    }
}
