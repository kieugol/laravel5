<?php

namespace App\Admin\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class NetSalesAndBillByHoursExport implements FromView
{
    protected  $data = '';

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('report.net_sales_and_bill_by_hours', $this->data);
    }
}
