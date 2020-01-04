<?php

/**
 * Created by PhpStorm.
 * User: ThieuPham
 * Date: 02/03/18
 * Time: 3:44 PM
 */

namespace App\Repository;

use App\Model\Order;
use App\Model\OrderPayment;
use App\Model\OrderDetail;
use App\Model\OrderStatus;
use App\Model\PaymentMethod;
use Illuminate\Support\Facades\DB;

class OrderRepository extends BaseRepository {

    public function __construct(Order $model) {
        parent::__construct($model);
    }

    public function getListPrinter() {
        $list = DB::table("printer")->get()->pluck("name", "ip");
        return $list;
    }

    public function getListPartner() {
        $list = DB::table("partner")->get()->pluck("name", "id");
        return $list;
    }

    public function getListSku() {
        $list = DB::table("menu_sku")->get()->pluck("sku", "plucode");
        return $list;
    }

    public function getReportNonCash($param = null) {
        $query_builder = DB::table("payment_method as a")
            ->join("order_payment as b", "a.id", "=", "b.payment_method_id")
            ->join("order as c", "b.order_id", "=", "c.id")
            ->join("user as d", "c.created_by", "=", "d.id")
            ->select("b.payment_method_name", "c.number", "c.created_date", "b.approval_code", "c.name", "b.value", "b.remark", "b.card_number", "d.name AS cashier_name")
            ->where("a.type", "<>", "CASH")
            ->where("a.type", "<>", "OC")
            ->where("a.type", "<>", PAYMENT_METHOD_OUTLET_MEAL)
            ->where("c.order_status_id", ORDER_STATUS_FINISHED)
            ->orderBy("b.payment_method_name", "desc");

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("c.date", ">=", $param['fromDate']);
            }
            if (isset($param['toDate'])) {
                $query_builder->where("c.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("c.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("c.created_date", "<", $param['toTime']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }

    public function getReportDelivery($param = null) {
        $query_builder = DB::table("order as a")
            ->join("order_delivery as b", "a.id", "=", "b.order_id")
            ->join("order_log_time as d", "a.id", "=", "d.order_id")
            ->leftJoin("user as c", "b.user_driver_id", "=", "c.id")
            ->select("d.*", "a.*", "b.address", "b.zone", "b.lat", "b.long", "b.delivery_together", "b.status as delivery_status", "c.username", "c.code as user_code", "c.name as driver_name")
            ->where("a.order_status_id", "=", ORDER_STATUS_FINISHED)
            ->where("a.order_type_id", ORDER_TYPE_DELIVERY)
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0)
            ->orderBy("a.id", "desc");

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }
            if (isset($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("a.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTimgetReportCashe'])) {
                $query_builder->where("a.created_date", "<", $param['toTime']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }

    public function getReportSummaryLog($param = null) {
        $query_builder = DB::table("order as a")
            ->join("order_log_time as d", "a.id", "=", "d.order_id")
            ->leftJoin("order_delivery as b", "a.id", "=", "b.order_id")
            ->leftJoin("user as c", "b.user_driver_id", "=", "c.id")
            ->select("d.*", "a.*", "b.address", "b.zone", "b.lat", "b.long", "b.delivery_together", "b.status as delivery_status", "c.username", "c.code as user_code", "c.name as driver_name")
            ->where("a.order_status_id", "=", ORDER_STATUS_FINISHED)
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0)
            ->orderBy("a.id", "desc");

        if (!empty($param)) {
            if (!empty($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }
            if (!empty($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("a.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("a.created_date", "<", $param['toTime']);
            }
            if (!empty($param['type'])) {
                $query_builder->where("a.order_type_id", $param['type']);
            }
        }

        //dd($query_builder);
        $items = $query_builder->get();
        return $items;
    }

    public function getReportSummaryOrder($param = null) {
        $query_builder = DB::table("order as a")
            ->join("order_payment as b", "a.id", "=", "b.order_id")
            ->leftJoin("partner as c", "a.partner_id", "=", "c.id")
            ->select("a.*", "a.id as order_id", "b.*", "c.name as partner_name")
            ->where("a.order_status_id", ORDER_STATUS_FINISHED)
//                ->where("b.payment_method_id", "<>", PAYMENT_METHOD_OC)
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0)
            ->orderBy("a.id");

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }
            if (isset($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("a.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("a.created_date", "<", $param['toTime']);
            }
        }
        $items = $query_builder->get();
        return $items;
    }

    public function getReportSummaryOrderVoid($param = null) {
        $query_builder = DB::table("order as a")
            ->join("order_detail as b", "a.id", "=", "b.order_id")
            ->where("order_status_id", ORDER_STATUS_CANCELED)
            ->where("b.is_delete", 0);

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("date", ">=", $param['fromDate']);
            }

            if (isset($param['toDate'])) {
                $query_builder->where("date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("created_date", "<", $param['toTime']);
            }
        }
        $items = $query_builder->get();
        return $items;
    }

    public function getReportSummaryOrderDetail($param = null) {
        $query_builder = DB::table("order_detail as a")
            ->join("order as b", "a.order_id", "=", "b.id")
            ->join("order_payment as c", "b.id", "=", "c.order_id")
            ->where("b.order_status_id", ORDER_STATUS_FINISHED)
//                ->where("c.payment_method_id", "<>", PAYMENT_METHOD_OC)
            ->where("b.is_oc", 0)
            ->where("b.is_meals_outlet", 0)
            ->where("a.is_delete", 0)
            ->where("a.order_detail_id", NULL)
            ->groupBy("a.id");

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("b.date", ">=", substr($param['fromDate'], 0, 10));
            }

            if (isset($param['toDate'])) {
                $query_builder->where("b.date", "<=", substr($param['toDate'], 0, 10));
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("b.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("b.created_date", "<", $param['toTime']);
            }
        }
        $items = $query_builder->get();

        return $items;
    }

    public function getReportSummaryPaymentMethod($param = null) {
        $query_builder = DB::table("order_payment as a")
            ->join("order as b", "a.order_id", "=", "b.id")
            ->select("a.payment_method_id", "a.payment_method_name")
            ->where("b.order_status_id", ORDER_STATUS_FINISHED)
//                ->where("a.payment_method_id", "<>", PAYMENT_METHOD_OC);
            ->where("b.is_oc", 0)
            ->where("b.is_meals_outlet", 0);

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("b.date", ">=", $param['fromDate']);
            }

            if (isset($param['toDate'])) {
                $query_builder->where("b.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("b.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("b.created_date", "<", $param['toTime']);
            }
        }
        $query_builder->groupBy("a.payment_method_id");
        $items = $query_builder->get();
        return $items;
    }

    public function getReportByPartner($param = null) {
        $query_builder = DB::table("order as a")
            ->join("user as b", "a.created_by", "=", "b.id")
            ->join("partner as pn", "pn.id", "=", "a.partner_id")
            ->select("a.*", "b.name as cashier_name", 'pn.code AS code_partner')
            ->where("a.order_status_id", "=", ORDER_STATUS_FINISHED)
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0);

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }

            if (isset($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("a.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("a.created_date", "<", $param['toTime']);
            }
        }
        $items = $query_builder->get();
        return $items;
    }

    public function getReportMix($param = null) {
        $query_builder = DB::table("order_detail as a")
            ->join("menu_category as b", "a.category_id", "=", "b.id")
            ->join("order as c", "c.id", "=", "a.order_id")
            ->leftJoin("menu_sku as d", "a.plucode", "=", "d.plucode")
            ->select("a.*", "b.name as category_name", "c.order_type_id", "c.created_date", "d.sku")
            ->where("c.order_status_id", ORDER_STATUS_FINISHED)
            ->where("c.is_oc", 0)
            ->where("c.is_meals_outlet", 0)
            ->where("a.is_delete", 0)
            ->orderBy("a.category_id", "asc")
            ->orderBy("d.sku", "asc")
            ->orderBy("a.variant_name", "desc")
            ->orderBy("a.addon_name", "desc")
            ->groupBy("a.id");

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("c.date", ">=", $param['fromDate']);
            }

            if (isset($param['toDate'])) {
                $query_builder->where("c.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("c.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("c.created_date", "<", $param['toTime']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }

    public function getReportMixByHour($param = null) {
        $query_builder = DB::table("order")
            ->where("order_status_id", ORDER_STATUS_FINISHED)
            ->where("is_oc", 0)
            ->where("is_meals_outlet", 0);

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("date", ">=", $param['fromDate']);
            }

            if (isset($param['toDate'])) {
                $query_builder->where("date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("created_date", "<", $param['toTime']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }

    public function getReportSummaryVoid($param = null) {
        $query_builder = $this->model::with([
            "details" => function($query) {
                $query->where("is_delete", "=", 0);
                $query->where("order_detail_id");
            },
            "customer"
        ]);
        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("date", ">=", $param['fromDate']);
            }
            if (isset($param['toDate'])) {
                $query_builder->where("date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("created_date", ">",$param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("created_date", "<", $param['toTime']);
            }
        }
        $orders = $query_builder->where("order_status_id", ORDER_STATUS_CANCELED)->get();

        $ids = $orders->pluck("id", "id");
        if ($ids) {
            $logs = $this->getLogStatus($ids, ORDER_STATUS_CANCELED);
            foreach ($orders as &$order) {
                $order->void_name = "";
                $order->void_time = "";
                $order->void_reason = "";
                if (isset($logs[$order->id])) {
                    $log = $logs[$order->id];
                    $order->void_name = $log->user_name;
                    $order->void_time = $log->created_date;
                    $order->void_reason = $log->reason;
                    $order->void_admin_name = $log->admin_username;
                }
            }
        }
        return $orders;
    }

    public function getLogStatus($ids, $status) {
        $logs = DB::table("order_log_status as a")->leftJoin("user as b", "a.updated_by", "b.id")
            ->leftJoin("user as c", "a.admin_user_id", "c.id")
            ->whereIn("a.order_id", $ids)
            ->where("a.status_new", $status)
            ->select("a.order_id", "a.reason", "a.created_date", "a.admin_user_id", "b.name as user_name", "c.name as admin_username")
            ->get();
        $result = array();
        foreach ($logs as $log) {
            $result[$log->order_id] = $log;
        }
        return $result;
    }

    public function getReportSummaryOc($param = null) {
        $query_builder = DB::table("order_payment as a")
            ->join("order as b", "b.id", "=", "a.order_id")
            ->leftJoin("shift_transaction as c", "a.shift_id", "=", "c.shift_id")
            ->select("a.*", "b.number", "b.created_date", "c.user_id")
            ->whereRaw("(b.is_oc = 1 or b.is_meals_outlet = 1)");
        //->orWhere("b.is_meals_outlet", 1);
        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("b.date", ">=", $param['fromDate']);
            }
            if (isset($param['toDate'])) {
                $query_builder->where("b.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("b.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("b.created_date", "<", $param['toTime']);
            }
        }
        $items = $query_builder->get();
        return $items;
    }

    public function getReportMixBySegmentOC($param = null) {
        $query_builder = DB::table("order_detail as a")
            ->join("menu_category as b", "a.category_id", "=", "b.id")
            ->join("order as c", "c.id", "=", "a.order_id")
            ->join("order_payment as op", "op.order_id", "=", "c.id")
            ->leftJoin("menu_sku as d", "a.plucode", "=", "d.plucode")
            ->select("a.*", "b.name as category_name", "c.order_type_id", "op.payment_method_id as payment_method_id", "d.sku")
            ->whereRaw("(c.is_oc = 1 or c.is_meals_outlet = 1)")
            ->where("a.is_delete", 0)
            ->orderBy("a.category_id", "asc")
            ->orderBy("a.variant_name", "desc")
            ->orderBy("a.addon_name", "desc");
        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("c.date", ">=", $param['fromDate']);
            }

            if (isset($param['toDate'])) {
                $query_builder->where("c.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("c.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("c.created_date", "<", $param['toTime']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }

    public function getReportHistoryByBill($param = null) {
        $query_builder = Order::query()
            ->with(['user_encash', 'order_type', 'order_payments'])
            ->with(['order_details' => function ($query) {
                $query->whereNull('order_detail_id')
                    ->where('is_delete', 0)->with(['children' => function($q) {
                        $q->where('is_delete', 0);
                    }]);
            }]);

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("order.date", ">=", $param['fromDate']);
            }
            if (isset($param['toDate'])) {
                $query_builder->where("order.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("created_date", "<", $param['toTime']);
            }
        }

        $items = $query_builder->where("order.order_status_id", "=", ORDER_STATUS_FINISHED)->get();
        //$items = $query_builder->get();
        return $items;
    }

    public function getReportSpeedLog($param = null) {
        $query_builder = DB::table("order as a")
            ->join("order_log_time as b", "a.id", "=", "b.order_id")
            ->select("a.created_date", "a.amount", "b.*", "c.delivery_together")
            ->leftJoin("order_delivery as c", "a.id", "=", "c.order_id")
            ->where("a.order_status_id", "=", ORDER_STATUS_FINISHED)
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0);

        if (!empty($param)) {
            if (!empty($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }
            if (!empty($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("a.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("a.created_date", "<", $param['toTime']);
            }
            if (!empty($param['order_type_id'])) {
                $query_builder->whereIn("a.order_type_id", $param['order_type_id']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }

    /**
     * get data for Speed of service log - swipe done report
     * @param null $param
     * @return mixed
     */
    public function getReportSwipeDone($param = null) {
        $query_builder = DB::table("order as a")
            ->join("order_log_time as b", "a.id", "=", "b.order_id")
            ->select("a.created_date", "a.amount", "b.*", "c.delivery_together")
            ->join("order_delivery as c", "a.id", "=", "c.order_id")
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0)
            ->whereNotNull("b.delivering_time");

        if (!empty($param)) {
            if (!empty($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }
            if (!empty($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }

    public function getReportByCashier($param = null) {
        $query_builder = DB::table("order as a")
            ->join("order_payment as b", "a.id", "b.order_id")
            ->leftJoin("user as c", "c.id", "=", "a.encash_by")
            ->select("a.*", "b.*", "c.name as cashier_name")
            ->where("a.order_status_id", "=", ORDER_STATUS_FINISHED)
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0)
            ->groupBy("b.id");

        if (!empty($param)) {
            if (!empty($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }
            if (!empty($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("a.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("a.created_date", "<", $param['toTime']);
            }
        }
        $items = $query_builder->get();
        return $items;
    }

    public function getReportPaymentDetail($param = null) {
        $query_builder = DB::table("payment_method as a")
            ->join("order_payment as b", "a.id", "=", "b.payment_method_id")
            ->join("order as c", "b.order_id", "=", "c.id")
            ->leftJoin("user as d", "d.id", "=", "c.encash_by")
            ->select("d.name as cashier_name", "c.amount", "b.payment_method_name", "b.payment_method_id", "c.number", "c.created_date", "b.approval_code", "b.card_number", "b.remark", "c.name", "b.value", "b.total_payment")
            ->where("c.order_status_id", ORDER_STATUS_FINISHED)
            ->where("c.is_oc", 0)
            ->where("c.is_meals_outlet", 0)
            ->groupBy("b.id");

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("c.date", ">=", $param['fromDate']);
            }
            if (isset($param['toDate'])) {
                $query_builder->where("c.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("c.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("c.created_date", "<", $param['toTime']);
            }
        }
        $items = $query_builder->get();
        return $items;
    }

    public function getCustomerOrdering($param)
    {
        $tblOrder = 'order';
        $tblOrderDO = 'order_delivery';

        $query = $this->model
            ->leftJoin("order_delivery AS $tblOrderDO", "$tblOrder.id", "$tblOrderDO.order_id")
            ->select([
                "$tblOrder.*",
                DB::raw("DATE_FORMAT($tblOrder.created_date, '%H:%i') AS time"),
                "$tblOrderDO.address AS do_address",
                "$tblOrderDO.zone AS do_zone"
            ])
            ->where("$tblOrder.order_status_id", ORDER_STATUS_FINISHED)
            ->where("$tblOrder.is_oc", 0)
            ->where("$tblOrder.is_meals_outlet", 0);

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query->where("$tblOrder.date", ">=", $param['fromDate']);
            }

            if (isset($param['toDate'])) {
                $query->where("$tblOrder.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query->where("$tblOrder.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query->where("$tblOrder.created_date", "<", $param['toTime']);
            }
        }

        $query->orderBy("$tblOrder.order_type_id", "DESC");
        $result = $query->get()->toArray();

        return $result;
    }

    public function getReportCash($param = null) {
        $query_builder = DB::table("order as a")
            ->join("order_payment as b", "a.id", "=", "b.order_id")
            ->select("a.date", DB::raw('SUM(b.total_payment) as amount'))
            ->where("b.payment_method_id", PAYMENT_METHOD_CASH)
            ->where("a.order_status_id", ORDER_STATUS_FINISHED)
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0)
            ->groupBy("a.date");

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }

            if (isset($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("a.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("a.created_date", "<", $param['toTime']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }

    public function getReportBillByPoding($param = null) {
        $query_builder = DB::table("order as a")
            ->join("order_delivery as b", "a.id", "=", "b.order_id")
            ->select("b.zone", DB::raw("count(*) as total_bill"), DB::raw("sum(a.amount) as total_amount"))
            ->where("a.order_status_id", ORDER_STATUS_FINISHED)
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0)
            ->groupBy("b.zone");

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }

            if (isset($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("a.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("a.created_date", "<", $param['toTime']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }

    public function getReportDonationDetail($param = null) {
        $query_builder = DB::table("order as a")
            ->leftJoin("user as c", "a.encash_by", "c.id")
            ->select("a.number", "a.created_date", "a.amount", "a.donation", "c.name as user_name")
            ->where("a.order_status_id", ORDER_STATUS_FINISHED)
            ->where("a.donation", '>', 0)
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0)
            ->groupBy("a.id");

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }

            if (isset($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
            if (isset($param['fromTime'])) {
                $query_builder->where("a.created_date", ">", $param['fromTime']);
            }
            if (isset($param['toTime'])) {
                $query_builder->where("a.created_date", "<", $param['toTime']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }

    public function getOrderDetails($id){
        $query_builder = DB::table("order_status as os")
            ->Join("order", "order.order_status_id", "os.id")
            ->Join("order_detail", "order.id" , "order_detail.order_id")
            ->select("order.*","order_detail.*","os.name as status_name")
            ->where("order.id", $id);

       if (!empty($id)) {
           $items = $query_builder->get();
           return $items;
       }

    }

    public function getstatusDetails(){
        $result = DB::table("order_status as os")
            ->Join("order", "order.order_status_id", "os.id")
            ->Join("order_detail", "order.id" , "order_detail.order_id")
            ->select("order.*","order_detail.*","os.name as status_name")
            ->get();

        return $result;
    }

    public function getPaymentDetails($id){
        $result = DB::table("order_payment as pd")
            ->leftJoin("order", "order.id", "pd.order_id")
            ->where("order.id", $id)
            ->get();

        return $result;
    }

    public function getDeliveryDetails($id){
        $result = DB::table("order_delivery as pd")
            ->leftJoin("order", "order.id", "pd.order_id")
            ->where("order.id", $id)
            ->get();

        return $result;
    }

    public function getlogDetails($id){
        $result = DB::table("order_status as os")
            ->Join("order_log_status as ols", "ols.status_new", "os.id")
            ->Join("order as od", "od.id" , "ols.order_id")
            ->select("ols.*","od.*","os.name as status_name", "ols.created_date as created_status_log")
            ->where("od.id", $id)
            ->get();

        return $result;
    }

    public function getOrderByID($order_id)
    {
        $items = DB::table("order as os")
            ->select("os.kds_time")
            ->where("os.id", $order_id)
            ->first();

        return $items;
    }

    public function getOrder($order_id)
    {
        $query = DB::table('order')
             ->select('order.*', 'order_status.name as order_status_name', 'order_type.name as order_type_name')
             ->leftJoin('order_status', 'order.order_status_id', '=', 'order_status.id')
             ->leftJoin('order_type', 'order.order_type_id', '=', 'order_type.id')
             ->where('order.id', $order_id)
             ->first();

        return $query;
    }

    public function updateOrderByID($id, $data)
    {
        $query = DB::table('order')
            ->where('id', $id)
            ->update($data);

        return $query;
    }

    public function getOrderDetailByID($order_detail_id)
    {
        $query = DB::table('order_detail')
            ->where('id', $order_detail_id)
            ->first();

        return $query;
    }

    /**
     * @param $order_detail_id
     * @param $data
     * @return mixed
     */
    public  function updateOrderDetailByID($order_detail_id, $data)
    {
        $query = DB::table('order_detail')
            ->where('id', $order_detail_id)
            ->update($data);

        return $query;
    }

    /**
     * @param $order_id
     * @return mixed
     */
    public function getOrderPaymentByOrderID($order_id)
    {
        $query = DB::table('order_payment')
            ->where('order_id', $order_id)
            ->first();
        return $query;

    }

    /**
     * @param $order_id
     * @return mixed
     */
    public function updateOrderPaymentByOrderID($order_id, $data)
    {
        $query = DB::table('order_payment')
            ->where('order_id', $order_id)
            ->update($data);
        return $query;
    }

    /**
     * @param null $param
     * @return mixed
     */
    public function getReportPizzaSize($param = null) {
        $query_builder = $this->model->from('order as a')
            ->select("a.*", "b.addon_id", "b.addon_name")
            ->rightJoin("order_detail as b", "a.id", "=", "b.order_id")
            ->where("b.addon_id", ">", 0)
            ->where("a.order_status_id", ORDER_STATUS_FINISHED)
            ->where("a.is_oc", 0)
            ->where("a.is_meals_outlet", 0);

        if (!empty($param)) {
            if (isset($param['fromDate'])) {
                $query_builder->where("a.date", ">=", $param['fromDate']);
            }
            if (isset($param['toDate'])) {
                $query_builder->where("a.date", "<=", $param['toDate']);
            }
        }

        $items = $query_builder->get();
        return $items;
    }
    
    public function calculateSaleFoodAndBeverage($param, $keyTotalGroupFB)
    {
        $result = [
            GROUP_FOOD       => 0,
            GROUP_BEVERAGE   => 0,
            $keyTotalGroupFB => 0
        ];
        
        $orderDetail = $this->getReportSummaryOrderDetail($param);
        
        foreach ($orderDetail as $order_detail) {
            $price_before_tax   = $order_detail->sub_price;
            $price_and_quantity = $price_before_tax * $order_detail->quantity;
            if ($order_detail->category_id == CATEGORY_DRINK) {
                $result[GROUP_BEVERAGE] += $price_and_quantity;
            } else {
                $result[GROUP_FOOD] += $price_and_quantity;
            }
            $result[$keyTotalGroupFB] += $price_and_quantity;
        }
        
        return $result;
    }
}
