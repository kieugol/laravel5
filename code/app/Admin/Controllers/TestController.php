<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\BaseController;
//use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
//use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
//use Encore\Admin\Layout\Row;
use App\Repository\MenuRepository;
use App\Repository\EodHistoryRepository;
use Illuminate\Support\Facades\DB;

class TestController extends BaseController {

    private $mmenu = null;
    private $meodhistory = null;

    public function __construct(MenuRepository $mmenu, EodHistoryRepository $eodhistory) {
        parent::__construct();
        $this->mmenu = $mmenu;
        $this->meodhistory = $eodhistory;
    }

    public function index() {
        /*$list = DB::table('order as a')
            ->select('a.id', 'a.amount', 'b.total_payment', 'b.payment_method_name')
            ->join('order_payment as b', 'a.id', '=', 'b.order_id')
            ->whereRaw("(date(collection_time) >= '2019-04-29')")
            ->where('order_status_id', 1)
            ->where('is_oc', 0)
            ->get();
        $a = [];
        foreach ($list as $item) {
            if ($item->total_payment != $item->amount) {
                $a[] = $item;
            }
        }
        dd($a);*/
        /*$list = DB::table('order as a')
            ->select('b.id', 'b.plucode')
            ->join('order_detail as b', 'a.id', '=', 'b.order_id')
            ->whereRaw("(date >= '2018-08-15')")
            ->get();
        $a = [];
        foreach ($list as $item) {
            if ($item->plucode == null) {
                $a[] = $item;
            }
        }

        dd($a);*/
        $list = DB::table('order')
            ->whereRaw("(date >= '2019-04-29')")
            ->where('order_status_id', 1)
            ->where('is_oc', 0)
            ->get();
        $a = [];
        foreach ($list as $item) {
            if ($item->sub_delivery_fee + $item->sub_total + $item->tax_value + $item->donation != $item->amount) {
                $a[] = $item;
            }
        }

        dd($a);
    }

}
